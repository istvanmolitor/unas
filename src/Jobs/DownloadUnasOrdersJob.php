<?php

declare(strict_types=1);

namespace Molitor\Unas\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Molitor\Unas\Models\UnasShop;
use Molitor\Unas\Services\UnasOrderService;

class DownloadUnasOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public UnasShop $shop
    ) {}

    public function handle(UnasOrderService $unasOrderService): void
    {
        $unasOrderService->downloadOrders($this->shop);
    }
}