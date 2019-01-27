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
						 printf("Sending notification...\n");
						 if (!pcntl_fork()) {
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

								$guild = $discord->guilds->get("id", $guild_id);
								$channel = $guild->channels->getAll("type", "text")->first();
								$channel->sendMessage($r)->then(function ($message) use (&$file) {
								    printf("The message was sent ~!\n");
								    if (is_string($file)) {
								    	var_dump($file);
								    	$file = STORAGE_PATH."/mp3/{$file}";
								    	var_dump($file);
								    	var_dump("wmm");
								    	sleep(100);
								   //  	if (!pcntl_fork()) {
								   //  		printf("[StreamQueue] initd\n");
								   //  		$this->bot->discord->init();
								   //  		$this->bot->discord->on("ready", function ($discord) use (&$guild_id, &$channel_id, &$file) {
											// 	printf("[StreamQueue] Streaming is ready!\n");
											// 	$guild = $discord->guilds->get("id", $guild_id);
											// 	$channel = $guild->channels->getAll("type", "voice")->first();
											// 	$discord->joinVoiceChannel($voiceChannel, false, false, null)->then(
											// 		function (VoiceClient $vc) use (&$file) {
											// 		    printf("[StreamQueue] Joined voice channel...\n");
											// 		    printf("[StreamQueue] Playing %s...\n", $file);
											// 		    $vc->setBitrate(128000)->then(
											// 	    		function () use ($vc, &$file) {
											// 		    		$vc->playFile($file)->otherwise(function($e){ 
											// 		    			printf("Error: %s\n", $e->getMessage());
											// 		    			exit;
											// 		    		})->then(function () {
											// 		    			exit;
											// 		    		});
											// 	    		}
											// 	    	)->otherwise(function($e){ 
											// 	    		printf("Error: %s\n", $e->getMessage());
											// 	    		exit;
											// 	    	});
											// 		},

											// 		function ($e) {
											// 	    	printf(
											// 	    		"There was an error joining the voice channel: %s\n",
											// 	    		$e->getMessage()
											// 	    	); 
											// 		}
											// 	)->otherwise(
											// 		function ($e) {
											// 	    	printf(
											// 	    		"There was an error joining the voice channel: %s\n",
											// 	    		$e->getMessage()
											// 	    	); 
											// 		}
											// 	);
											// });
											// $this->bot->discord->run();
								   //  		exit;
								   //  	}
								   //  	pcntl_wait($status);
								    }
								    exit;
								})->otherwise(function ($e) {
								    printf("There was an error sending the message: %s\n", $e->getMessage());
								    exit;
								});
							});
							$this->bot->discord->run();
							exit;
					    }
					    var_dump("waiting...");
					    pcntl_wait($status);
					    $status = null;
					    var_dump($file);
					   //  if (is_string($file)) {
					   //  	$file = STORAGE_PATH."/mp3/{$file}";
					   //  	if (!pcntl_fork()) {
					   //  		printf("[StreamQueue] initd\n");
					   //  		$this->bot->discord->init();
					   //  		$this->bot->discord->on("ready", function ($discord) use (&$guild_id, &$channel_id, &$file) {
								// 	printf("[StreamQueue] Streaming is ready!\n");
								// 	$guild = $discord->guilds->get("id", $guild_id);
								// 	$channel = $guild->channels->getAll("type", "voice")->first();
								// 	$discord->joinVoiceChannel($voiceChannel, false, false, null)->then(
								// 		function (VoiceClient $vc) use (&$file) {
								// 		    printf("[StreamQueue] Joined voice channel...\n");
								// 		    printf("[StreamQueue] Playing %s...\n", $file);
								// 		    $vc->setBitrate(128000)->then(
								// 	    		function () use ($vc, &$file) {
								// 		    		$vc->playFile($file)->otherwise(function($e){ 
								// 		    			printf("Error: %s\n", $e->getMessage());
								// 		    			exit;
								// 		    		})->then(function () {
								// 		    			exit;
								// 		    		});
								// 	    		}
								// 	    	)->otherwise(function($e){ 
								// 	    		printf("Error: %s\n", $e->getMessage());
								// 	    		exit;
								// 	    	});
								// 		},

								// 		function ($e) {
								// 	    	printf(
								// 	    		"There was an error joining the voice channel: %s\n",
								// 	    		$e->getMessage()
								// 	    	); 
								// 		}
								// 	)->otherwise(
								// 		function ($e) {
								// 	    	printf(
								// 	    		"There was an error joining the voice channel: %s\n",
								// 	    		$e->getMessage()
								// 	    	); 
								// 		}
								// 	);
								// });
								// $this->bot->discord->run();
					   //  		exit;
					   //  	}
					   //  	pcntl_wait($status);
					   //  }
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
