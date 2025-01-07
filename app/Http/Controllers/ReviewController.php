<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Stat;

class ReviewController extends Controller
{
    //

    public function index(){
        $reviews = Review::all();
        $count = $reviews -> count();

        return response()->json([
            'status' => true,
            'data' => $reviews,
            'count' => $count
        ], 200);
    }

    public function stats(){
        $stats = Stat::latest('updated_at')->first();
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
