<?php

namespace DiscordDaemon;

use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\Voice\VoiceClient;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \DiscordDaemon
 * @version 0.0.1
 */
class YoutubeKernel
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
	public $filename = null;

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

		$proxy = [
			"104.168.127.243:49050",
			"104.168.127.243:49051",
			"104.168.127.243:49052",
			"104.168.127.243:49053",
			"104.168.127.243:49054",
			"104.168.127.243:49055",
			"104.168.127.243:49056",
			"104.168.127.243:49057",
			"104.168.127.243:49058",
			"104.168.127.243:49059",
			"104.168.127.243:49060",
			"104.168.127.243:49061",
			"104.168.127.243:49062",
			"104.168.127.243:49063",
			"104.168.127.243:49064",
			"104.168.127.243:49065",
			"104.168.127.243:49066",
			"104.168.127.243:49067",
			"104.168.127.243:49068",
			"104.168.127.243:49069",
			"104.168.127.243:49070",
			"104.168.127.243:49071",
			"104.168.127.243:49072",
			"104.168.127.243:49073",
			"104.168.127.243:49074",
			"104.168.127.243:49075",
			"104.168.127.243:49076",
			"104.168.127.243:49077",
			"104.168.127.243:49078",
			"104.168.127.243:49079",
			"104.168.127.243:49080",
			"104.168.127.243:49081",
			"104.168.127.243:49082",
			"104.168.127.243:49083",
			"104.168.127.243:49084",
			"104.168.127.243:49085",
			"104.168.127.243:49086",
			"104.168.127.243:49087",
			"104.168.127.243:49088",
			"104.168.127.243:49089",
			"104.168.127.243:49090",
		];

		$proxy = $proxy[rand(0, count($proxy) - 1)];

		$me = proc_open(
			"exec {$py} {$ytdl} -f 18 --proxy \"socks5://{$proxy}\" --extract-audio --audio-format mp3 {$ytid} --cache-dir /var/cache/youtube-dl",
			$fd,
			$pipes,
			$this->chdir
		);
		if (preg_match("/\[ffmpeg\] Destination: (.*.mp3)/Usi", stream_get_contents($pipes[1]), $m)) {
			$this->filename = $m[1];
		}
		proc_close($me);
	}
}
