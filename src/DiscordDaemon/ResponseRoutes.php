<?php

namespace DiscordDaemon;

use DiscordDaemon\StreamQueue;
use DiscordDaemon\StreamQueue\MasterQueue;
use DiscordDaemon\StreamQueue\QueueException;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \DiscordDaemon
 * @version 0.0.1
 */
trait ResponseRoutes
{
	/**
	 * @param string $text
	 * @param $guild
	 * @param $channel
	 * @param $message
	 * @return mixed
	 */
	private function getResponse(string &$text, &$guild, &$channel, &$message)
	{
		if (preg_match("/^[\/\.\!\~]?ping$/USsi", $text)) {
			return "Pong!";
		}

		if (preg_match("/^[\/\.\!\~]?vq$/USsi", $text)) {
			$st = new MasterQueue($message->channel->guild_id);
			$st = $st->getQueue();
			if (!$st) {
				return "Queue is empty.";
			}
			$r = "";
			foreach ($st as $k => $st) {
				$r .= "{$k}. {$st}\n";
			}

			return trim($r);
		}

		if (preg_match("/^[\/\.\!\~]?vadd[\s\n]+([\S]+)$/USsi", $text, $m)) {
			$st = new MasterQueue($message->channel->guild_id);
			try {
				if ($st->enqueue($m[1])) {
					$r = sprintf("\"%s\" has been added to queue.", $m[1]);
				} else {
					$r = sprintf("Couldn't add \"%s\" because the same id has already been exist in the queue.\nSend !vq to show the queue.", $m[1]);
				}
				unset($st);	
			} catch (QueueException $e) {
				$r = $e->getMessage();
			}
			return $r;
		}
	}
}
