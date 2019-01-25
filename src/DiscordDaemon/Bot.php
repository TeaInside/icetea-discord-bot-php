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
	}

	/**
	 * @param array $opt
	 * @return void
	 */
	public function init(array $opt): void
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
		$this->init([]);
		$this->eventHandler();
		sleep(1000 * 30);

		// if (!($radioPid = pcntl_fork())) {
		// 	cli_set_process_title("discordd: radio_worker --memory-copy");
		// 	$this->radio();
		// 	exit;	
		// }

		if (!($eventHandlerPid = pcntl_fork())) {
			cli_set_process_title(
				sprintf(
					"discordd: event_handler --pool --child=%s",
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
					$this->discord = null;
					cli_set_process_title(
						sprintf(
							"discordd: radio --channel_id=%s --guild_id=%s --playlist_dir=%s --loop --daemonize",
							$v["channel_id"],
							$v["guild_id"],
							__DISCORD_RADIO_PLAYLIST_DIR
						)
					);
					if (!pcntl_fork()) {
						cli_set_process_title(sprintf("discordd: radio --player"));
						$this->init([
							// "disabledEvents" => [Event::TYPING_START, Event::VOICE_STATE_UPDATE, Event::VOICE_SERVER_UPDATE, Event::GUILD_CREATE, Event::GUILD_DELETE, Event::GUILD_UPDATE, Event::CHANNEL_CREATE, Event::CHANNEL_UPDATE, Event::CHANNEL_DELETE, Event::GUILD_BAN_ADD, Event::GUILD_BAN_REMOVE, Event::MESSAGE_CREATE, Event::MESSAGE_DELETE, Event::MESSAGE_DELETE_BULK, Event::MESSAGE_UPDATE, Event::GUILD_MEMBER_ADD, Event::GUILD_MEMBER_REMOVE, Event::GUILD_MEMBER_UPDATE, Event::GUILD_ROLE_CREATE, Event::GUILD_ROLE_DELETE, Event::GUILD_ROLE_UPDATE]
						]);
						(new Radio($this->discord))->dispatch($v["guild_id"], $v["channel_id"]);
						exit;
					}
					pcntl_wait($status);
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
		$this->discord->on("ready", function ($discord) {
			
			printf("Bot is ready\n");

			

			$discord->on("message", function ($message) use ($discord) {
				//$pool = new Pool(15);
				// $guild_id = $message->channel->guild_id;
				// $channel_id = $message->channel_id;
				// $guild = $discord->guilds->get("id", $guild_id);
				// $channel = $guild->channels->get("id", $guild);
				
				// printf("Recieved a message from %s: %s\n", $message->author->username, json_encode(
				// 	$text = $message->content
				// ));
				(new Response($discord, $message))->run();
				// $pool->submit(new Response($discord, $message));
			});
		});
		$this->discord->run();
		return;
	}

	// /**
	//  * @return void
	//  */
	// public function worker(): void
	// {
	// 	$pids = [];
	// 	for ($i=__DISCORD_WORKERS; $i--;) { 
	// 		if (!($pid = pcntl_fork())) {
					
	// 			exit;
	// 		}
	// 		$pids[] = $pid;
	// 	}
	// }

	// /**
	//  * @return void
	//  */
	// public function _run()
	// {
	// 	$pidFile = __DIR__."/discordd.pid";

	// 	file_put_contents($pidFile, getmypid());
	// 	cli_set_process_title("discordd --daemonize --pid_file={$pidFile} --pool");

	// 	$this->discord->on("ready", function (&$discord) {
			
	// 		printf("Bot is ready\n");


	// 		$guild_id = $message->channel->guild_id;
	// 		$channel_id = $message->channel_id;
	// 		$guild = $this->discord->guilds->get("id", $guild_id);
	// 		$channel = $guild->channels->get("id", $guild);


	// 		print "A11\n";
	// 		$discord->joinVoiceChannel($channel, false, false, null)->then(function (VoiceClient $vc) {
			    
	// 		    echo "Joined voice channel.\r\n";
	// 		    $vc->playFile(__DIR__."/me.mp3")->otherwise(function($e){ 
	// 		    	echo "ERR: ".$e->getMessage(); 
	// 		    });

	// 		}, function ($e) {
	// 		    echo "There was an error joining the voice channel: {$e->getMessage()}\r\n"; 
	// 		});
	// 		print "A12\n";



	// 		// /**
	// 		//  * On message event.
	// 		//  */
	// 		// $discord->on("message", function (&$message) use (&$discord) {

				


	// 		// 	// $response = new Response($discord);
	// 		// 	// $response->onMessage($message);

	// 		// 	// $response = null;
	// 		// 	// unset($response, $message);

	// 		// 	return;
	// 		// });


	// 	});

	// 	$this->discord->run();
	// }

}

