<?php

namespace Discord;

use Discord\Discord;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 */
final class Bot
{
	/**
	 * @var \Discord\Discord
	 */	
	private $discord;

	/**
	 * @param \Discord\Discord
	 */
	public function __construct(Discord &$discord)
	{
		$this->discord = $discord;
	}

	/**
	 * @return void
	 */
	public function run()
	{
		$pidFile = __DIR__."/discordd.pid";

		file_put_contents($pidFile, getmypid());
		cli_set_process_title("discordd --daemonize --pid_file={$pidFile} --pool");

		$this->discord->on("ready", function (&$discord) {
			
			printf("Bot is ready\n");

			/**
			 * On message event.
			 */
			$discord->on("message", function (&$message) use (&$discord) {

				$guild_id = $message->channel->guild_id;
				$channel_id = $message->channel_id;
				$guild = $this->discord->guilds->get("id", $guild_id);
				//var_dump($guild->channels);
				$channel = $guild->channels->get("id", "446634690015657987");

				var_dump($channel);

				print "Me\n\n";
				$discord->joinVoiceChannel($channel, false, true, null)->then(function (VoiceClient $vc) {
				    echo "Joined voice channel.\r\n";
				    $vc->playFile(__DIR__."/me.mp3");
				}, function ($e) {
				    echo "There was an error joining the voice channel: {$e->getMessage()}\r\n"; 
				});
				print "Test\n";


				// $response = new Response($discord);
				// $response->onMessage($message);

				// $response = null;
				// unset($response, $message);

				return;
			});


		});

		$this->discord->run();
	}

}

