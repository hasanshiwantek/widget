<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Stat;

class ReviewController extends Controller
{
    //

    public function reviewsNts(){
        $reviews = Review::query()
        ->where('brand', 1) // Replace 'brand' with the actual column name
        ->get();
        $count = $reviews -> count();

        return response()->json([
            'status' => true,
            'data' => $reviews,
            'count' => $count
        ], 200);
    }

    public function statsNts(){
        $stats = Stat::query()
        ->where('brand', 1) // Filter records where brand equals 1
        ->latest('updated_at') // Order by updated_at in descending order
        ->first();
        // $count = $stats -> count();


        if ($stats) {
            // $stats is not null, so you can use it
            $count = $stats->count();
        } else {
            // $stats is null
            $count = 0; // or handle accordingly
        }

        return response()->json([
            'status' => true,
            'data' => $stats,
        ], 200);
    }

    public function reviewsSb(){
        $reviews = Review::query()
        ->where('brand', 2) // Replace 'brand' with the actual column name
        ->get();
        $count = $reviews -> count();

        return response()->json([
            'status' => true,
            'data' => $reviews,
            'count' => $count
        ], 200);
    }

    public function statsSb(){
        $stats = Stat::query()
        ->where('brand', 2) // Filter records where brand equals 1
        ->latest('updated_at') // Order by updated_at in descending order
        ->first();
        // $count = $stats -> count();


        if ($stats) {
            // $stats is not null, so you can use it
            $count = $stats->count();
        } else {
            // $stats is null
            $count = 0; // or handle accordingly
        }

        return response()->json([
            'status' => true,
            'data' => $stats,
        ], 200);
    }
}
