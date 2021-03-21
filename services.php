<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

use \Firebase\JWT\JWT;

require('./vendor/autoload.php');
require('./config.php');
require('./help.php');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  return 0;
}

if (!preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
  header('HTTP/1.0 400 Bad Request');
  echo 'Token not found in request';
  exit;
}

$jwt = $matches[1];
if (!$jwt) {
  http_response_code(400);
  exit;
}

$token = JWT::decode($jwt, $jwt_secret, ['HS512']);
$now = new DateTimeImmutable();

if (
  $token->iss !== $serverName ||
  $token->nbf > $now->getTimestamp() ||
  $token->exp < $now->getTimestamp()
) {
  http_response_code(401);
  exit;
}

if (isset($_GET['action'])) {
  $action = $_GET['action'];
  if ($action == 'getAppDataById') {
    $appId = $_GET['appId'];
    $appData = db_fetch_array(
        "SELECT * FROM $shop_installed
        LEFT JOIN $tbl_usersettings ON $tbl_usersettings.store_name = $shop_installed.shop AND $tbl_usersettings.app_id = $shop_installed.app_id
        WHERE $shop_installed.app_id = '$appId'
        AND $tbl_usersettings.status = 'active'
        "
      );
    if (!empty($appData)) {
      echo json_encode($appData);
    } else http_response_code(404);
  }
}
