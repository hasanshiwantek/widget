<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Review;

class FetchCtspointReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:ctspoint-reviews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and update Cts Point Trustpilot reviews';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $baseUrl = 'https://widget.shiwantek.com/api/proxy/ctspoint?page=1';
        $url = $baseUrl; // Start with the first page
        $hasMorePages = true;
        $page = 1;
    
        try {
            // Review::truncate(); // Clear old data before fetching new data
    
            Review::query()->where('brand', 3)->delete();
            while ($hasMorePages) {
                $url = "https://widget.shiwantek.com/api/proxy/ctspoint?page={$page}";
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
                    // $reviewer = $this->getText($xpath, $card, './/span[@data-consumer-name-typography="true"]');
                    // $location = $this->getText($xpath, $card, './/div[@data-consumer-country-typography="true"]');
                    // $totalReviews = $this->getText($xpath, $card, './/span[@data-consumer-reviews-count-typography="true"]');
                    // $date = $this->getText($xpath, $card, './/time[@data-service-review-date-time-ago="true"]');
                    // $reviewHeading = $this->getText($xpath, $card, './/h2[@data-service-review-title-typography="true"]');
                    // $reviewContent = $this->getText($xpath, $card, './/p[@data-service-review-text-typography="true"]');
                    // $dateOfExperience = $this->getText($xpath, $card, './/p[@data-service-review-date-of-experience-typography="true"]');
                    // $reviewUrl = $xpath->query('.//a[@data-review-title-typography="true"]', $card)?->item(0)->getAttribute('href') ?? '';
                    // $stars = $xpath->query('.//div[@class="styles_reviewHeader__xV2js"]//img', $card)?->item(0)->getAttribute('src') ?? '';

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
                    $starsNode = $xpath->query('.//div[contains(@class, "star-rating_starRating__")]/img', $card)?->item(0);
                    $stars = $starsNode ? $starsNode->getAttribute('src') : '';
    
                    Review::create([
                        'brand' => 3,
                        // 'reviewer' => $reviewer,
                        // 'location' => $location,
                        // 'totalReviews' => (int) filter_var($totalReviews, FILTER_SANITIZE_NUMBER_INT),
                        // 'date' => $date,
                        // 'reviewHeading' => $reviewHeading,
                        // 'reviewContent' => $reviewContent,
                        // 'dateOfExperience' => str_replace('Date of experience: ', '', $dateOfExperience),
                        // 'stars' => $stars,
                        // 'url' => 'https://www.trustpilot.com' . $reviewUrl,
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
    
                // Find the "Next page" link
                // $nextPageLink = $xpath->query('//a[@aria-label="Next page"]')?->item(0);
                // if ($nextPageLink) {
                //     $nextPageUrl = 'https://www.trustpilot.com' . $nextPageLink->getAttribute('href');
                //     $this->info("Found Next page link: $nextPageUrl");
    
                //     if (filter_var($nextPageUrl, FILTER_VALIDATE_URL)) {
                //         $url = $nextPageUrl; // Update the URL for the next iteration
                //     } else {
                //         $this->info("Next page URL is invalid. Stopping.");
                //         $hasMorePages = false;
                //     }
                // } else {
                //     $this->info("No Next page link found at URL: $url. Stopping.");
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
