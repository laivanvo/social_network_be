<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Block;
use Illuminate\Http\Request;

class BlockController extends ApiController
{
    public function store(Request $request) {
        Block::create($request->all());
        return response()->json([
            'success' => true,
        ], 200);
    }

    public function destroy(Request $request) {
        $block = Block::where('post_id', $request->post_id)->where('user_id', $request->user_id)->first();
        $block->delete();
        return response()->json([
            'success' => true,
        ], 200);
    }
}
