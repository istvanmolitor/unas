<?php

namespace Molitor\Unas\Console\Commands;

use Molitor\Unas\Repositories\UnasProductCategoryRepository;
use Illuminate\Console\Command;

class UnasProductCategoryImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop-categories:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Áruház kategória képeinek letöltése';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $repository = new UnasProductCategoryRepository();

        $shops = UnasShop::where('enabled', 1)->get();
        foreach ($shops as $shop) {
            $shopProductCategories = $shop->shopProductCategories()->whereNotNull('image_url')->whereNull(
                'file_id'
            )->get();
            foreach ($shopProductCategories as $shopProductCategory) {
                $repository->downloadImage($shopProductCategory);
                $this->info($shopProductCategory->image_url);
            }
        }

        return 0;
    }
}
