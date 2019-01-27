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
	 * @return int
	 */
	public function countQueue(): int
	{
		return sizeof($this->queue);
	}

	/**
	 * @param string $ytid
	 * @throws DiscordDaemon\StreamQueue\QueueException
	 * @return bool
	 */
	public function enqueue(string $ytid): bool
	{
		if (in_array($ytid, $this->queue)) {
			return false;
		}

		if (count($this->queue) >= 5) {
			throw new QueueException("The system has reached the maximum number of queues (5 queues). Please try again later.");
		}

		$this->queue[] = $ytid;
		return true;
	}

	/**
	 * @return string
	 */
	public function dequeue(): string
	{
		if (isset($this->queue[0])) {
			$r = $this->queue[0];
			unset($this->queue[0]);
			$this->queue = array_values($this->queue);
			return $r;
		}
		return "";
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		file_put_contents($this->queueFile, json_encode($this->queue));
	}

	/**
	 * @return array
	 */
	public function &getQueue(): array
	{
		return $this->queue;
	}
}
