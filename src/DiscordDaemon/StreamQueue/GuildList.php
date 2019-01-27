<?php

namespace DiscordDaemon\StreamQueue;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \DiscordDaemo\StreamQueue
 * @version 0.0.1
 */
class GuildList
{
	/**
	 * @var array
	 */
	private $guildList = [];

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		if (is_dir(STORAGE_PATH."/stream_queue/")) {
			$this->guildList = scandir(STORAGE_PATH."/stream_queue/");
			unset($this->guildList[0], $this->guildList[1]);
		}
	}

	/**
	 * @return array
	 */
	public function &get(): array
	{
		return $this->guildList;
	}
}
