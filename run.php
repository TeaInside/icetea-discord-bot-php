<?php

use Discord\Discord;
use Discord\Cache\Cache;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\Part;
use Discord\Parts\User\Game;
use Discord\Parts\User\User;
use React\Promise\Deferred;

include __DIR__.'/vendor/autoload.php';

while (true) {
  $discord = new Discord([
      'token' => 'NDQ2NTc3Nzg5NTAwNTIyNTI4.Dd7Dgg.qjCVGUtPZlprQLf3uqur-VYmgXQ',
  ]);

  $discord->on('ready', function ($discord) {
      echo "Bot is ready.", PHP_EOL;
    
      // Listen for events here
      $discord->on('message', function ($message) use ($discord) {
          echo "Recieved a message from {$message->author->username}: {$message->content}", PHP_EOL;

          $guild_id = $message->channel->guild_id;
          $channel_id = $message->channel_id;
          $guild = $discord->guilds->get('id', $guild_id);
          $channel = $guild->channels->get('id', $channel_id);

          $s = $message->content;

          if ($s === "ping") {
            $channel->sendMessage('Pong!')->then(function ($message) {
                echo "The message was sent!", PHP_EOL;
            })->otherwise(function ($e) {
                echo "There was an error sending the message: {$e->getMessage()}", PHP_EOL;
                echo $e->getTraceAsString() . PHP_EOL;
            }); 
          }

          $s = explode(" ", $s, 2);

          if (in_array(strtolower($s[0]), ["sh", "!sh", "/sh", "~sh"]) && isset($s[1])) {
            $f = "/tmp/".substr(sha1($s[1].md5($s[1])), 0, 5).".sh";
            file_put_contents($f, "#!/usr/bin/env bash\n".$s[1]);
            shell_exec("chmod +x ".$f);
            $s = shell_exec("sudo -u limited ".$f." 2>&1");
            $channel->sendMessage("shell_output:\n```".$s."```")->then(function ($message) {
                echo "The message was sent!", PHP_EOL;
            })->otherwise(function ($e) {
                echo "There was an error sending the message: {$e->getMessage()}", PHP_EOL;
                echo $e->getTraceAsString() . PHP_EOL;
            });
            shell_exec("rm -rf ".$f);
          }

      });
  });
  $discord->run();
}
