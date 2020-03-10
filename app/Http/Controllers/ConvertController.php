<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\ConvertSqliteToMysql;

class ConvertController extends Controller
{
    public function sqliteToMysql(Request $request)
    {
        try {

            ConvertSqliteToMysql::dispatch();

            return response()->json([
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 400);

        }

    }
}
