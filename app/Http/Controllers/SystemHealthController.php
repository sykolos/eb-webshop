<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

final class SystemHealthController extends Controller
{
    public function live(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }

    public function ready(): JsonResponse
    {
        $checks = ['database' => false, 'storage' => false];
        try {
            DB::connection()->getPdo();
            $checks['database'] = true;
        } catch (Throwable) {
        }

        $checks['storage'] = is_writable(storage_path());
        $ready = ! in_array(false, $checks, true);

        return response()->json(['status' => $ready ? 'ok' : 'degraded', 'checks' => $checks], $ready ? 200 : 503);
    }
}
