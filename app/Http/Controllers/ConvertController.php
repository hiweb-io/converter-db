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

            return redirect()->back()->with(['success' => 'Convert successfully']);

        } catch (\Exception $e) {

            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);

        }

    }
}
