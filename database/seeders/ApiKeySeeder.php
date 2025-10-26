<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use Illuminate\Database\Seeder;

class ApiKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apiKeys = [
            [
                'source' => 'newsapi.org',
                'api_url' => 'https://newsapi.org/v2/everything',
                'api_key' => '1fb6367d30514b6990609ce8e43bd630',
            ],
            [
                'source' => 'theguardian.com',
                'api_url' => 'https://content.guardianapis.com/search',
                'api_key' => '8395b69f-f538-4585-bf9a-ed7d520d860f',
            ],
            [
                'source' => 'nytimes.com',
                'api_url' => 'https://api.nytimes.com/svc/search/v2/articlesearch.json',
                'api_key' => 'ckiTzLMKAHdcjGaQ815AGwHLywLG884A',
            ],
        ];

        foreach ($apiKeys as $apiKey) {
            ApiKey::updateOrCreate(
                ['source' => $apiKey['source']],
                $apiKey
            );
        }
    }
}
