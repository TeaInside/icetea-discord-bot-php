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
				cli_set_process_title(sprintf("discordd: youtube_kernel --youtube-id=%s --download --extract-audio --audio-format mp3", $st));
				$this->bot->init();
				$this->bot->discord->on("ready", function ($discord) use (&$st, &$guild_id) {

					$r = sprintf("Downloading \"%s\"...", $st);

					$guild = $discord->guilds->get("id", $guild_id);
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

					$notify = function ($file) use (&$st, &$guild_id) {
							var_dump("send");
						 printf("Sending notification...\n");
						 // if (!pcntl_fork()) {
					    	$this->bot->init();
							$this->bot->discord->on("ready", function ($discord) use (&$st, &$file, &$guild_id) {

								if (is_string($file)) {
									if (file_exists(STORAGE_PATH."/mp3/{$file}")) {
										$r = sprintf("Download finished!\nYoutube ID: \"%s\"\nFilename: \"%s\"\n\nPreparing streaming...", $st, $file);
									} else {
										$r = "Download succeded, but the file is missing.\n\nAborted!\n\nRunning next queue in background...";
										$file = null;
									}
								} else {
									$r = "Error data";
								}
								var_dump("mememe");
								$guild = $discord->guilds->get("id", $guild_id);
								$channel = $guild->channels->getAll("type", "text")->first();
								$voiceChannel = $guild->channels->getAll("type", "voice")->first();
								var_dump($voiceChannel);
								$channel->sendMessage($r)->then(function ($message) use ($file) {
									var_dump(123123123);
								    printf("The message was sent ~! 2\n");
								})->otherwise(function ($e) {
								    printf("There was an error sending the message: %s\n", $e->getMessage());
								});
							});
							$this->bot->discord->run();
							var_dump("memset 2ddd");
							exit;
					    //}
					};

					$channel->sendMessage($r)->then(function ($message) use ($act, $channel, $notify) {
					    printf("The message was sent ~! 1\n");
					    $notify($act($channel));
					    exit;
					})->otherwise(function ($e) use ($act, $channel, $notify) {
					    printf("There was an error sending the message: %s\n", $e->getMessage());
					    $notify($act($channel));
					    exit;
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
