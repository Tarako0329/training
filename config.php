<?php
session_start();
// 共通ヘッダー
date_default_timezone_set('Asia/Tokyo');
require "./vendor/autoload.php";
require "functions.php";
$time="ver1.20.9";

//.envの取得
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define("MAIN_DOMAIN",$_ENV["MAIN_DOMAIN"]);
define("EXEC_MODE",$_ENV["KANKYO"]);
if(EXEC_MODE==="local"){
  define("ROOT_URL","http://".MAIN_DOMAIN."/");
  $time=date('Ymd-His');
}else{
  define("ROOT_URL","https://".MAIN_DOMAIN."/");
  
}


//DB接続関連
define("DNS","mysql:host=".$_ENV["SV"].";dbname=".$_ENV["DBNAME"].";charset=utf8");
define("USER_NAME", $_ENV["DBUSER"]);
define("PASSWORD", $_ENV["PASS"]);
// DBとの接続
$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());

define("key","bonBer");
?>