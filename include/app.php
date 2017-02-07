<?php

/**
 * Авторизация в Битрикс24
 *
 * @param $config
 * @return array
 */
function auth($config)
{

    $_url = 'https://' . $config['domain'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $_url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $res = curl_exec($ch);
    $l = '';
    if (preg_match('#Location: (.*)#', $res, $r)) {
        $l = trim($r[1]);
    }
//echo $l.PHP_EOL;
    curl_setopt($ch, CURLOPT_URL, $l);
    $res = curl_exec($ch);
    preg_match('#name="backurl" value="(.*)"#', $res, $math);
    $post = http_build_query([
        'AUTH_FORM' => 'Y',
        'TYPE' => 'AUTH',
        'backurl' => $math[1],
        'USER_LOGIN' => $config['login'],
        'USER_PASSWORD' => $config['password'],
        'USER_REMEMBER' => 'Y'
    ]);
    curl_setopt($ch, CURLOPT_URL, 'https://www.bitrix24.net/auth/');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $res = curl_exec($ch);
    $l = '';
    if (preg_match('#Location: (.*)#', $res, $r)) {
        $l = trim($r[1]);
    }
//echo $l.PHP_EOL;
    curl_setopt($ch, CURLOPT_URL, $l);
    $res = curl_exec($ch);
    $l = '';
    if (preg_match('#Location: (.*)#', $res, $r)) {
        $l = trim($r[1]);
    }
//echo $l.PHP_EOL;
    curl_setopt($ch, CURLOPT_URL, $l);
    $res = curl_exec($ch);
//end autorize
    curl_setopt($ch, CURLOPT_URL, 'https://' . $config['domain'] . '/oauth/authorize/?response_type=code&client_id=' . $config['client_id']);
    $res = curl_exec($ch);
    $l = '';
    if (preg_match('#Location: (.*)#', $res, $r)) {
        $l = trim($r[1]);
    }
    preg_match('/code=(.*)&do/', $l, $code);
    $code = $code[1];
    curl_setopt($ch, CURLOPT_URL, 'https://' . $config['domain'] . '/oauth/token/?grant_type=authorization_code&client_id=' . $config['client_id'] . '&client_secret=' . $config['client_secret'] . '&code=' . $code . '&scope=' . $config['scope']);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $res = curl_exec($ch);
    curl_close($ch);

    //echo "<pre>" . $res . "</pre>";
    return (array)json_decode($res);
}


/**
 * Новая авторизация в Битрикс24
 *
 * @param $config
 * @return array
 */

function auth_new($config)
{

    unset($_SESSION["query_data"]);

    $_SESSION["query_data"] = auth($config); //  авторизация

    $_SESSION["query_data"]["ts"] = time(); // текущее время

    return $_SESSION["query_data"];
}


/**
 * Обновление токена авторизации по auth_token по refresh_token
 *
 * @param $config
 * @return array
 */
function auth_refresh($config)
{
    $params = array(
        "grant_type" => "refresh_token",
        "client_id" => $config['client_id'],
        "client_secret" => $config['client_secret'],
        "redirect_uri" => "",
        "scope" => $config['scope'],
        "refresh_token" => $_SESSION["query_data"]["refresh_token"],
    );

    $query_data = query("GET", "https://" . $_SESSION["query_data"]["domain"] . "/oauth/token/", $params);

    if (isset($query_data["access_token"])) {
        $_SESSION["query_data"] = $query_data;
        $_SESSION["query_data"]["ts"] = time(); // обновление времени действия токена
    }

    return $_SESSION["query_data"];
}


/**
 * Определение оставшегося времени до истечения токена auth_token
 *
 * @return integer кол-во оставшихся секунд
 */
function auth_status()
{
    return $_SESSION["query_data"]["ts"] + $_SESSION["query_data"]["expires_in"] - time();
}


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
    return query("POST", "https://" . $domain . "/rest/" . $method, $params);
}


/**
 * curl запрс
 *
 * @param $method
 * @param $url
 * @param null $data
 * @return mixed
 */
function query($method, $url, $data = null)
{
    $curlOptions = array(
        CURLOPT_RETURNTRANSFER => true
    );

    if ($method == "POST") {
        $curlOptions[CURLOPT_POST] = true;
        $curlOptions[CURLOPT_POSTFIELDS] = http_build_query($data);
    } elseif (!empty($data)) {
        $url .= strpos($url, "?") > 0 ? "&" : "?";
        $url .= http_build_query($data);
    }

    $curl = curl_init($url);
    curl_setopt_array($curl, $curlOptions);
    $result = curl_exec($curl);

    return json_decode($result, 1);
}


// если авторизация еще не пройдена или уже истекла
if (time() > $_SESSION["query_data"]["ts"] + $_SESSION["query_data"]["expires_in"]) {

    $auth = auth_new($_SESSION['config']);

    echo "<h3>Новая авторизация</h3>";

    print_r($auth);

} else if ($_SESSION["query_data"]["ts"] + $_SESSION["query_data"]["expires_in"] - time() < $_SESSION["query_data"]["expires_in"] / 4) {//обновить ключ авторизации

    $auth = auth_refresh($_SESSION['config']);

    echo "<h3>Обновление ключа авторизации</h3>";

    print_r($auth);

} else {

    echo "<h3>Авторизационные данные истекут через " . auth_status() . " секунд</h3>";
}

