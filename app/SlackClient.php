<?php namespace App;

use wrapi\slack\slack;

class SlackClient
{

    public function __construct ()
    {

        $this->slack = new slack (env ("SLACK_API_TOKEN"));

    }

    public function sendMessageToChannel ($channel, $message)
    {

        $response = $this->slack->chat->postMessage(array(
            "channel" => $channel,
            "text" => $message,
            "username" => env ("SLACK_BOT_USERNAME"),
            "as_user" => false,
            "parse" => "full",
            "link_names" => 1,
            "unfurl_links" => true,
            "unfurl_media" => false
          )
        );

        if ( ! $response["ok"])
            die (var_dump ($response));

    }


}