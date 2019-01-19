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
		$pidFile = __DIR__."/discordd.pid"

		file_put_contents($pidFile, getmypid());
		cli_set_process_title("discordd --daemonize --pid_file={$pidFile} --pool");

		$this->discord->on("ready", function (&$discord) {
			
			printf("Bot is ready\n");

			/**
			 * On message event.
			 */
			$discord->on("message", function (&$message) use (&$discord) {

				$response = new Response($discord);
				$response->onMessage($message);

				$response = null;
				unset($response, $message);

				return;
			});


		});

		$this->discord->run();
	}

}

