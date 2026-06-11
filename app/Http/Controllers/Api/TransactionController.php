<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Withdraw;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * GET /api/transactions
     * Returns merged deposits + withdrawals for the logged-in user,
     * sorted by date descending.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // ── Deposits ───────────────────────────────────────
        $deposits = Deposit::where('user_id', $userId)
            ->latest()
            ->get()
            ->map(fn($d) => [
                'id'          => 'dep_' . $d->id,
                'type'        => 'deposit',
                'title'       => 'Deposit',
                'amount'      => $d->amount,
                'is_credit'   => true,
                'description' => $d->description ?? '',
                'method'      => $d->method ?? '',
                'date'        => $d->created_at->format('D d M Y'),
                'created_at'  => (string) $d->created_at,
            ]);

        // ── Withdrawals ────────────────────────────────────
        $withdrawals = Withdraw::where('user_id', $userId)
            ->latest()
            ->get()
            ->map(fn($w) => [
                'id'          => 'wit_' . $w->id,
                'type'        => 'withdrawal',
                'title'       => 'Withdrawal',
                'amount'      => $w->amount,
                'is_credit'   => false,
                'description' => $w->description ?? '',
                'method'      => $w->method ?? '',
                'date'        => $w->created_at->format('D d M Y'),
                'created_at'  => (string) $w->created_at,
            ]);

        // ── Merge & sort ───────────────────────────────────
        $all = $deposits
            ->concat($withdrawals)
            ->sortByDesc('created_at')
            ->values();

        return response()->json([
            'data' => $all,
        ]);
    }

    /**
     * GET /api/admin/transactions
     * Admin view — returns ALL users' transactions with user info.
     */
    public function adminIndex(Request $request)
    {
        $deposits = Deposit::with('user')
            ->latest()
            ->get()
            ->map(fn($d) => [
                'id'          => 'dep_' . $d->id,
                'type'        => 'deposit',
                'title'       => 'Deposit',
                'amount'      => $d->amount,
                'is_credit'   => true,
                'description' => $d->description ?? '',
                'method'      => $d->method ?? '',
                'date'        => $d->created_at->format('D d M Y'),
                'created_at'  => (string) $d->created_at,
                'user'        => [
                    'id'    => $d->user->id,
                    'name'  => $d->user->name,
                    'email' => $d->user->email,
                ],
            ]);

        $withdrawals = Withdraw::with('user')
            ->latest()
            ->get()
            ->map(fn($w) => [
                'id'          => 'wit_' . $w->id,
                'type'        => 'withdrawal',
                'title'       => 'Withdrawal',
                'amount'      => $w->amount,
                'is_credit'   => false,
                'description' => $w->description ?? '',
                'method'      => $w->method ?? '',
                'date'        => $w->created_at->format('D d M Y'),
                'created_at'  => (string) $w->created_at,
                'user'        => [
                    'id'    => $w->user->id,
                    'name'  => $w->user->name,
                    'email' => $w->user->email,
                ],
            ]);

        $all = $deposits
            ->concat($withdrawals)
            ->sortByDesc('created_at')
            ->values();

        return response()->json([
            'data'  => $all,
            'total' => $all->count(),
        ]);
    }
}