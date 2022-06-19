<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Post;
use App\Models\FileUpload;
use App\Models\Profile;
use Illuminate\Http\Request;
use App\Models\Relation;


class RelationshipController extends ApiController
{
    public function index($id)
    {
        $relation = $this->currentUser()->requestToMes();
        if (count($relation->get())) {
            $relation = $relation->where('from', $id)->first();
            if ($relation) {
                return response()->json([
                    'type' => $relation->type,
                    'success' => 'send request successfully.'
                ]);
            }
        }
        $relation = $this->currentUser()->requestByMes();
        if (count($relation->get())) {
            $relation = $relation->where('to', $id)->first();
            if ($relation) {
                return response()->json([
                    'type' => $relation->type,
                    'success' => 'send request successfully.'
                ]);
            }
        }
        return response()->json([
            'type' => 'none',
            'success' => 'send request successfully.'
        ]);
    }


    public function listRequest()
    {
        $from = $this->currentUser()->requestToMes()->where('type', 'request')->pluck('from')->toArray();
        if ($from) {
            $profiles = Profile::whereIn('user_id', $from)->get();
            return response()->json([
                'profiles' => $profiles,
                'success' => 'send request successfully.'
            ]);

        } else {
            return response()->json([
                'profiles' => null,
                'success' => 'send request successfully.'
            ]);
        }
    }

    public function listSuggest()
    {
        $from = $this->currentUser()->requestToMes()->pluck('from')->toArray();
        $to = $this->currentUser()->requestByMes()->pluck('to')->toArray();
        if (!count($from)) {
            $from = [];
        }
        if (!count($to)) {
            $to = [];
        }
        for ($i = 0; $i < count($to); $i++) {
            array_push($from, $to[$i]);
        }
        $profiles = Profile::whereNotIn('user_id', $from)->get();
        return response()->json([
            'profiles' => $profiles,
            'success' => 'send request successfully.'
        ]);
    }

    public function listSend()
    {
        $from = $this->currentUser()->requestByMes()->where('type', 'request')->pluck('to')->toArray();
        $profiles = Profile::whereIn('user_id', $from)->get();
        return response()->json([
            'profiles' => $profiles,
            'success' => 'send request successfully.'
        ]);
    }
    public function listFriend()
    {
        $from = Relation::where('to', $this->currentUser()->id)->where('type', 'friend')->pluck('from')->toArray();
        $to = Relation::where('from', $this->currentUser()->id)->where('type', 'friend')->pluck('to')->toArray();
        if (!count($from)) {
            $from = [];
        }
        if (!count($to)) {
            $to = [];
        }
        for ($i = 0; $i < count($to); $i++) {
            array_push($from, $to[$i]);
        }
        $relations = Profile::whereIn('user_id', $from)->get();
        return response()->json([
            'profiles' => $relations,
            'success' => 'send request successfully.'
        ]);
    }

    public function listFriendByAddress()
    {
        $from = Relation::where('to', $this->currentUser()->id)->where('type', 'friend')->pluck('from');
        $to = Relation::where('from', $this->currentUser()->id)->where('type', 'friend')->pluck('to');
        if (!count($from)) {
            $from = [];
        }
        if (!count($to)) {
            $to = [];
        }
        for ($i = 0; $i < count($to); $i++) {
            array_push($from, $to[$i]);
        }
        $relations = Profile::whereIn('user_id', $from)->where('address', $this->currentUser()->profile->address)->get();
        return response()->json([
            'relations' => $relations,
            'success' => 'send request successfully.'
        ]);
    }

    public function listFriendByBirthday()
    {
        $from = Relation::where('to', $this->currentUser()->id)->where('type', 'friend')->pluck('from');
        $to = Relation::where('from', $this->currentUser()->id)->where('type', 'friend')->pluck('to');
        if (!count($from)) {
            $from = [];
        }
        if (!count($to)) {
            $to = [];
        }
        for ($i = 0; $i < count($to); $i++) {
            array_push($from, $to[$i]);
        }
        $relations = Profile::whereIn('user_id', $from)->where('birthday', $this->currentUser()->profile->birthday)->get();
        return response()->json([
            'relations' => $relations,
            'success' => 'send request successfully.'
        ]);
    }

    public function sendRequest($id)
    {
        Relation::create([
            'from' => $this->currentUser()->id,
            'to' => $id,
            'type' => 'request',
        ]);
        return response()->json([
            'success' => 'send request successfully.'
        ]);
    }

    public function check($id)
    {
        $from = Relation::where('to', $id)->where('from', $this->currentUser()->id)->first();
        if ($from) {
            return response()->json([
                'success' => 'send request successfully.',
                'type' => $from->type . 'ByMe',
                'relation' => $from,
            ]);
        }
        $to = Relation::where('from', $id)->where('to', $this->currentUser()->id)->first();
        if ($to) {
            return response()->json([
                'success' => 'send request successfully.',
                'type' => $to->type . "ToMe",
                'relation' => $to,
            ]);
        }
        return response()->json([
            'success' => 'send request successfully.',
            'type' => 'none',
            'relation' => null,
        ]);
    }

    public function destroy($id) {
        Relation::findOrFail($id)->delete();
        return response()->json([
            'success' => 'send request successfully.',
        ]);
    }

    public function accept($id) {
        $relation = Relation::findOrfail($id);
        $relation->type = "friend";
        $relation->save();
        return response()->json([
            'success' => 'send request successfully.',
        ]);
    }

    public function sameFriend($id) {
        $from = Relation::where('to', $this->currentUser()->id)->where('type', 'friend')->pluck('from')->toArray();
        $to = Relation::where('from', $this->currentUser()->id)->where('type', 'friend')->pluck('to')->toArray();
        if (!count($from)) {
            $from = [];
        }
        if (!count($to)) {
            $to = [];
        }
        for ($i = 0; $i < count($to); $i++) {
            array_push($from, $to[$i]);
        }

        $from1 = Relation::where('to', $id)->where('type', 'friend')->pluck('from')->toArray();
        $to = Relation::where('from', $id)->where('type', 'friend')->pluck('to')->toArray();
        if (!count($from1)) {
            $from1 = [];
        }
        if (!count($to)) {
            $to = [];
        }
        for ($i = 0; $i < count($to); $i++) {
            array_push($from1, $to[$i]);
        }
        $count = 0;
        foreach ($from1 as $id) {
            if (in_array($id, $from)) {
                $count++;
            }
        }
        return response()->json([
            'success' => 'send request successfully.',
            'count' => $count,
        ]);
    }
}
