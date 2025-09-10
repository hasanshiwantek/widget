<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Review;

class FetchTrustpilotReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:trustpilot-reviews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and update Newtownspares Trustpilot reviews';

     /**
     * Execute the console command.
     */
    public function handle()
    {
        \Log::info('fetch:trustpilot-reviews is running');

        $baseUrl = 'https://widget.advertsedge.com/api/proxy/newtownspares?page=1';
        $url = $baseUrl;
        $page = 1;
        $hasMorePages = true;

        try {
            Review::query()->where('brand', 1)->delete();

            while ($hasMorePages) {
                $url = "https://widget.advertsedge.com/api/proxy/newtownspares?page={$page}";
                $this->info("Fetching page $page: $url");

                $response = Http::get($url);

                if ($response->failed()) {
                    $this->error("Failed to fetch URL: $url");
                    break;
                }

                $htmlContent = $response->body();
                $dom = new \DOMDocument();
                @$dom->loadHTML($htmlContent);
                $xpath = new \DOMXPath($dom);

                $reviewCards = $xpath->query('//div[contains(@class, "styles_cardWrapper__g8amG")]');

                if ($reviewCards->length === 0) {
                    $this->info("No reviews found at URL: $url.");
                    break;
                }

                foreach ($reviewCards as $card) {
                    $reviewer = $this->getText($xpath, $card, './/span[@data-consumer-name-typography="true"]');
                    $location = $this->getText($xpath, $card, './/span[@data-consumer-country-typography="true"]');
                    $totalReviews = $this->getText($xpath, $card, './/span[@data-consumer-reviews-count-typography="true"]');
                    $date = $this->getText($xpath, $card, './/time[@data-service-review-date-time-ago="true"]');
                    $reviewHeading = $this->getText($xpath, $card, './/h2[@data-service-review-title-typography="true"]');
                    $reviewContent = $this->getText($xpath, $card, './/p[@data-service-review-text-typography="true"]');
                    $dateOfExperience = $this->getText($xpath, $card, './/p[@data-service-review-date-of-experience-typography="true"]/span');

                    // Safe href extraction
                    $reviewUrlNode = $xpath->query('.//a[@data-review-title-typography="true"]', $card)?->item(0);
                    $reviewUrl = $reviewUrlNode ? 'https://www.trustpilot.com' . $reviewUrlNode->getAttribute('href') : '';

                    // Safe stars img src
                    $starsNode = $xpath->query('.//img[contains(@class, "CDS_StarRating_starRating__614d2e")]', $card)?->item(0);
                    $stars = $starsNode ? $starsNode->getAttribute('src') : '';

                    Review::create([
                        'brand' => 1,
                        'reviewer' => $reviewer,
                        'location' => $location,
                        'totalReviews' => (int) filter_var($totalReviews, FILTER_SANITIZE_NUMBER_INT),
                        'date' => $date,
                        'reviewHeading' => $reviewHeading,
                        'reviewContent' => $reviewContent,
                        'dateOfExperience' => trim(str_replace('Date of experience:', '', $dateOfExperience)),
                        'stars' => $stars,
                        'url' => $reviewUrl,
                    ]);
                }

                $this->info("Fetched reviews from URL: $url.");
                $page++;

                // Pagination: look for the "Next page" button
                // $nextPageNode = $xpath->query('//a[@data-pagination-button-next-link="true"]')?->item(0);
                // if ($nextPageNode) {
                //     $relativeUrl = $nextPageNode->getAttribute('href');
                //     // $url = filter_var($relativeUrl, FILTER_VALIDATE_URL)
                //     //     ? $relativeUrl
                //     //     : 'https://widget.shiwantek.com' . $relativeUrl;

                //     if (str_starts_with($relativeUrl, '/api/proxy/')) {
                //         $url = 'https://widget.shiwantek.com' . $relativeUrl;
                //         $this->info("Next page URL: $url");
                //     } else {
                //         $this->warn("Next page href is outside proxy: $relativeUrl. Stopping.");
                //         $hasMorePages = false;
                //     }
                    
                // } else {
                //     $this->info("No more pages.");
                //     $hasMorePages = false;
                // }
            }

            $this->info('Trustpilot reviews have been successfully updated.');
        } catch (\Exception $e) {
            $this->error('Error fetching Trustpilot reviews: ' . $e->getMessage());
        }
    }

    /**
     * Helper function to fetch text content safely from the DOM.
     */
    private function getText($xpath, $context, $query)
    {
        $node = $xpath->query($query, $context)?->item(0);
        return $node ? trim($node->textContent) : null;
    }

}
