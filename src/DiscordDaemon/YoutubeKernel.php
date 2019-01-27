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
	 * @var string
	 */

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
			["pipe", "w"],
			["file", "php://stdout", "w"]
		];

		$ytdl = trim(shell_exec("which youtube-dl"));
		$py = trim(shell_exec("which python"));
		$ytid = escapeshellarg($this->ytid);
		ob_start();
		$me = proc_open(
			"exec {$py} {$ytdl} -f 18 --extract-audio --audio-format mp3 {$ytid} --cache-dir /var/cache/youtube-dl",
			$fd,
			$pipes,
			$this->chdir
		);
		if (preg_match("/\[ffmpeg\] Destination: (.*.mp3)/Usi", stream_get_contents($pipes[1]), $m)) {
			var_dump($m);
			$shm_key = ftok(__FILE__, 'a');
			$shmid = shmop_open($shm_key, "c", 0644, 255);
			shmop_write($shmid, sprintf("%s\0", $m[1]), 0);
			shmop_close($shmid);
		}
		proc_close($me);
	}
}
