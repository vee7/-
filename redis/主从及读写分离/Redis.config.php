<?php
/*
**redis开启主从方法：
**1.redis.conf下加入 slaveof MasterIP MasterPort
**2.redis开启时 redis-server slaveof MasterIP MasterPort
**3.redis-cli命令输入 slaveof MasterIP MasterPort
**查看是否开启 redis-cli命令输入 info replication
 */
$server = [
	"is_open"=>True,
	"master"=>["host"=>"192.168.137.77","port"=>6380],
	"slaves"=>[
		["host"=>"192.168.137.77","port"=>6381],
		["host"=>"192.168.137.77","port"=>6382]
	]
];

?>