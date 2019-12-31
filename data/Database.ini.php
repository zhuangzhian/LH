<?php
return array(
        'driver' => 'Pdo',
        'dsn' => 'mysql:dbname=sql_127_0_0_21;port=3306;host=localhost;charset=utf8',
        'username' => 'sql_127_0_0_21',
        'password' => 'nebYE2dxFK28JXja',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))"
        )
);