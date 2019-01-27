<?php

namespace DiscordDaemon;

use Discord\Discord;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \DiscordDaemon
 * @version 0.0.1
 */
class Response
{
	use ResponseRoutes;

	/**
	 * @param \Discord\Discord $discrod
	 * @param mixed $message
	 *
	 * Constructor.
	 */
	public function __construct(Discord $discord, $message)
	{
		$this->discord = $discord;
		$this->message = $message;
	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		$reply = null;

		$guild_id = $this->message->channel->guild_id;
		$channel_id = $this->message->channel_id;
		$guild = $this->discord->guilds->get("id", $guild_id);
		$channel = $guild->channels->get("id", $channel_id);
		
		printf(
			"Recieved a message from %s: %s\n", 
			$this->message->author->username, 
			json_encode(
				$text = $this->message->content,
				JSON_UNESCAPED_SLASHES
			)
		);

		$reply = $this->getResponse($text, $guild, $channel, $this->message);

		if (isset($reply)) {
			$channel->sendMessage($reply)->then(function ($message) {
        		printf("The message was sent!");
    		})->otherwise(function ($e) {
    			printf("There was an error sending the message: %s\n", $e->getMessage());
    			printf("%s\n", $e->getTraceAsString());
    		});	
		}
	}
}
