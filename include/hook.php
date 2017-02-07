<?php
/**
 * Фунцкции для подключения к Битрикс24 и вызова API
 *
 */
/**
 * Вызов Битрикс24 метода
 *
 * @param $domain
 * @param $method
 * @param $params
 * @return mixed
 */
function call($domain, $method, $params)
{
    return query($_SESSION['config']['domain'], $_SESSION['config']['userid'], $_SESSION['config']['hook'], $method, $params);
}

/**
 * curl запрос
 *
 * @param $domain
 * @param $userid
 * @param $hook
 * @param $method
 * @param null $params
 * @return mixed
 */
function query($domain, $userid, $hook, $method, $params = null)
{

    $url = "https://" . $domain . "/rest/" . $userid . "/" . $hook . "/" . $method;

    $fields = http_build_query($params);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        CURLOPT_POSTFIELDS => $fields,
    ));

    $result = curl_exec($curl);
    curl_close($curl);

    return json_decode($result, 1);
}

