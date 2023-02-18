<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Models\ProductMailBox;
use App\Models\Setting;
use App\Traits\Api\DeleteExpiredCartCroneJobTrait;
use App\Traits\Api\ShoppingCartTimeCroneTrait;
use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShoppingCartTime extends Command
{
    use ShoppingCartTimeCroneTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopping-cart:time';
    private $cart, $croneJobError, $croneJobSuccess, $setting, $productMailBox;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->cart = new Cart();
        $this->setting = new Setting();
        $this->productMailBox = new ProductMailBox();
        $this->croneJobError = 'ShoppingCartTime Crone Job Error : ';
        $this->croneJobSuccess = 'ShoppingCartTime Crone Job Run Successfully';
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            DB::beginTransaction();

            // Expire Cart Item Crone
            if(!$this->expireCartItemCrone())
            {
                DB::rollBack();
                return Log::info($this->croneJobError.GENERAL_ERROR_MESSAGE);
            }

            // Delete Expire Cart
            if(!$this->deleteExpiredCartCrone())
            {
                DB::rollBack();
                return Log::info($this->croneJobError.GENERAL_ERROR_MESSAGE);
            }
            DB::commit();
            return Log::info($this->croneJobSuccess);
        }catch (QueryException $e) {
            DB::rollBack();
            return Log::info($this->croneJobError.$e->getMessage());
        }catch (Exception $e) {
            DB::rollBack();
            return Log::info($this->croneJobError.$e->getMessage());
        }

        return Command::SUCCESS;
    }


}
