<?php

namespace DiscordDaemon;

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
	 * @var \Discord\Voice\VoiceClient
	 */
	private $vc;

	/**
	 * @param \Discord\Voice\VoiceClient $vc
	 *
	 * Constructor.
	 */
	public function __construct(VoiceClient $vc)
	{
		$this->vc = $vc;
	}

	/**
	 * @param string &$guild_id
	 * @param string &$channel_id
	 * @return void
	 */
	public function dispatch(string &$guild_id, string &$channel_id): void
	{
		$this->discord
			->joinVoiceChannel(
				$this->discord
					->guilds
					->get("id", $guild_id)
					->channels->get("id", $guild),
					false,false, null
			)
			->then(
				
				/**
				 * Promise resolved.
				 */
				function (VoiceClient $vc) {
				    printf("[radio] Joined voice channel...\n");
				    printf("[radio] ");


				    $vc->playFile(__DIR__."/me.mp3")
				    	->otherwise(function($e){ 
				    		printf("Error: %s\n", $e->getMessage())
				    	});
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
			);

		return;	
	}

	/**
	 * 
	 */
	public function resolved()
	{

	}
}
