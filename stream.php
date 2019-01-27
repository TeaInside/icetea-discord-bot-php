<?php

if (isset($argv[1])):

	$argv = json_decode($argv[1], true);

	if (isset($argv["file"], $argv["guild_id"])) {

		require __DIR__."/vendor/autoload.php";
		require __DIR__."/config.php";

		if (!function_exists("\\Sodium\\crypto_secretbox")) {
			require __DIR__."/src/sodium.php";
		}

		$discord = new \Discord\Discord(
			[
				"token" => __DISCORD_BOT_TOKEN
			]
		);

		$discord->on("ready", function ($discord) use (&$argv) {
			$discord->bitrate = 128000;

			$guild = $discord->guilds->get("id", $argv["guild_id"]);
			$channel = $guild->channels->getAll("type", 2)->first();

			$discord->joinVoiceChannel($channel)->then(
				function (\Discord\Voice\VoiceClient $vc) use (&$argv) {
				    $vc->setBitrate(128000)->then(
			    		function () use ($vc, &$argv) {
				    		$vc->playFile($argv["file"])->otherwise(function($e){ 
				    			printf("Error: %s\n", $e->getMessage());
				    		})->then(function () {
				    			exit;
				    		});
			    		}
			    	)->otherwise(function($e){ 
			    		printf("Error: %s\n", $e->getMessage());
			    		exit;
			    	});
			    }
			)->otherwise(
				function ($e) {
			    	printf(
			    		"There was an error joining the voice channel: %s\n",
			    		$e->getMessage()
			    	);
			    	exit;
				}
			);
		});
		$discord->run();
	}

endif;
