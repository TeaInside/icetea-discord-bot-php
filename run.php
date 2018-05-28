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

while (true) {
  try { echo 333;
    $discord = new Discord(["token" => TOKEN]);
    echo 555;$bot = new Bot($discord);
    $bot->run();
  } catch (Error $e) {
  }
}
