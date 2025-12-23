<?php 
namespace App\Services;

use Illuminate\Support\Facades\Http;

class ShopifyGraphqlService
{
    public function query(string $shop, string $token, string $query, array $variables = [])
    {
        return Http::withHeaders([
            'X-Shopify-Access-Token' => $token,
            'Content-Type' => 'application/json',
        ])->post("https://{$shop}/admin/api/2025-10/graphql.json", [
            'query' => $query,
            'variables' => $variables,
        ])->json();
    }
}

