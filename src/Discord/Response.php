<?php

namespace Discord;

use Discord\Discord;
use Discord\Parts\Channel\Message;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 */
final class Response
{
	/**
	 * @var \Discord\Discord
	 */	
	private $discord;

	/**
	 * @var \Discord\Parts\Channel\Message
	 */
	private $message;

	/**
	 * @param \Discord\Discord
	 */
	public function __construct(Discord &$discord)
	{
		$this->discord = $discord;
	}

	/**
	 * @param \Discord\Parts\Channel\Message $message
	 * @return void
	 */
	public function onMessage(Message &$message)
	{
		printf("Recieved a message from %s: %s\n", $message->author->username, json_encode(
			$text = &$message->content
		));

		if (!($pid = pcntl_fork())) {

			$guild_id = $message->channel->guild_id;
			$channel_id = $message->channel_id;
			$guild = $this->discord->guilds->get("id", $guild_id);
			$channel = $guild->channels->get("id", $channel_id);

			cli_set_process_title("discordd receiver --guild_id={$guild} --channel_id={$channel_id}");

			sleep(3);

			$reply = "me";

			if (preg_match("/^[\/\!\.\~]ping$/", $text)) {
				$reply = "Pong!";
				goto sendResponse;
			}


			if (preg_match("/^(?:[\/\!\.\~]sh[\s\n])(.*)$/", $text, $m)) {

				$f = "/tmp/".substr(md5($m[1])), 0, 5).".sh";
				file_put_contents($f, "#!/usr/bin/env bash\n".$sr[1]);
				shell_exec("sudo chmod +x {$f}");

				if (in_array($message->author->username, SUDOERS)) {	
					$reply = shell_exec("{$text} 2>&1");
					// $reply = "handled";
				} else {
					// $reply = shell_exec("cd /home/limited && sudo -u limited {$f} 2>&1");
					$reply = "Invalid user";
				}

				unlink($f);
				goto sendResponse;
			}



			sendResponse:

				cli_set_process_title("discordd send --guild_id={$guild} --channel_id={$channel_id} --content={$reply}");

				$channel->sendMessage($reply)->then(function ($message) {
	        		echo "The message was sent!", PHP_EOL;
	    		})->otherwise(function ($e) {
	        		echo "There was an error sending the message: {$e->getMessage()}", PHP_EOL;
	        		echo $e->getTraceAsString() . PHP_EOL;
	    		});

			exit;
		}

		return $pid;
	}
}
