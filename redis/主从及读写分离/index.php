<?php
include "Redis.config.php";
include "Redis.class.php";

$redisMS = new RedisMS($server);



$redisMS->runCall('set',["test","haha"]);
echo $redisMS->runCall( "get",["test"]);
//var_dump($redisMS->log);
//var_dump($redisMS->runCall('set',["test","haha"]));

// var_dump([
//     'master' => $redisMS->ConnMaster(),
//     'slaves' => $redisMS->ConnSlave(),
// ]);

?>

