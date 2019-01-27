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
	 * @var string
	 */
	private $chdir;

	/**
	 * @param string $ytid
	 *
	 * Constructor.
	 */
	public function __construct(string $ytid, string $chdir)
	{
		$this->chdir = $chdir;
		$this->ytid = $ytid;
		is_dir("/var/cache/youtube-dl") or mkdir("/var/cache/youtube-dl");
	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		$fd = [
			["pipe", "r"],
			["file", "php://stdout", "w"],
			["file", "php://stdout", "w"]
		];

		$ytdl = trim(shell_exec("which youtube-dl"));
		$py = trim(shell_exec("which python"));
		$ytid = escapeshellarg($this->ytid);
		proc_open(
			"exec {$py} {$ytdl} -f 18 --extract-audio --audio-format mp3 {$ytid} --cache-dir /var/cache/youtube-dl",
			$fd,
			$pipes,
			$this->chdir
		);
	}
}
