<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfitTracker;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProfitTrackerController extends Controller
{
    // GET /api/profit-tracker
    public function index(Request $request): JsonResponse
    {
        $query = ProfitTracker::where('user_id', Auth::id());

        // Filter by period
        if ($request->period) {
            $query->where('period', $request->period);
        }

        // Filter by date range
        if ($request->from && $request->to) {
            $query->whereBetween('record_date', [$request->from, $request->to]);
        }

        $records = $query->latest('record_date')->get();

        // Summary stats
        $summary = [
            'total_revenue'  => $records->sum('revenue'),
            'total_expenses' => $records->sum('expenses'),
            'total_profit'   => $records->sum('profit'),
            'total_records'  => $records->count(),
        ];

        return response()->json([
            'status'  => 'success',
            'summary' => $summary,
            'data'    => $records,
        ]);
    }

    // POST /api/profit-tracker
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'revenue'       => 'required|numeric|min:0',
            'expenses'      => 'required|numeric|min:0',
            'category'      => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'record_date'   => 'required|date',
            'period'        => 'required|in:daily,weekly,monthly',
        ]);

        $record = ProfitTracker::create([
            ...$request->only([
                'business_name', 'revenue', 'expenses',
                'category', 'description', 'record_date', 'period'
            ]),
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Profit record added successfully',
            'data'    => $record,
        ], 201);
    }

    // GET /api/profit-tracker/{id}
    public function show($id): JsonResponse
    {
        $record = ProfitTracker::where('user_id', Auth::id())->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $record,
        ]);
    }

    // PUT /api/profit-tracker/{id}
    public function update(Request $request, $id): JsonResponse
    {
        $record = ProfitTracker::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'business_name' => 'sometimes|string|max:255',
            'revenue'       => 'sometimes|numeric|min:0',
            'expenses'      => 'sometimes|numeric|min:0',
            'category'      => 'nullable|string',
            'description'   => 'nullable|string',
            'record_date'   => 'sometimes|date',
            'period'        => 'sometimes|in:daily,weekly,monthly',
        ]);

        $record->update($request->only([
            'business_name', 'revenue', 'expenses',
            'category', 'description', 'record_date', 'period'
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Profit record updated successfully',
            'data'    => $record,
        ]);
    }

    // DELETE /api/profit-tracker/{id}
    public function destroy($id): JsonResponse
    {
        $record = ProfitTracker::where('user_id', Auth::id())->findOrFail($id);
        $record->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Profit record deleted successfully',
        ]);
    }

    // GET /api/profit-tracker/summary/monthly
    public function monthlySummary(): JsonResponse
    {
        $records = ProfitTracker::where('user_id', Auth::id())
            ->selectRaw('MONTH(record_date) as month, YEAR(record_date) as year,
                SUM(revenue) as total_revenue,
                SUM(expenses) as total_expenses,
                SUM(profit) as total_profit')
            ->groupByRaw('YEAR(record_date), MONTH(record_date)')
            ->orderByRaw('YEAR(record_date) DESC, MONTH(record_date) DESC')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $records,
        ]);
    }
}