<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stat;
use App\Models\Review;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateStatsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:stats';

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
        $url = 'https://trust.advertsedge.com/api/proxy/newtownspares';

        try {

            Stat::query()->where('brand', 1)->delete();

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
            $reviewCountElement = $xpath->query('//p[contains(@class, "styles_reviewCount__NXlel")]');
            $ratingCountElement = $xpath->query('//p[contains(@class, "styles_trustScore__MVJJI")]');
            $ratingStatusElement = $xpath->query('//h3[contains(@class, "styles_starRatingName__njtqK")]');
            $ratingImageElement = $xpath->query('//img[contains(@class, "CDS_StarRating_starRating__614d2e") and contains(@class, "CDS_StarRating_starRatingResponsive__614d2e")]');

            // Parse values
            $reviewCount = $reviewCountElement->length > 0 ? $this->extractNumericValue($reviewCountElement->item(0)->textContent) : null;
            $ratingCount = $ratingCountElement->length > 0 ? trim($ratingCountElement->item(0)->textContent) : null;
            $ratingStatus = $ratingStatusElement->length > 0 ? $ratingStatusElement->item(0)->textContent : null;
            $ratingImage = $ratingImageElement->length > 0 ? $ratingImageElement->item(0)->getAttribute('src') : null;

            // 6. Validate the data BEFORE deleting anything
            if (
                $reviewCount === null ||
                $ratingCount === null ||
                $ratingStatus === null
            ) {
                $this->error('Unable to extract valid Trustpilot stats.');

                Log::error('Invalid Trustpilot data extracted', [
                    'reviewCount' => $reviewCount,
                    'ratingCount' => $ratingCount,
                    'ratingStatus' => $ratingStatus,
                    'ratingImage' => $ratingImage,
                ]);

                return Command::FAILURE;
            }

            // 7. Only NOW modify the database
            DB::transaction(function () use (
                $reviewCount,
                $ratingCount,
                $ratingStatus,
                $ratingImage
            ) {

                // Delete old data
                Stat::where('brand', 1)->delete();

                // Insert new data
                Stat::create([
                    'brand' => 1,
                    'count' => $reviewCount,
                    'rating' => $ratingCount,
                    'status' => $ratingStatus,
                    'image' => $ratingImage,
                ]);
            });

            $this->info('Trustpilot stats have been successfully saved.');
            \Log::info('Trustpilot stats saved successfully.', [
                'reviewCount' => $reviewCount,
                'ratingCount' => $ratingCount,
                'ratingStatus' => $ratingStatus,
                'ratingImage' => $ratingImage,
            ]);

        } catch (\Exception $e) {
            $this->error('Error fetching Trustpilot stats: ' . $e->getMessage());
            Log::error('Error updating Trustpilot stats', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return Command::FAILURE;
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
