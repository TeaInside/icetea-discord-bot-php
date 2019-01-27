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
			for ($i=0; $i < 3; $i++) {
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
					
					$act = function ($channel) use (&$st, &$discord, &$guild_id) {

						try {
							$ytkernel = new YoutubeKernel($st, STORAGE_PATH."/mp3");
							$ytkernel->run();
						} catch (\Error $e) {
							printf("\n\nAn error occured!\n");
							var_dump($e->getMessage(), $e->getFile(), $e->getLine());
						}

						if (is_string($ytkernel->filename)) {
							$file = STORAGE_PATH."/mp3/{$ytkernel->filename}";
							unset($youtube_kernel);
							printf("[StreamQueue] Download success!\n");
							proc_close(
								proc_open(
									sprintf(__STREAMING_ME, escapeshellarg(
										json_encode(
											[
												"file" => $file,
												"guild_id" => $guild_id
											],
											JSON_UNESCAPED_SLASHES
										)
									)), 
									[
										["pipe", "r"],
										["file", "php://stdout", "w"],
										["file", "php://stdout", "w"]
									],
									$pipes
								)
							);
							$pipes = null;
							unset($pipes);
						}
					};

					$channel->sendMessage($r)->then(function ($message) use ($act, $channel) {
					    printf("The message was sent ~! 1\n");
					    $act($channel);
					    exit;
					})->otherwise(function ($e) use ($act, $channel) {
					    printf("There was an error sending the message: %s\n", $e->getMessage());
					    $act($channel);
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
