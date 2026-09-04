<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/proxy/newtownspares', function (Request $request) {
    // Get the page number from the query parameters (default to page 1 if not provided)
    $page = $request->query('page', 1);

    // Construct the URL with the page number
    $url = 'https://www.trustpilot.com/review/newtownspares.com?page=' . $page;

    // Fetch the HTML content from Trustpilot
    $response = Http::get($url);

    // Check if the response is successful
    if ($response->successful()) {
        // Return the HTML content of the page
        return $response->body();
    }

    // Return an error response if fetching fails
    return response()->json(['error' => 'Unable to fetch data'], 500);
});

// Route::get('/proxy/serverblink', function (Request $request) {
//     // Get the page number from the query parameters (default to page 1 if not provided)
//     $page = $request->query('page', 1);

//     // Construct the URL with the page number
//     $url = 'https://www.trustpilot.com/review/serverblink.com?page=' . $page;

//     // Fetch the HTML content from Trustpilot
//     $response = Http::get($url);

//     // Check if the response is successful
//     if ($response->successful()) {
//         // Return the HTML content of the page
//         return $response->body();
//     }

//     // Return an error response if fetching fails
//     return response()->json(['error' => 'Unable to fetch data'], 500);
// });

Route::get('/proxy/serverblink', function (Request $request) {
    $page = $request->query('page', 1);

    $trustpilotUrl = 'https://www.trustpilot.com/review/serverblink.com?page=' . $page;

    try {
        $response = Http::withToken(env('BROWSER_WORKER_KEY'))
            ->timeout(90)
            ->post(env('BROWSER_WORKER_URL') . '/render', [
                'url' => $trustpilotUrl,
            ]);

        if ($response->successful()) {
            return $response->json('html');
        }

        return response()->json([
            'error' => 'Unable to fetch data',
            'worker_status' => $response->status(),
            'worker_response' => $response->body(),
        ], 500);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Browser worker connection failed',
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/proxy/ctspoint', function (Request $request) {
    // Get the page number from the query parameters (default to page 1 if not provided)
    $page = $request->query('page', 1);

    // Construct the URL with the page number
    $url = 'https://www.trustpilot.com/review/ctspoint.com?page=' . $page;

    // Fetch the HTML content from Trustpilot
    $response = Http::get($url);

    // Check if the response is successful
    if ($response->successful()) {
        // Return the HTML content of the page
        return $response->body();
    }

    // Return an error response if fetching fails
    return response()->json(['error' => 'Unable to fetch data'], 500);
});

Route::get('/reviews-nts', [ReviewController::class, 'reviewsNts']);
Route::get('/stats-nts', [ReviewController::class, 'statsNts']);

Route::get('/reviews-sb', [ReviewController::class, 'reviewsSb']);
Route::get('/stats-sb', [ReviewController::class, 'statsSb']);

Route::get('/reviews-cts', [ReviewController::class, 'reviewsCts']);
Route::get('/stats-cts', [ReviewController::class, 'statsCts']);
