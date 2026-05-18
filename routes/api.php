<?php

use Illuminate\Support\Facades\DB;

Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status'      => 'healthy',
            'environment' => env('APP_ENV'),
            'timestamp'   => now()->toIso8601String(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error'  => $e->getMessage(),
        ], 503);
    }
});

Route::get('/ready', function () {
    try {
        DB::connection()->getPdo();
        return response()->json(['ready' => true]);
    } catch (\Exception $e) {
        return response()->json(['ready' => false], 503);
    }
});

Route::get('/v1/status', function () {
    return response()->json([
        'api'         => 'running',
        'version'     => '1.0.0',
        'environment' => env('APP_ENV'),
    ]);
});

Route::get('/v1/customers', function () {
    $customers = DB::table('customers')->get();
    return response()->json($customers);
});

Route::get('/v1/repair-orders', function () {
    $orders = DB::table('repair_orders')
        ->join('customers', 'repair_orders.customer_id', '=', 'customers.id')
        ->join('vehicles', 'repair_orders.vehicle_id', '=', 'vehicles.id')
        ->select('repair_orders.*', 'customers.first_name', 'customers.email', 'vehicles.make', 'vehicles.model', 'vehicles.year')
        ->get();
    return response()->json($orders);
});
