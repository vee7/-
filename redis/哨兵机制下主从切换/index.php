 <?php
$hosts = [
    [
        'host' => '127.0.0.1',
        'port' => 26381
    ],
    [
        'host' => '127.0.0.1',
        'port' => 26380
    ]
];
$masterName = 'mymaster';
$sredis = new SRedis($hosts, $masterName);
$masterRedis = $sredis->getInstansOf();
if ($masterRedis) {
    print_r($masterRedis->hgetall("iplist"));
} else {
    echo "redis 服务器连接失败";
}