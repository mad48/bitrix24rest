<?php
/**
 * Фунцкции для подключения к Битрикс24 и вызова API 
 *
 */

/**
 * Список всех сотрудников
 *
 * @return mixed
 */
function user_get()
{
    return call($_SESSION["query_data"]["domain"], "user.get", array(
            "auth" => $_SESSION["query_data"]["access_token"],
        )
    )['result'];
}


/**
 * Подразделение сотрудника
 *
 * @return mixed
 */

function department_get($department_id)
{
    return call($_SESSION["query_data"]["domain"], "department.get", array(
            "auth" => $_SESSION["query_data"]["access_token"],
            "ID" => $department_id
        )
    )['result'][0];
}


/**
 * Получение данных календаря // есть альтернатива только для absent
 *
 * @param $owner_id
 * @param $from
 * @param $to
 * @return mixed
 */
function calendar_event_get($owner_id, $from, $to)
{
    return call($_SESSION["query_data"]["domain"], "calendar.event.get", array(
            "auth" => $_SESSION["query_data"]["access_token"],
            "type" => 'user',
            "ownerId" => $owner_id, // id владельца календаря
            "from" => $from, // текущая дата
            "to" => $to //конечная дата
        )
    );
}


/**
 * Отправка уведомления в  Битрикс24
 *
 * @param $user_id
 * @param $message
 * @return mixed
 */
function notify($user_id, $message)
{
    return call($_SESSION["query_data"]["domain"], "im.notify", array(
            "auth" => $_SESSION["query_data"]["access_token"],
            "to" => $user_id,
            "message" => $message
        )
    );
}


/**
 * Заглушка отправки email
 *
 * @param $email
 * @param $message
 */
function mailer($email, $message)
{
    //mail($email, "Bitrix24 alarm", $message);
    return false;

}