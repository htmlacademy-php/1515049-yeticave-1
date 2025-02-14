<?php
session_start();

date_default_timezone_set('Europe/Moscow');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


require_once 'functions/template.php';
require_once 'functions/validators.php';
require_once 'functions/db.php';
require_once 'functions/process-file-upload.php';
require_once 'functions/handle-form.php';
require_once 'functions/email.php';

$config = require 'config.php';
$dbConnection = dbConnect($config);
$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$categories = getCategories($dbConnection, $categoryId);

$pageItems = 9;
$curPage = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

$paginationData = getPaginationData($dbConnection, $categoryId, $pageItems, $curPage);

$pagination = includeTemplate('pagination.php', [
    'pages' => $paginationData['pages'],
    'pagesCount' => $paginationData['pagesCount'],
    'curPage' => $curPage,
    'prevPageUrl' => $paginationData['prevPageUrl'],
    'nextPageUrl' => $paginationData['nextPageUrl']
]);

$user = getUserData($dbConnection);
$userName = $user ? $user['name'] : '';
$userId = $user ? $user['id'] : '';
