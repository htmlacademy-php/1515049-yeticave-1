<?php
session_start();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


require_once 'functions/template.php';
require_once 'functions/validators.php';
require_once 'functions/db.php';
require_once 'functions/process-file-upload.php';
require_once 'functions/handle-form.php';

$config = require 'config.php';
$dbConnection = dbConnect($config);

$isAuth = isUserAuthenticated();
$userName = $isAuth ? $_SESSION['user']['name'] : '';
