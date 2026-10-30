<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Phase8GatewayController extends Controller
{
    /**
     * List the payment gateway catalog.
     */
    public function index(Request $request)
    {
        $gateways = DB::table('payment_gateways')
            ->select([
                'id',
                'gateway_code',
                'gateway_name',
                'provider',
                'gateway_type',
                'is_active',
                'sort_order',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function ($gateway) {
                return [
                    'id' => $gateway->id,
                    'name' => $gateway->gateway_name,
                    'gateway_code' => $gateway->gateway_code,
                    'provider' => $gateway->provider,
                    'gateway_type' => $gateway->gateway_type,
                    'is_active' => (bool) $gateway->is_active,
                    'sort_order' => $gateway->sort_order,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $gateways,
        ]);
    }

    /**
     * Create or update a company's gateway configuration.
     */
    public function configureCompanyGateway(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => [
                'required',
                'integer',
                'min:1',
                Rule::exists('companies', 'id'),
            ],
            'gateway_id' => [
                'required',
                'integer',
                'min:1',
                Rule::exists('payment_gateways', 'id'),
            ],
            'environment' => [
                'sometimes',
                'required',
                Rule::in(['TEST', 'LIVE']),
            ],
            'merchant_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'terminal_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // A catalog entry must be active before it can be configured.
        $gateway = DB::table('payment_gateways')
            ->where('id', $data['gateway_id'])
            ->first();

        if (!$gateway || !$gateway->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This payment gateway is inactive or unavailable.',
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($data) {
                $companyId = (int) $data['company_id'];
                $gatewayId = (int) $data['gateway_id'];

                // Lock this company's existing configuration rows while
                // applying default-gateway changes.
                DB::table('company_payment_gateways')
                    ->where('company_id', $companyId)
                    ->lockForUpdate()
                    ->get();

                $existing = DB::table('company_payment_gateways')
                    ->where('company_id', $companyId)
                    ->where('gateway_id', $gatewayId)
                    ->first();

                $now = now();

                $values = [
                    'company_id' => $companyId,
                    'gateway_id' => $gatewayId,
                    'gateway_name' => DB::table('payment_gateways')
                        ->where('id', $gatewayId)
                        ->value('gateway_name'),
                    'environment' => $data['environment']
                        ?? ($existing->environment ?? 'TEST'),
                    'is_active' => array_key_exists('is_active', $data)
                        ? (bool) $data['is_active']
                        : (bool) ($existing->is_active ?? true),
                    'is_default' => array_key_exists('is_default', $data)
                        ? (bool) $data['is_default']
                        : (bool) ($existing->is_default ?? false),
                    'updated_at' => $now,
                ];

                // Preserve stored values when optional fields are omitted.
                foreach (['merchant_id', 'terminal_id'] as $field) {
                    if (array_key_exists($field, $data)) {
                        $values[$field] = $data[$field];
                    } elseif ($existing) {
                        $values[$field] = $existing->{$field};
                    }
                }

                if ($values['is_default']) {
                    // Only one default gateway per company.
                    DB::table('company_payment_gateways')
                        ->where('company_id', $companyId)
                        ->update([
                            'is_default' => false,
                            'updated_at' => $now,
                        ]);
                }

                if ($existing) {
                    DB::table('company_payment_gateways')
                        ->where('id', $existing->id)
                        ->update($values);

                    $id = $existing->id;
                }                 else {
                    // Let PostgreSQL generate the identity ID automatically.
                    $values['uuid'] = (string) Str::uuid();
                    $values['created_at'] = $now;

                    $id = DB::table('company_payment_gateways')
                        ->insertGetId($values, 'id');
                }

                return DB::table('company_payment_gateways')
                    ->where('id', $id)
                    ->first();
            });

            return response()->json([
                'success' => true,
                'message' => 'Company payment gateway configuration saved.',
                'data' => $result,
            ], 200);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to save the gateway configuration.',
            ], 500);
        }
    }
}
