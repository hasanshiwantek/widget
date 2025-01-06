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
        return response()->json([
            'status' => true,
            'data' => $reviews
        ], 200);
    }
}
