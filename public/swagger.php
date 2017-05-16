<?php
require '../vendor/autoload.php';
$swagger = \Swagger\scan('../routers');
header('Content-Type: application/json');
echo $swagger;