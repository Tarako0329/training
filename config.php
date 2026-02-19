<?php
session_start();
// 共通ヘッダー
date_default_timezone_set('Asia/Tokyo');
require_once "./vendor/autoload.php";
require_once "functions.php";
$time="ver1.21.0";

//.envの取得
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define("MAIN_DOMAIN",$_ENV["MAIN_DOMAIN"]);
define("EXEC_MODE",$_ENV["KANKYO"]);
if(EXEC_MODE==="local"){
  define("ROOT_URL","http://".MAIN_DOMAIN."/");
}else{
  define("ROOT_URL","https://".MAIN_DOMAIN."/");
}
if(EXEC_MODE==="Product"){
  $time="2026-02-13";	//リリース日
}else{
  $time=date("YmdHis");
}


//DB接続関連
define("DNS","mysql:host=".$_ENV["SV"].";dbname=".$_ENV["DBNAME"].";charset=utf8");
define("USER_NAME", $_ENV["DBUSER"]);
define("PASSWORD", $_ENV["PASS"]);
define("DB_HOST", $_ENV["SV"]);
define("DB_NAME", $_ENV["DBNAME"]);
// DBとの接続
$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());  //CLASS化

define("key","bonBer");
?>