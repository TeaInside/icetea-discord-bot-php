<?php

namespace DiscordDaemon;

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
	 * @return mixed
	 */
	private function getResponse(string $text, $guild, $channel)
	{
		if (preg_match("/^[\/\.\!\~]?ping$/i", $text)) {
			return "Pong!";
		}
	}
}
