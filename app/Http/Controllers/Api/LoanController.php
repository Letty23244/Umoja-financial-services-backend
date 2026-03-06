<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loan; 

class LoanController extends Controller
{
   public function applyLoan(Request $request)
{
    $request->validate([
        'amount'=>'required|numeric|min:1000',
        'duration_months'=>'required|integer'
    ]);

    $loan = Loan::create([
        'user_id'=>auth()->id(),
        'amount'=>$request->amount,
        'duration_months'=>$request->duration_months,
        'remaining_balance'=>$request->amount,
        'status'=>'pending'
    ]);

    return response()->json([
        'message'=>'Loan application submitted',
        'loan'=>$loan
    ]);
}
public function approveLoan($id)
{
    $loan = Loan::findOrFail($id);

    $loan->status = 'approved';
    $loan->save();

    return response()->json([
        'message'=>'Loan approved',
        'loan'=>$loan
    ]);
}
public function repayLoan(Request $request,$id)
{
    $loan = Loan::findOrFail($id);

    if($loan->status !== 'approved'){
        return response()->json([
            'message'=>'Loan not approved'
        ],400);
    }

    $loan->remaining_balance -= $request->amount;

    if($loan->remaining_balance <= 0){
        $loan->status = 'paid';
        $loan->remaining_balance = 0;
    }

    $loan->save();

    return response()->json([
        'message'=>'Loan repayment successful',
        'loan'=>$loan
    ]);
}
 //
}
