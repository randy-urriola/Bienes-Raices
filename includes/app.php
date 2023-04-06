<?php

use Dotenv\Dotenv;
use Model\ActiveRecord;
require 'funciones.php';
require 'config/database.php';
require __DIR__ . '/../vendor/autoload.php';

// Conectarnos a la BD
$db = conectarDB();

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

ActiveRecord::setDB($db);