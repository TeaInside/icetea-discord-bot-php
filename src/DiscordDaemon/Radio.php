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
					$this->discord
						->guilds
						->get("id", $guild_id)
						->channels->get("id", $guild),
						false, false, null
				)
				->then(
					
					/**
					 * Promise resolved.
					 */
					function (VoiceClient $vc) {
					    printf("[radio] Joined voice channel...\n");

					    $playList = scandir(__DISCORD_RADIO_PLAYLIST_DIR);
					    shuffle($playList);

					    var_dump($playList);sleep(1000);

					    // $handler = function () use ($vc) {
					    // 	$vc->playFile(__DIR__."/me.mp3")
						   //  	->then(function () use ($vc) {
						   //  		$vc->play
						   //  	})
					    // 		->otherwise(function($e){ 
					    // 			printf("Error: %s\n", $e->getMessage())
					    // 		});
					    // };

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
