<?php

namespace DiscordDaemon;

use Thread;
use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\Voice\VoiceClient;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \DiscordDaemon
 * @version 0.0.1
 */
class YoutubeKernel extends Thread
{
	/**
	 * @var string
	 */
	private $ytid;

	/**
	 * @param string $ytid
	 *
	 * Constructor.
	 */
	public function __construct(string $ytid)
	{
		$this->ytid = $ytid;
	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		
	}
}
