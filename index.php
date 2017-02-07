<?php
/**
 * Приложение третьего типа для Битрикс24 и WebHooks - упрощенный вариант rest-событий и rest-команд, без написания приложения.
 * Для выбора закомментировать лишнее (конфиг и require 'include/app.php' или require 'include/hook.php' соответственно)
 * Скрипт лежит на своем хостинге, по cron подключается к Битрикс24, проверяет не собирается ли кто-то из пользователей в отпуск через 3, 2 и 1 неделю, отправляет в Б24 уведомление об этом событии руководителю отправляющегося в отпуск сотрудника. Опционально можно оповещать сразу из скрипта по e-mail.
 *
 * @author Mad Max <programmint48@yandex.ru>
 * @version 1.0 27.01.2017
 */

session_start();

/**
 * Подключаем файл конфигурации
 */
$config = file_exists('include/config-dev.php') ? require_once 'include/config-dev.php' : require_once 'include/config.php';

/**
 * Подключаем файл с функциями обращения по API
 */
require_once 'include/rest.php';


//$_SESSION['config']  = $config['app'];
//require 'include/app.php';

$_SESSION['config'] = $config['webhook'];
require 'include/hook.php';


// получение списка всех работников
$users = user_get();
$mans = [];

foreach ($users as $user) {
    $department_id = $user['UF_DEPARTMENT'][0];
    //echo "user:" . $user['ID'] . " " . $user['NAME'] . "<br>";

    //подразделение работника
    $department = department_get($department_id); //на нескольких подразделениях не тестил
    //echo "department:" . $departmentid . " " . $department['NAME'] . "<br>";

    //руководитель подразделения
    $managerid = $department['UF_HEAD'];
    //echo "managerid:" . $managerid . "<br><br><br>";

    $man = [];

    $man['user_id'] = $user['ID'];
    $man['user_name'] = $user['NAME'];
    $man['user_lastname'] = $user['LAST_NAME'];
    $man['user_email'] = $user['EMAIL'];
    $man['user_workposition'] = $user['WORK_POSITION'];
    $man['department_id'] = $department['ID'];
    $man['department_name'] = $department['NAME'];
    $man['manager_id'] = $department['UF_HEAD'];

    $mans[$user['ID']] = $man;
}


echo '<pre>';
print_r($mans);
echo '</pre>';


// перебор календарей всех пользователей
foreach ($mans as $employee) {

    // интервал = месяц
    $data = calendar_event_get($employee['user_id'], (new DateTime())->format('Y-m-d'), (new DateTime())->modify('+1 month')->format('Y-m-d'));


    foreach ($data as $row) {

        foreach ($row as $event) {

            if ($event['ACCESSIBILITY'] == "absent") { // отсутствовал - в отпуске
                //echo "Сегодня: " . (new DateTime())->format('d.m.Y') . "<br><br>";

                $holiday_begin = date('d.m.Y', strtotime($event['DATE_FROM']));  // дата начала отпуска
                $holiday_end = date('d.m.Y', strtotime($event['DATE_TO']));  // дата окончания  отпуска

                $message =
                    $employee['user_workposition'] . " " .
                    $employee['user_name'] . " " .
                    $employee['user_lastname'] .
                    " будет отсутствовать на рабочем месте  c " .
                    $holiday_begin .
                    " по " .
                    $holiday_end .
                    " по причине: " .
                    $event['NAME'];


                // цикл по неделям
                for ($i = 1; $i <= 3; $i++) {

                    if ((new DateTime())->modify("+$i week")->format('d.m.Y') == $holiday_begin) {

                        $alarm = "до отпуска ровно $i неделя. Отправляем почту";

                        if ($mans[$employee['manager_id']]['user_id'] == $employee['user_id']) {  // если сам манагер, то главному

                            $email = $mans[1]['user_email'];
                            $notify_userid = $mans[1]['user_id'];
                            $alarm .= " Главному " . $email;

                        } else { // если не манагер, то манагеру

                            $email = $mans[$employee['manager_id']]['user_email'];
                            $notify_userid = $mans[$employee['manager_id']]['user_id'];

                            $alarm .= " руководителю " . $mans[$employee['manager_id']]['user_name'] . " на почту " . $email;
                        }

                        echo "<hr>" . $message . " " . $alarm . "<br><br>";
                        // отправка почты
                        mailer($email, $message);

                        echo "<b>Уведомление в  Битрикс24 </b><i> для user_id=" . $notify_userid . ": " . $message . "</i><br>";
                        // отправка оповещения в Битрикс24
                        // notify($notify_userid, $message);
                    }
                }
            }
        }
    }

}

