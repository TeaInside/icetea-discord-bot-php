<?php

namespace DiscordDaemon;

use Pool;
use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\Voice\VoiceClient;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \DiscordDaemon
 * @version 0.0.1
 */
final class Bot
{
	/**
	 * @var \Discord\Discord
	 */	
	private $discord;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		foreach($GLOBALS as $k => &$v) { 
			if ($k != "GLOBALS") {
				unset($GLOBALS[$k]); 
			}
		}
		unset($GLOBALS, $k, $v);
	}

	/**
	 * @param array $opt
	 * @return void
	 */
	public function init(array $opt = []): void
	{
		$opt["token"] = __DISCORD_BOT_TOKEN;
		$this->discord = new Discord($opt);
	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		file_put_contents(__DISCORD_DAEMON_PID_FILE, getmypid());
		cli_set_process_title(
			sprintf(
				"discordd: master --daemonize --pid_file=%s",
				__DISCORD_DAEMON_PID_FILE
			)
		);

		if (!($radioPid = pcntl_fork())) {
			cli_set_process_title("discordd: radio_worker --memory-copy");
			$this->radio();
			exit;	
		}

		if (!($eventHandlerPid = pcntl_fork())) {
			cli_set_process_title(
				sprintf(
					"discordd: event_handler",
					__DISCORD_WORKERS
				)
			);
			$this->init([
				// "disabledEvents" => [Event::TYPING_START, Event::VOICE_STATE_UPDATE, Event::VOICE_SERVER_UPDATE, Event::GUILD_CREATE, Event::GUILD_DELETE, Event::GUILD_UPDATE, Event::CHANNEL_CREATE, Event::CHANNEL_UPDATE, Event::CHANNEL_DELETE, Event::GUILD_BAN_ADD, Event::GUILD_BAN_REMOVE, Event::MESSAGE_DELETE, Event::MESSAGE_DELETE_BULK, Event::MESSAGE_UPDATE, Event::GUILD_MEMBER_ADD, Event::GUILD_MEMBER_REMOVE, Event::GUILD_MEMBER_UPDATE, Event::GUILD_ROLE_CREATE, Event::GUILD_ROLE_DELETE, Event::GUILD_ROLE_UPDATE]
			]);
			$this->eventHandler();
			exit;
		}

		$status = null;
		pcntl_wait($status);
	}

	/**
	 * @return void
	 */
	private function radio(): void
	{
		$status = null;
		foreach (__DISCORD_RADIO_STREAM_TARGET as $v) {
			if (!(pcntl_fork())) {
				if (isset($v["guild_id"], $v["channel_id"])) {

					cli_set_process_title(
						sprintf(
							"discordd: radio --channel_id=%s --guild_id=%s --playlist_dir=%s --loop --daemonize",
							$v["channel_id"],
							$v["guild_id"],
							__DISCORD_RADIO_PLAYLIST_DIR
						)
					);
mdd1:
					$playList = glob(sprintf(
						"%s/*.mp3",
				    	__DISCORD_RADIO_PLAYLIST_DIR
				    ));
					shuffle($playList);
					foreach ($playList as &$file) {
						$this->discord = null;
						if (!($pid = pcntl_fork())) {
							cli_set_process_title(sprintf("discordd: streamer --file=%s --no-ff", $file));
							$this->init();
							(new Radio($this->discord))->dispatch($v["guild_id"], $v["channel_id"], $file);
							exit;
						}
						pcntl_waitpid($pid, $status, WUNTRACED);
						printf("%s", shell_exec(__KILL_DCA));
					}
					goto mdd1;
				}

				exit;
			}
		}
		pcntl_wait($status);
		return;
	}

	/**
	 * @return void
	 */
	private function eventHandler(): void
	{
		try {
			$this->discord->on("ready", function ($discord) use ($pool) {
				
				printf("Bot is ready\n");

				$discord->on("message", function ($message) use ($discord, $pool) {
						try {
							(new Response($discord, $message))->run();
						} catch (\Error $e) {
							printf("\n\nAn error occured!\n");
							var_dump($e->getMessage(), $e->getFile(), $e->getLine());		
						}
				});
			});
			$this->discord->run();
		} catch (\Error $e) {
			printf("\n\nAn error occured!\n");
			var_dump($e->getMessage(), $e->getFile(), $e->getLine());
		}
		return;
	}
}
