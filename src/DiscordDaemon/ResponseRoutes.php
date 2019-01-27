<?php

namespace DiscordDaemon;

use DiscordDaemon\StreamQueue\MasterQueue;

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
			// $r = "";
			// $i = 0;
			// foreach ($st as $st) {
			// 	$r .= "{$i}. {$st}";
			// 	$i++;
			// }
		}
	}
}
