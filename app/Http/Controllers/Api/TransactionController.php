<?php

namespace App\Http\Controllers\Api;
use App\Models\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::where(
            'user_id',
            auth()->id()
        )->latest()->get();

        return response()->json($transactions);
    }
}
