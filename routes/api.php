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

Route::get('/proxy/trustpilot', function (Request $request) {
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

Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/stats', [ReviewController::class, 'stats']);
