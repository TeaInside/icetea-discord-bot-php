<?php

namespace Discord;

use Discord\Discord;

/**
* @author Ammar Faizi <ammarfaizi2@gmail.com>
*/
final class Bot
{
	
	private $discord;

	public function __construct(Discord $discord)
	{
		$this->discord = $discord;
	}

	public function run()
	{
		echo 11133;
		$this->discord->on('ready', function ($discord) {
			echo "Bot is ready.", PHP_EOL;

			$discord->on('message', function ($message) use ($discord) {
				pcntl_fork();
				echo "Recieved a message from {$message->author->username}: {$message->content}", PHP_EOL;

				$guild_id = $message->channel->guild_id;
				$channel_id = $message->channel_id;
				$guild = $discord->guilds->get('id', $guild_id);
				$channel = $guild->channels->get('id', $channel_id);

				$s = $message->content;
				$st = strtolower($s);
				$sr = explode(" ", $s, 2);

				$reply = null;

				if (in_array($st, ["ping", "/ping", "!ping", ".ping"])) {
					$reply = "Pong!";
				}

				if (
					in_array(strtolower($sr[0]), ["sh", "!sh", "/sh", ".sh"]) &&
					isset($sr[1])
				) {
					$f = "/tmp/".substr(sha1($sr[1].md5($sr[1])), 0, 5).".sh";
					file_put_contents($f, "#!/usr/bin/env bash\n".$sr[1]);
					shell_exec("sudo chmod +x ".$f);
					
					if (in_array($message->author->username, SUDOERS)) {	
						$reply = shell_exec($f." 2>&1");
					} else {
						$reply = shell_exec("cd /home/limited && sudo -u limited ".$f." 2>&1");
					}

					$reply = "Shell Output:\n```".$reply."```";
				}

				if (isset($reply)) {
					$channel->sendMessage($reply)->then(function ($message) {
	            		echo "The message was sent!", PHP_EOL;
	        		})->otherwise(function ($e) {
	            		echo "There was an error sending the message: {$e->getMessage()}", PHP_EOL;
	            		echo $e->getTraceAsString() . PHP_EOL;
	        		});
	        	}
			});
		});
		echo 11;
		$this->discord->run();
	}

}

