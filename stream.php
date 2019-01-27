<?php

if (isset($argv[1])):

	$argv = json_decode($argv[1], true);

	if (isset($argv["file"], $argv["guild_id"], $argv["ytid"])):

		require __DIR__."/vendor/autoload.php";
		require __DIR__."/config.php";

		if (!function_exists("\\Sodium\\crypto_secretbox")) {
			require __DIR__."/src/sodium.php";
		}

		callq($argv);

		$discord = new \Discord\Discord(
			[
				"token" => __DISCORD_BOT_TOKEN
			]
		);

		$discord->on("ready", function ($discord) use (&$argv) {

			$discord->bitrate = 128000;

			$guild = $discord->guilds->get("id", $argv["guild_id"]);
			$channel = $guild->channels->getAll("type", 2)->first();

			$discord->joinVoiceChannel($channel, false, false, null)->then(
				function (\Discord\Voice\VoiceClient $vc) use (&$argv) {
				    $vc->setBitrate(128000)->then(
			    		function () use ($vc, &$argv) {
				    		$vc->playFile($argv["file"])->otherwise(function($e){ 
				    			shell_exec(__KILL_DCA);
				    			printf("Error: %s\n", $e->getMessage());
				    		})->then(function () {
				    			shell_exec(__KILL_DCA);
				    			exit;
				    		});
			    		}
			    	)->otherwise(function($e){ 
			    		printf("Error: %s\n", $e->getMessage());
			    		shell_exec(__KILL_DCA);
			    	});
			    }
			)->otherwise(
				function ($e) {
			    	printf(
			    		"There was an error joining the voice channel: %s\n",
			    		$e->getMessage()
			    	);
			    	shell_exec(__KILL_DCA);
				}
			);
		});
		$discord->run();
		shell_exec(__KILL_DCA);
	endif;

endif;

/**
 * @param array &$argv
 * @return void
 */
function callq(array &$argv): void
{
	if (!(pcntl_fork())) {
		$discord = new \Discord\Discord(
			[
				"token" => __DISCORD_BOT_TOKEN
			]
		);
		$discord->on("ready", function ($discord) use (&$argv) {
			$guild = $discord->guilds->get("id", $argv["guild_id"]);
			$channel = $guild->channels->getAll("type", "text")->first();
			$channel->sendMessage(
				sprintf(
					"Download finished!\nYoutube ID: \"%s\"\nFilename: \"%s\"\n\nPreparing streaming...",
					$argv["ytid"],
					$argv["file"]
				)
			)->then(function () { exit; })->otherwise(function () { exit; });
		});
		$discord->run();
		exit;
	}
}
