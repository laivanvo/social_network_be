<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Notification;


class NotificationController extends ApiController
{
    public function index()
    {
        $noties = Notification::with(['userFrom', 'userFrom.profile', 'post', 'post.user', 'post.user.profile', 'userTO', 'userTo.profile'])->where('to', $this->currentUser()->id)->get();
        return response()->json([
            'noties' => $noties,
            'success' => 'send request successfully.'
        ]);
    }
}
