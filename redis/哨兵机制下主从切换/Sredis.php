<?php

class SRedis
{

    /**
     * 哨兵地址，支持多哨兵地址
     * @var array
     * eg:  [ [ 'host' => '127.0.0.1' , 'port' => 26379 ] ]
     */
    private $_sentinelAddr = [];

    private $_sentinelConn = null;

    private $_timeout = 10; //超时时间

    private $_masterName = 'mymaster'; //主节点名称

    private static $_handle = []; //存放redis连接实例

    public function __construct(array $iplist, string $masterName = null)
    {
        $this->_sentinelAddr = $iplist;
        $masterName !== null && $this->_masterName = $masterName;
        $this->_getSentinelConn();
    }

    /**
     * 获取redis主节点的实例
     * @return bool|Redis
     * @throws Exception
     */
    public function getInstansOf()
    {
        $masterInfo = $this->getMasterInfo();
        if ($masterInfo) {
            $instansof = $this->_connection($masterInfo[0], $masterInfo[1], $this->_timeout);
            return $instansof;
        }
        return false;
    }

    /**
     * 获取主节点的ip地址
     * @return array
     */
    public function getMasterInfo()
    {
        $masterInfo = [];
        if ($this->_sentinelConn != null) {
            $masterInfo = $this->_sentinelConn->rawcommand("sentinel", 'get-master-addr-by-name', $this->_masterName);
        }
        return $masterInfo;

    }

    /**
     * 设置哨兵连接句柄
     */
    private function _getSentinelConn()
    {
        if (is_array($this->_sentinelAddr) && $this->_sentinelAddr) {
            $this->_sentinelConn = $this->_RConnect($this->_sentinelAddr);
        }
    }

    /**
     * 获取redis句柄（如果是多主机，保证连接的是可用的哨兵服务器）
     * @param array $hosts
     * @return null|Redis
     */
    private function _RConnect(array $hosts)
    {
        $count = count($hosts);
        $redis = null;
        if ($count == 1) {
            $this->_connection($hosts[0]['host'], $hosts[0]['port'], $this->_timeout);
        } else {
            $i = 0;
            while ($redis == null && $i < $count) {
                $redis = $this->_connection($hosts[$i]['host'], $hosts[$i]['port'], $this->_timeout);
                $i++;
            }
        }
        return $redis;
    }

    /**
     * redis 连接句柄
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @return null|Redis
     */
    private function _connection(string $host, int $port, int $timeout)
    {
        if (isset(self::$_handle[$host . ':' . $port])) {
            return self::$_handle[$host . ':' . $port];
        }
        try {
            $redis = new Redis();
            $redis->connect($host, $port, $timeout);
            self::$_handle[$host . ':' . $port] = $redis;
        } catch (\Exception $e) {
            $redis = null;
        }
        return $redis;
    }
}