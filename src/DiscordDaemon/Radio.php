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

					    $playList = glob(sprintf(
					    	"%s/*.mp3",
					    	__DISCORD_RADIO_PLAYLIST_DIR
					    ));
					    $i = 0;
					    shuffle($playList);
					    $c = count($playList) - 1;
					    $loopSong = function () use (&$loopSong, &$playList, &$i, $vc, &$c) {
					    	if ($i === $c) {
					    		$i = 0;
					    	}
					    	cli_set_process_title(
								sprintf("discordd: radio --play --file=%s", $playList[$i])
							);
					    	printf("[radio] Playing %s\n", $playList[$i]);
					    	$vc->playFile($playList[$i++])
					    		->then($loopSong)
						    	->otherwise(function($e){ 
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

	/**
	 * 
	 */
	public function resolved()
	{

	}
}
