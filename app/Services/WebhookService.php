<?

namespace App\Services;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public function verify(Request $request): void
    {
        $hmac = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();

        $calculated = base64_encode(
            hash_hmac('sha256', $data, config('services.shopify.secret'), true)
        );

        if (!hash_equals($hmac, $calculated)) {
            abort(401);
        }
    }
    public function registerWebhooks($shop)
    {
        try {
            $token = Shop::where('shop', $shop)->value('access_token');

            $productsTopics =  [
                'https://bashfully-unhoarding-lenita.ngrok-free.dev/api/webhooks/products' =>  [
                    'products/create',
                    'products/update',
                    'products/delete',
                ],

            ]; 
             // web add other trigger topics for webhook
            foreach ($productsTopics as $address => $topics) {
                foreach ($topics as $topic) {
                    Http::withHeaders([
                        'X-Shopify-Access-Token' => $token,
                    ])->post("https://{$shop}/admin/api/2025-10/webhooks.json", [
                        'webhook' => [
                            'topic' => $topic,
                            'address' => $address,
                            'format' => 'json',
                        ]
                    ]);
                }
            }
            return 'Webhooks registered';
        } catch (\Throwable $e) {
            Log::error('Webhook registered error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response('Server error', 500);
        }
    }
}
