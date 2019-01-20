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
	 * @return void
	 */
	public function dispatch(string &$guild_id, string &$channel_id): void
	{
		$this->discord->on("ready", function ($discord) use (&$guild_id, &$channel_id) {
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
					function (VoiceClient $vc) {
					    printf("[radio] Joined voice channel...\n");
					    
					    $it = 0;
					    do {
					    	
					    	if ($it > 0) {
					    		printf("Sleeping for 60 seconds...");
					    		sleep(60);
					    	}

					    	$playList = glob(sprintf(
						    	"%s/*.mp3",
						    	__DISCORD_RADIO_PLAYLIST_DIR
						    ));

						    $c = count($playList);

						    if ($c === 0) {
					    		printf("Empty playlist\n");
					    	}

					    } while ($c === 0);

					    $i = 0;
					    shuffle($playList);

					    printf("Got %d playlist!\n", $c);

					    $loopSong = function () use (&$loopSong, &$playList, &$i, $vc, &$c) {
					    	$vc->setBitrate(128000)->then(
					    		function () use (&$loopSong, &$playList, &$i, $vc, &$c) {
					    			cli_set_process_title(
					    				sprintf(
					    					"discordd: radio --player --file=%s", 
					    					$playList[$i % $c]
					    				)
					    			);
					    			printf("[radio] Playing %s...; offset %d\n", $playList[$i], $i % $c);
						    		$vc->playFile($playList[$i++ % $c])
							    		->then($loopSong)
								    	->otherwise(function($e){ 
								    		printf("Error: %s\n", $e->getMessage());
								    	});
					    		}
					    	)->otherwise(function($e){ 
					    		printf("Error: %s\n", $e->getMessage());
					    	});
					    };

					    $loopSong();
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
