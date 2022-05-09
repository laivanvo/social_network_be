<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\ApiController;
use App\Models\BgImage;


class BgImageController extends ApiController
{
    public function index() {
        $bgImages = BgImage::all();
        return response()->json([
            'success' => true,
            'bgImages' => $bgImages,
        ], 200);
    }
}
