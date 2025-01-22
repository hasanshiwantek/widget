<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stat;
use App\Models\Review;
use Illuminate\Support\Facades\Http;

class FetchCtspointStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:ctspoint-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update stats table based on Trustpilot reviews';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \Log::info('Fetching Trustpilot stats...');
        $url = 'https://widget.shiwantek.com/api/proxy/ctspoint';

        try {

            Stat::query()->where('brand', 3)->delete();

            // Fetch HTML content from the Trustpilot proxy URL
            $response = Http::get($url);

            if ($response->failed()) {
                $this->error("Failed to fetch data from $url");
                return;
            }

            $htmlContent = $response->body();

            // Load HTML content into DOMDocument
            $dom = new \DOMDocument();
            @$dom->loadHTML($htmlContent); // Suppress warnings for malformed HTML
            $xpath = new \DOMXPath($dom);

            // Extract data using XPath
            $reviewCountElement = $xpath->query('//p[contains(@class, "typography_body-l__v5JLj") and contains(@class, "typography_appearance-default__t8iAq")]');
            $ratingCountElement = $xpath->query('//span[contains(@class, "typography_heading-m__UdgHy") and contains(@class, "typography_appearance-default__t8iAq")]');
            $ratingStatusElement = $xpath->query('//span[contains(@class, "typography_body-l__v5JLj") and contains(@class, "typography_appearance-subtle__PYOVM") and contains(@class, "styles_text__r3o5y")]');
            $ratingImageElement = $xpath->query('//div[contains(@class, "star-rating_starRating__sdbkn") and contains(@class, "star-rating_medium__Oj7C9")]/img');

            // Parse values
            $reviewCount = $reviewCountElement->length > 0 ? $this->extractNumericValue($reviewCountElement->item(0)->textContent) : null;
            $ratingCount = $ratingCountElement->length > 0 ? trim($ratingCountElement->item(0)->textContent) : null;
            $ratingStatus = $ratingStatusElement->length > 0 ? $this->extractStatus($ratingStatusElement->item(0)->textContent) : null;
            $ratingImage = $ratingImageElement->length > 0 ? $ratingImageElement->item(0)->getAttribute('src') : null;

            // Save data into the stats table
            Stat::create([
            'brand' => 3,
            'count' => $reviewCount,
            'rating' => $ratingCount,
            'status' => $ratingStatus,
            'image' => $ratingImage,
            ]);

            $this->info('Trustpilot stats have been successfully saved.');
            \Log::info('Trustpilot stats saved successfully.', [
                'reviewCount' => $reviewCount,
                'ratingCount' => $ratingCount,
                'ratingStatus' => $ratingStatus,
                'ratingImage' => $ratingImage,
            ]);

        } catch (\Exception $e) {
            $this->error('Error fetching Trustpilot stats: ' . $e->getMessage());
            \Log::error('Error fetching Trustpilot stats: ' . $e->getMessage());
        }
    }

    /**
     * Extract numeric value from a string.
     */
    private function extractNumericValue($text)
    {
        preg_match('/\d+/', $text, $matches);
        return $matches[0] ?? null;
    }

    /**
     * Extract status from text after "•".
     */
    private function extractStatus($text)
    {
        $parts = explode('•', $text);
        return isset($parts[1]) ? trim($parts[1]) : null;
    }
}
