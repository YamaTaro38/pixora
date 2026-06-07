<?php
$curl_options = array(
    10002 => 'http://example.com',
    10023 => array(
        0 => 'Content-Type: application/json',
        1 => 'Accept: application/json',
        2 => 'User-Agent: midtrans-php-v2.6.2',
        3 => 'Authorization: Basic dXNlcm5hbWU6'
    ),
    19913 => 1
);

$Config_curlOptions = array(
    64 => false,
    81 => false,
    10023 => array('X-Dummy: true')
);

$mergedHeaders = array_merge($curl_options[10023], $Config_curlOptions[10023]);
$headerOptions = array(10023 => $mergedHeaders);

$curl_options_final = array_replace_recursive($curl_options, $Config_curlOptions, $headerOptions);

print_r($curl_options_final[10023]);
