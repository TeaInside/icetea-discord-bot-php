<?php

namespace DiscordDaemon;

use Discord\Discord;
use Discord\Voice\VoiceClient;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \DiscordDaemon
 * @version 0.0.1
 */
class Radio
{	
	/**
	 * @var Discord\Discord
	 */
	private $vc;

	/**
	 * @var string
	 */
	private $guild_id;

	/**
	 * @var string
	 */
	private $channel_id;

	/**
	 * @var string
	 */
	private $file;

	/**
	 * @param Discord\Discord $discord
	 *
	 * Constructor.
	 */
	public function __construct(Discord $discord)
	{
		$this->discord = $discord;
	}

	/**
	 * @param string &$guild_id
	 * @param string &$channel_id
	 * @param string &$file
	 * @return void
	 */
	public function setData(string &$guild_id, string &$channel_id, string &$file): void
	{
		$this->guild_id = &$guild_id;
		$this->channel_id = &$channel_id;
		$this->file = &$file;
	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		require_once __DIR__."/../../vendor/autoload.php";
		$guild_id = &$this->guild_id;
		$channel_id = &$this->channel_id;
		$file = &$this->$file;

		$this->discord->on("ready", function ($discord) use (&$guild_id, &$channel_id, &$file) {
			printf("Radio is ready!\n");			

			// Prepare radio
			$this
				->discord
				->joinVoiceChannel(
					$this
						->discord
						->guilds
						->get("id", $guild_id)
						->channels
						->get("id", $channel_id),
						false, false, null
				)
				->then(
					
					/**
					 * Promise resolved.
					 */
					function (VoiceClient $vc) use (&$file) {
					    printf("[radio] Joined voice channel...\n");

					    $vc->setBitrate(128000)->then(
				    		function () use ($vc, &$file) {
					    		$vc->playFile($file)->otherwise(function($e){ 
					    			printf("Error: %s\n", $e->getMessage());
					    		})->then(function () {
					    			exit;
					    		});
				    		}
				    	)->otherwise(function($e){ 
				    		printf("Error: %s\n", $e->getMessage());
				    	});


					    // $it = 0;
					    // do {
					    	
					    // 	if ($it > 0) {
					    // 		printf("Sleeping for 60 seconds...");
					    // 		sleep(60);
					    // 	}

					    // 	$playList = glob(sprintf(
						   //  	"%s/*.mp3",
						   //  	__DISCORD_RADIO_PLAYLIST_DIR
						   //  ));

						   //  $c = count($playList);

						   //  if ($c === 0) {
					    // 		printf("Empty playlist\n");
					    // 	}

					    // } while ($c === 0);

					    // $i = 0;
					    // shuffle($playList);

					    // printf("Got %d playlist!\n", $c);

					    // $loopSong = function () use (&$loopSong, &$playList, &$i, $vc, &$c) {
					    // 	$vc->setBitrate(128000)->then(
					    // 		function () use (&$loopSong, &$playList, &$i, $vc, &$c) {
					    // 			$qq = cli_set_process_title(
					    // 				sprintf(
					    // 					"discordd: radio --player --file=%s", 
					    // 					$playList[$i % $c]
					    // 				)
					    // 			);
					    // 			printf("[radio] Playing %s...; offset %d\n", $playList[$i % $c], $i % $c);
						   //  		$vc->playFile($playList[$i++ % $c])
							  //   		->then($loopSong)
								 //    	->otherwise(function($e){ 
								 //    		printf("Error: %s\n", $e->getMessage());
								 //    	});
					    // 		}
					    // 	)->otherwise(function($e){ 
					    // 		printf("Error: %s\n", $e->getMessage());
					    // 	});
					    // };

					    // $loopSong();
					},

					/**
					 * Promise rejected.
					 */
					function ($e) {
				    	printf(
				    		"There was an error joining the voice channel: %s\n",
				    		$e->getMessage()
				    	); 
					}
				)->otherwise(
					function ($e) {
				    	printf(
				    		"There was an error joining the voice channel: %s\n",
				    		$e->getMessage()
				    	); 
					}
				);
			// end of prepare radio

		});
		$this->discord->run();
		return;	
	}
}
