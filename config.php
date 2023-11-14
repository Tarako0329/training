<?php
session_start();
// 共通ヘッダー
date_default_timezone_set('Asia/Tokyo');
require "./vendor/autoload.php";
require "functions.php";



//.envの取得
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define("MAIN_DOMAIN",$_ENV["MAIN_DOMAIN"]);
define("ROOT_URL","http://".MAIN_DOMAIN."/");
define("EXEC_MODE",$_ENV["KANKYO"]);
$time=date('Ymd-His');

//DB接続関連
define("DNS","mysql:host=".$_ENV["SV"].";dbname=".$_ENV["DBNAME"].";charset=utf8");
define("USER_NAME", $_ENV["DBUSER"]);
define("PASSWORD", $_ENV["PASS"]);
// DBとの接続
$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());

// =========================================================
// オンラインリンク　設定ファイル
// =========================================================
//データベース設定

define("key","bonBer");
// =========================================================
// MySQLエラーレポート用共通宣言
// =========================================================





// =========================================================
// アクセスログ記録
// =========================================================

//未実装

// =========================================================
// 自動ログインチェック
// =========================================================


?>



