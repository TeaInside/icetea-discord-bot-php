<?php

namespace DiscordDaemon;

use Threaded;
use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\Voice\VoiceClient;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \DiscordDaemon
 * @version 0.0.1
 */
class Response extends Threaded
{
	/**
	 * @param \Discord\Discord $discrod
	 * @param mixed $message
	 *
	 * Constructor.
	 */
	public function __construct(Discord $discord, $message)
	{
		var_dump(123123);
		$this->discord = $discord;
		$this->message = $message;
	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		var_dump("run");
		$reply = null;

		$guild_id = $this->message->channel->guild_id;
		$channel_id = $this->message->channel_id;
		$guild = $this->discord->guilds->get("id", $guild_id);
		$channel = $guild->channels->get("id", $guild);
		
		printf("Recieved a message from %s: %s\n", $this->message->author->username, json_encode(
			$text = $this->message->content
		));

		if (strtolower($text) === "ping") {
			$reply = "Pong!";
		}

		if (isset($reply)) {
			$channel->sendMessage($reply)->then(function ($message) {
        		echo "The message was sent!", PHP_EOL;
    		})->otherwise(function ($e) {
        		echo "There was an error sending the message: {$e->getMessage()}", PHP_EOL;
        		echo $e->getTraceAsString() . PHP_EOL;
    		});	
		}
	}
}
