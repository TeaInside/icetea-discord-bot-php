<?php

require __DIR__."/vendor/autoload.php";
require __DIR__."/config.php";

if (!function_exists("\\Sodium\\crypto_secretbox")) {
	require __DIR__."/src/sodium.php";
}

declare(ticks=1);
pcntl_signal(SIGCHLD, SIG_IGN);

(new DiscordDaemon\Bot)->run();
