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

$oldDomain = 'apparatov.net';
$redirectDomain = 'apparatov.azurewebsites.net';

$yandexPos = stripos($user_referer, 'yandex');
$sitePos = stripos($user_referer, $redirectDomain);
if ( !$user_referer ){
    include_once '403.php';
    exit();
}

header('Location: https://'.$redirectDomain.$serverRequestUri, true, 301);
exit;

function curlProxy($mirror)
{
    global $oldDomain, $redirectDomain, $user_agent;
    $url = "https://{$mirror}{$_SERVER['REQUEST_URI']}";
    // create a new cURL resource
    $ch = curl_init();
    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
    $result = curl_exec($ch);
    $result = str_replace($oldDomain, $redirectDomain, $result);
    $info = curl_getinfo($ch);
    $contentType = $info['content_type'];
    @header("Content-Type: $contentType");
    // close cURL resource, and free up system resources
    curl_close($ch);
    return $result;
}