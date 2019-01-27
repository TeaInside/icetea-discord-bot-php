<?php

namespace DiscordDaemon\StreamQueue;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \DiscordDaemo\StreamQueue
 * @version 0.0.1
 */
class MasterQueue
{
	/**
	 * @var string
	 */
	private $guild_id;

	/**
	 * @var string
	 */
	private $queueFile;

	/**
	 * @var array
	 */
	private $queue = [];

	/**
	 * @param string
	 *
	 * Constructor.
	 */
	public function __construct(string $guild_id)
	{
		$this->guild_id = $guild_id;

		is_dir(STORAGE_PATH) or mkdir(STORAGE_PATH);
		is_dir(STORAGE_PATH."/mp3") or mkdir(STORAGE_PATH."/mp3");
		is_dir(STORAGE_PATH."/stream_queue") or mkdir(STORAGE_PATH."/stream_queue");
		is_dir(STORAGE_PATH."/stream_queue/{$guild_id}") or mkdir(STORAGE_PATH."/stream_queue/{$guild_id}");
		file_exists(STORAGE_PATH."/stream_queue/.gitignore") or 
		file_put_contents(STORAGE_PATH."/stream_queue/.gitignore", "*\n!.gitignore");

		$this->queueFile = STORAGE_PATH."/stream_queue/{$guild_id}/queue.json";
		if (file_exists($this->queueFile)) {
			$this->queue = json_decode(file_get_contents($this->queueFile), true);
			if (!is_array($this->queue)) {
				$this->queue = [];
			}
		} else {
			$this->queue = [];
			file_put_contents($this->queueFile, "[]");
		}
	}
		
	/**
	 * @param string $ytid
	 * @return bool
	 */
	public function enqueue(string $ytid): bool
	{
		if (in_array($ytid, $this->queue)) {
			return false;
		}

		$this->queue[] = $ytid;
		return true;
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		file_put_contents($this->queueFile, json_decode($this->queue));
	}

	/**
	 * @return array
	 */
	public function getQueue(): array
	{
		return $this->queue;
	}
}
