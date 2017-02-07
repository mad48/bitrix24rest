<?php

return [

    'app' => [
        //идентификатор приложения
        'client_id' => 'local.xxxxxxxxxxxxxxxxxxxxxxxxx',
        //секретный код приложения
        'client_secret' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        //доступ
        'scope' => 'user,task,im,tasks_extended,lists,mailservice,log,department',
        //домен третьего уровня клиентского проекта в Bitrix24
        'domain' => 'xxxxxxxx.bitrix24.ru',
        //данные пользователя для авторизации
        'login' => 'xxxxxxx@xxxxxxx.xx',
        'password' => 'xxxxxxxxxx'
    ],

    'webhook' => [
        //идентификатор хука
        'hook' => 'xxxxxxxxxxxxxxxxx',
        //домен третьего уровня клиентского проекта в Bitrix24
        'domain' => 'xxxxxxxxx.bitrix24.ru',
        //идентификатор пользователя
        'userid' => '1',
    ]
    
];