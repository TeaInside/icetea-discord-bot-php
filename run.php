<?php

use Discord\Bot;
use Discord\Discord;
use Discord\Parts\Part;
use Discord\Cache\Cache;
use React\Promise\Deferred;
use Discord\Parts\User\Game;
use Discord\Parts\User\User;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;

include __DIR__."/vendor/autoload.php";
include __DIR__."/config.php";

register_shutdown_function("rehandle");

function rehandle()
{
	shell_exec(REHANDLE);
}

while (true) {
  try {
    $discord = new Discord(["token" => TOKEN]);
    $bot = new Bot($discord);
    $bot->run();
  } catch (Error $e) {
  	echo "An error occured!\n\n";
  	var_dump($e->getMessage());
  	exit();
  }
}
