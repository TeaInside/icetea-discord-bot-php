<?php

namespace DiscordDaemon;

use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\Voice\VoiceClient;
use DiscordDaemon\StreamQueue\GuildList;
use DiscordDaemon\StreamQueue\MasterQueue;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \DiscordDaemon
 * @version 0.0.1
 */
class StreamQueue
{
	/**
	 * @var \DiscordDaemon\Bot
	 */
	private $bot;

	/**
	 * @param \DiscordDaemon\Bot $bot
	 *
	 * Constructor.
	 */
	public function __construct(Bot $bot)
	{
		$this->bot = $bot;
	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		while (true) {
			$guildList = new GuildList;
			foreach ($guildList->get() as $k => &$v) {
				$this->dispatch($v);
			}
			unset($guildList);
			printf("[StreamQueue] Sleeping for 10 seconds");
			for ($i=0; $i < 10; $i++) {
				sleep(1);
				printf(".");
			}
			printf("\n");
		}
	}

	/**
	 * @param string &$guild_id
	 * @return void
	 */
	public function dispatch(string &$guild_id): void
	{
		printf("Dispatching %s stream queue...\n", $guild_id);
		$st = new MasterQueue($guild_id);
		if ($st->countQueue()) {
			$st = $st->dequeue();
			printf("Preparing download for %s...\n", $st);
			if (!pcntl_fork()) {
				$this->bot->init();
				$this->bot->discord->on("ready", function ($discord) use (&$st) {

					$r = sprintf("Downloading \"%s\"...", $st);

					$guild = $discord->guilds->first();
					$channel = $guild->channels->getAll("type", "text")->first();
					
					$act = function ($channel) use (&$st) {

						try {
							$ytkernel = new YoutubeKernel($st, STORAGE_PATH."/mp3");
							$ytkernel->run();
						} catch (\Error $e) {
							printf("\n\nAn error occured!\n");
							var_dump($e->getMessage(), $e->getFile(), $e->getLine());
						}

						printf("[StreamQueue] Download success!\n");

						return $ytkernel->filename;
					};

					$notify = function (&$file) use (&$st) {
						 printf("Sending notification...\n");
						 if (!pcntl_fork()) {
					    	$this->bot->init();
							$this->bot->discord->on("ready", function ($discord) use (&$st, &$file) {

								if (is_string($file)) {
									$r = sprintf("Download finished!\n\nYoutube ID: \"%s\"\nFilename: \"%s\"", $st, $file);
								} else {
									$r = "Error data";
								}

								$guild = $discord->guilds->first();
								$channel = $guild->channels->getAll("type", "text")->first();
								$channel->sendMessage($r)->then(function ($message) {
								    printf("The message was sent ~!\n");
								    exit;
								})->otherwise(function ($e) {
								    printf("There was an error sending the message: %s\n", $e->getMessage());
								    exit;
								});
							});
							$this->bot->discord->run();
							exit;
					    }
					    pcntl_wait($status);
					    $status = null;
					    exit;
					};

					$channel->sendMessage($r)->then(function ($message) use ($act, $channel, $notify) {
					    printf("The message was sent ~!\n");
					    $notify($act($channel));
					})->otherwise(function ($e) use ($act, $channel, $notify) {
					    printf("There was an error sending the message: %s\n", $e->getMessage());
					    $notify($act($channel));
					});

				});
				$this->bot->discord->run();
				exit;
			}
			pcntl_wait($status);
		} else {
			printf("[StreamQueue] There is no queue for guild %s\n", $guild_id);
		}
	}
}
