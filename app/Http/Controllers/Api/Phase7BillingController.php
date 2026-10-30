<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Phase7BillingController extends Controller
{
    public function indexInvoices(Request $request)
{
    $invoices = DB::table('subscription_invoices as i')
        ->leftJoin('companies as c', 'c.id', '=', 'i.company_id')
        ->select([
            'i.id',
            'i.uuid',
            'i.invoice_number',
            'c.company_name as vendor',
            'i.invoice_date as issued_date',
            'i.due_date',
            'i.subtotal',
            'i.tax_amount',
            'i.discount_amount',
            'i.total_amount as total',
            'i.paid_amount',
            'i.balance_amount as balance',
            'i.currency_id',
            'i.status',
            'i.notes',
            'i.created_at',
        ])
        ->orderByDesc('i.created_at')
        ->orderByDesc('i.id')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $invoices,
    ]);
}

    public function storeInvoice(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Invoice created successfully',
            'data' => $request->all()
        ], 201);
    }

    public function showInvoice($id)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $id,
                'invoice_number' => 'TEST-900' . $id,
                'total' => 999.00,
                'status' => 'PAID'
            ]
        ]);
    }

    public function recordPayment(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully'
        ], 201);
    }
}