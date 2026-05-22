<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    /**
     * Mobile Money Payment Webhook
     */
    public function handle(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {

            /*
            Example payload from MTN/Airtel
            */

            $reference = $request->input('reference');
            $status    = $request->input('status'); // SUCCESS / FAILED

            $transaction = Transaction::where('reference', $reference)
                ->lockForUpdate()
                ->firstOrFail();

            // Avoid duplicate processing
            if ($transaction->status === 'success') {
                return response()->json(['message' => 'Already processed']);
            }

            if ($status === 'SUCCESS') {

                $transaction->update([
                    'status' => 'success'
                ]);

                $wallet = Wallet::where('user_id', $transaction->user_id)->first();

                /*
                Deposit → add balance
                Withdrawal → subtract balance
                */

                if ($transaction->type === 'deposit') {

                    $wallet->increment('balance', $transaction->amount);

                } elseif ($transaction->type === 'withdrawal') {

                    $wallet->decrement('balance', $transaction->amount);
                }

                // Create notification
                Notification::create([
                    'user_id' => $transaction->user_id,
                    'title'   => 'Transaction Successful',
                    'message' => ucfirst($transaction->type)
                        .' of UGX '.$transaction->amount.' completed successfully.',
                    'is_read' => false,
                ]);

            } else {

                $transaction->update([
                    'status' => 'failed'
                ]);

                Notification::create([
                    'user_id' => $transaction->user_id,
                    'title'   => 'Transaction Failed',
                    'message' => 'Your payment failed.',
                    'is_read' => false,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}