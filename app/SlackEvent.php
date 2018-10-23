<?php namespace App;

use Illuminate\Http\Request;

class SlackEvent
{

    public function __construct (Request $request)
    {

        $this->command     = $request->command;
        $this->text        = $request->text;
        $this->responseUrl = $request->response_url;
        $this->triggerId   = $request->trigger_id;
        $this->userId      = $request->user_id;
        $this->username    = $request->user_name;
        $this->channelId   = $request->channel_id;

    }

}