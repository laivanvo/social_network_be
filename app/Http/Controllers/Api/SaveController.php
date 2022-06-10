<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Save;


class SaveController extends ApiController
{
    public function index()
    {
        $saves = Save::with(['user', 'user.profile', 'post', 'post.user', 'post.user.profile'])->where('user_id', $this->currentUser()->id)->get();
        return response()->json([
            'user' => $this->currentUser()->with('profile')->first(),
            'saves' => $saves,
            'success' => 'send request successfully.'
        ]);
    }

    public function store($id) {
        $save = $this->currentUser()->saves()->where('post_id', $id);
        if (count($save->get())) {
            $save->delete();
        } else {
            $this->currentUser()->saves()->create([
                'post_id' => $id,
            ]);
        }
        return response()->json([
            'success' => 'successfully.'
        ]);
    }
}
