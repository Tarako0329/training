<?php
session_start();
// 共通ヘッダー
date_default_timezone_set('Asia/Tokyo');
require_once "./vendor/autoload.php";
require_once "functions.php";
//$time="ver1.21.0";

//.envの取得
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define("MAIN_DOMAIN",$_ENV["MAIN_DOMAIN"]);
define("EXEC_MODE",$_ENV["KANKYO"]);
define("APP_NAME",$_ENV["APP_NAME"]);
if(EXEC_MODE==="Local"){
  define("ROOT_URL","http://".MAIN_DOMAIN."/");
}else{
  define("ROOT_URL","https://".MAIN_DOMAIN."/");
}
if(EXEC_MODE==="Product"){
  $time="2026-04-03";	//リリース日
}else{
  $time=date("YmdHis");
}

//メール送信関連
define("HOST", $_ENV["HOST"]);
define("PORT", $_ENV["PORT"]);
define("FROM", $_ENV["FROM"]);
define("PROTOCOL", $_ENV["PROTOCOL"]);
define("POP_HOST", $_ENV["POP_HOST"]);
define("POP_USER", $_ENV["POP_USER"]);
define("POP_PASS", $_ENV["POP_PASS"]);
define("SYSTEM_NOTICE_MAIL",$_ENV["SYSTEM_NOTICE_MAIL"]);


//DB接続関連
define("DNS","mysql:host=".$_ENV["SV"].";dbname=".$_ENV["DBNAME"].";charset=utf8");
define("USER_NAME", $_ENV["DBUSER"]);
define("PASSWORD", $_ENV["PASS"]);
define("DB_HOST", $_ENV["SV"]);
define("DB_NAME", $_ENV["DBNAME"]);
// DBとの接続
$pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());  //CLASS化

spl_autoload_register(function ($className) {
  // 1. 名前空間のバックスラッシュ '\' を、OS標準のパス区切り文字（通常は '/'）に置換
  $path = str_replace('\\', DIRECTORY_SEPARATOR, $className);
  // 2. クラスファイルを探すフルパスを組み立て
  $file = __DIR__.DIRECTORY_SEPARATOR.$path.'.php';
  //log_writer2("Autoloading class", $className . " (Path: " . $file . ")", "lv3");
  // 3. ファイルが存在すれば読み込む
  if (file_exists($file)) {
    require_once $file;
    //log_writer2("Autoloading success", "Class: " . $className . " (Expected Path: " . $file . ")", "lv3");
  }else{
    log_writer2("Autoloading failed", "Class: " . $className . " (Expected Path: " . $file . ")", "lv3");
  }
});

class_alias('classes\Utilities\Utilities','U');
use classes\Database\Database;

$db = new Database();
define("key","bonBer");

// シャットダウン時に実行される関数を登録
register_shutdown_function(function () {
    // 最後に発生したエラーを取得
    $error = error_get_last();

    // エラーが存在し、かつ致命的なエラー（E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR）の場合
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        
        $to = SYSTEM_NOTICE_MAIL; // 開発者のメールアドレス
        $subject = "【重要】システムフェータルエラー発生";
        
        // メールの本文を作成
        $body = "致命的なエラーが発生しました。\n\n";
        $body .= "メッセージ: " . $error['message'] . "\n";
        $body .= "ファイル: " . $error['file'] . "\n";
        $body .= "行数: " . $error['line'] . "\n";
        $body .= "発生時刻: " . date('Y-m-d H:i:s') . "\n";
        $body .= "URL: " . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'CLI') . "\n";

        $fromName = "System Watcher";

        // Utilitiesクラスのメソッドを呼び出し
        // ※クラスがオートロードされているか、requireされている必要があります
        U::send_mail(
            $to,
            $subject,
            $body,
            $fromName
        );
    }
});
?>