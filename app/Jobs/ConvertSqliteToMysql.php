<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use App\Jobs\InsertData;

class ConvertSqliteToMysql implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // connect bd input
        $db = DB::connection('input');

        // list table name
        $tables = $db->select("SELECT * FROM sqlite_master WHERE type='table' ORDER BY name;");

        if (count($tables) > 0) {

            foreach ($tables as $table) {

                if ($table->name !== 'sqlite_sequence' and $table->name !== 'jobs' and $table->name !== 'failed_jobs' and $table->name !== 'password_resets') {

                    // connect db output
                    // $dbOutput = DB::connection('output');

                    // // create table if not exist
                    // $sql = str_replace('"', '', $table->sql);
                    // $sql = str_replace('autoincrement', 'AUTO_INCREMENT', $sql);
                    // $sql = str_replace('varchar', 'varchar(255)', $sql);
                    // $sql = str_replace('datetime', 'TIMESTAMP', $sql);
                    // $sql = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $sql);

                    // $tableOutput = $dbOutput->select($sql);

                    InsertData::dispatch($table->name, 0);

                }

            }

        }
    }
}
