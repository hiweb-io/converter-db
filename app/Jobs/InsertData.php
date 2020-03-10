<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class InsertData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $page;
    protected $tableName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tableName, $page)
    {
        $this->tableName = $tableName;
        $this->page = $page;
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

        $limit = 100;

        // Get data table input
        $inputData = $db->table($this->tableName)->skip($this->page*$limit)->take($limit)->get();

        // check if data
        if (count($inputData) > 0) {

            // connect db output
            $dbOutput = DB::connection('output');

            foreach ($inputData as $data) {

                // Update pr insert data
                $dbOutput->table($this->tableName)->updateOrInsert((array)$data);

            }

            if (count($inputData) == $limit) {
                InsertData::dispatch($this->tableName, $this->page+1);
            }

        }
    }
}
