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

            $insertData = [];
            foreach ($inputData as $data) {

                if ($this->tableName == "carts") {

                    if ($data->quantity and (int)$data->quantity > 100000000) {
                        $data->quantity = '0';
                    }

                    if ($data->subtotal and (int)$data->subtotal > 100000000) {
                        $data->subtotal = 0;
                    }

                    if ($data->total and (int)$data->total > 100000000) {
                        $data->total = 0;
                    }

                }

                if ($this->tableName == "cart_items") {

                    if ($data->unit_price and (int)$data->unit_price > 100000000) {
                        $data->unit_price = 0;
                    }

                    if ($data->total and (int)$data->total > 100000000) {
                        $data->total = 0;
                    }

                    if ($data->quantity and (int)$data->quantity > 100000000) {
                        $data->quantity = '0';
                    }

                }

                if ($this->tableName == "coupons") {

                    if ((int)$data->usage_limit > 100000000) {

                        $data->usage_limit = 100000000;
                    }

                }

                if($this->tableName == "telescope_entries" or $this->tableName == "telescope_entries_tags" or $this->tableName == "telescope_monitoring") {

                        // Insert data
                        $dbOutput->table($this->tableName)->updateOrInsert((array)$data);

                } else {
                    $insertData[] = (array)$data;
                }

            }

            // Insert
            $dbOutput->table($this->tableName)->insert($insertData);

            if (count($inputData) == $limit) {
                InsertData::dispatch($this->tableName, $this->page+1);
            }

        }
    }
}
