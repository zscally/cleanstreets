<?php

use lib\Config;

$config = array(
    'environment' => 'local', //define Production / dev / local
    'production' => array(
        'system' => array(
            'domain' => 'https://cleanstreets.louisvilleky.gov',
            'api_key' => 'DEFINE YOUR API KEY HERE' //usally a sha256 salted key will do.
        ),
        'db' => array(
            'host' => 'localhost',
            'driver' => 'mysql',
            'port' => '3306',
            'name' => '',
            'user' => '',
            'password' => '',
            'connect_timeout' => 15,
            'path' => 'https://cleanstreets.com' // DOMAIN 
        ),
        'govDelivery' => array(
            'URL' => 'https://api.govdelivery.com/api/account/',
            'accountCode' => '',
            'username' => '',
            'password' => ''
        )
    ),
);



//Databasses
Config::write('db.host', $config[strtolower($config['environment'])]['db']['host']);
Config::write('db.driver', $config[strtolower($config['environment'])]['db']['driver']); //mysql, mssql etc.
Config::write('db.port', $config[strtolower($config['environment'])]['db']['port']);
Config::write('db.name', $config[strtolower($config['environment'])]['db']['name']);
Config::write('db.user', $config[strtolower($config['environment'])]['db']['user']);
Config::write('db.password', $config[strtolower($config['environment'])]['db']['password']);
Config::write('db.connect_timeout', $config[strtolower($config['environment'])]['db']['connect_timeout']);

//gov-delivery
Config::write('govDelivery.URL', $config[strtolower($config['environment'])]['govDelivery']['URL']);
Config::write('govDelivery.accountCode', $config[strtolower($config['environment'])]['govDelivery']['accountCode']);
Config::write('govDelivery.username', $config[strtolower($config['environment'])]['govDelivery']['username']);
Config::write('govDelivery.password', $config[strtolower($config['environment'])]['govDelivery']['password']);

//system
Config::write('system.domain', $config[strtolower($config['environment'])]['system']['domain']);
Config::write('system.api_key', $config[strtolower($config['environment'])]['system']['api_key']);

// Project Config
Config::write('path', $config[strtolower($config['environment'])]['db']['path']);
