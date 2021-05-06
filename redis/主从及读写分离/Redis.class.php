<?php
class RedisMS
{
	protected $server;
	protected $conn;
	protected $log;
	protected $call = [
		//哪些命令为写操作
		"write"=>[
			"set",
			"hset",
			"sadd",
			"lpush",
			"lpop",
			"rpush",
			"rpop",
			"incr",
			"decr"
		],
		//哪些命令为读操作
		"read"=>[
			"get",
			"hget",
			"hgetall",
			"smembers",
			"llen"
		]
	];

	//构造函数，实例化时初始化
	public function __construct($server)
	{
		$this->server = $server;
		//配置文件控制主从开关，False的时候全部操作master服务器
		if($server["is_open"]!=True){
			$this->conn["master"] = "配置未开启";
			$this->conn["slave"] = "配置未开启";
		}else{
			$this->conn["master"] = $this->ClientRedis($this->server["master"]["host"],$this->server["master"]["port"]);
			$this->conn["slave"] = $this->ClientRedis($this->server["master"]["host"],$this->server["master"]["port"]);
		}

	}

	//返回Master连接对象
	public function ConnMaster()
	{
		return $this->conn["master"];
	}

	//返回slave连接对象
	public function ConnSlave()
	{
		return $this->conn["slave"];
	}

	//连接Redis
	protected function ClientRedis($host,$port){
		$redis = new Redis();
        $redis->pconnect($host, $port);
        return $redis;
	}

	//获取命令-获取连接对象-执行命令
	public function runCall($command,$params=[])
	{
		$redis = $this->GetRedisCall($command);
		if($redis){
			return $redis->{$command}(...$params);
		}else{
			return "连接Redis失败.";
		}

	}

	//判断命令是读或写，分别连接主或从服务器
	//---主服务器直接连接返回连接对象
	//---从服务器进行获取其中一台
	protected function GetRedisCall($command)
	{
		if(in_array($command,$this->call["write"])){
			$this->log = "连接主服务器：".$this->server["master"]["host"].":".$this->server["master"]["port"];
			$this->conn["master"] = $this->ClientRedis($this->server["master"]["host"],$this->server["master"]["port"]);
			return $this->ConnMaster();

		}elseif(in_array($command,$this->call["read"])){
			$this->conn["slave"] = $this->ClientSlaveRedis();
			return $this->ConnSlave();

		}else{
			return "不支持该命令";
		}
	}

	//从配置文件获取从服务器的信息，判断数量，随机选择一台返回连接对象，也可做轮询算法
	protected function ClientSlaveRedis(){
		$slaves = $this->server["slaves"];
		$idx = mt_rand(0,count($slaves)-1);
		$this->log = "连接从服务器：".$slaves[$idx]["host"].":".$slaves[$idx]["port"];
		return $this->ClientRedis($slaves[$idx]["host"],$slaves[$idx]["port"]);
	}



}
?>
