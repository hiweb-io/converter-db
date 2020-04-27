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
            $ids = [];
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

                if ($this->tableName == "images") {
                    if (strlen($data->path) > 255) {
                        $data->path = substr($data->path,0 , strpos($data->path, "?"));
                    }
                }

                if ($this->tableName == "coupons") {

                    if ((int)$data->usage_limit > 100000000) {

                        $data->usage_limit = 100000000;
                    }

                }

                if($this->tableName == 'variants') {
                    if (strpos($data->compare_at_price, ',')) {
                        $data->compare_at_price = str_replace(',', '.', $data->compare_at_price);
                    }
                    if (strpos($data->price, ',')) {
                        $data->price = str_replace(',', '.', $data->price);
                    }
                }

                if ($this->tableName == "images") {
                    if (!$data->hash) {
                        $data->hash = sha1($data->path.'---'.$data->disk);
                    }
                }

                if ($this->tableName == "transactions") {
                    if (strpos($data->amount, '%2e')) {
                        $data->amount = str_replace('%2e', '.', $data->amount);
                    }
                }


                if($this->tableName == 'products') {
                    if (strpos($data->min_price_compare_at, ',')) {
                        $data->min_price_compare_at = str_replace(',', '.', $data->min_price_compare_at);
                    }
                    if (strpos($data->min_price, ',')) {
                        $data->min_price = str_replace(',', '.', $data->min_price);
                    }
                    if (strlen($data->preview) > 255) {
                        $data->preview = substr($data->preview,0 , 255);
                    }
                }

                $insertData[] = (array)$data;

                if (isset($data->id)) {
                    $ids[] = $data->id;
                }

            }

            $dispatchNext = true;
            \DB::connection('output')->transaction(function() use ($ids, $dbOutput, $insertData, $limit, &$dispatchNext) {

                // Try to count
                if ($dbOutput->table($this->tableName)->whereIn('id', $ids)->count() === $limit) {
                    dump('Duplicated - Not gonna dispatch next job');
                    $dispatchNext = false;
                    return;
                }

                // Insert
                if ($ids and count($ids)) {
                    $dbOutput->table($this->tableName)->whereIn('id', $ids)->delete();
                }

                $dbOutput->table($this->tableName)->insert($insertData);

                dump('Inserted '.$limit.' rows to table: '.$this->tableName.'. First ID: '.(@$ids[0]).', last ID: '.(@$ids[count($ids) - 1]));

            });

            if (count($inputData) == $limit and $dispatchNext) {
                InsertData::dispatch($this->tableName, $this->page+1);
            }

        }
    }
}
