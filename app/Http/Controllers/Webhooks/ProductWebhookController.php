<?php

namespace App\Http\Controllers\Webhooks;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\ProductSyncService;
use App\Services\WebhookService;

class ProductWebhookController extends Controller
{

    public function __construct(
        private WebhookService $verifier,
        private ProductSyncService $sync
    ) {}
    public function handle(Request $request)
    {
        try {
             $this->verifier->verify($request);
            $this->sync->handleWebhook($request);
            return response('OK', 200);
        } catch (\Throwable $e) {
            Log::error('Webhook error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
          
            return response('Server error', 500);
        }
    }


   
 
  
}
