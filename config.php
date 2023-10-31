<?php
session_start();

// =========================================================
// オンラインリンク　設定ファイル
// =========================================================
//データベース設定

define("sv", "localhost");
define("user", "ifduktdo_KINNIKU");
define("pass", "C@THNEPrZZXe");

define("key","bonBer");


//=========================================================
//  本番・テストのデータベースの切り替えはここで行います。
//  有効にしたいコードをコメントから外してください。
//=========================================================
//データベース切り替え
define("dbname", "ifduktdo_MASSURU");
if(__FILE__=="/home/ifduktdo/public_html/training_test/config.php"){
	//echo "test";
	define("dbname", "ifduktdo_MASSURU_test");
}else if(__FILE__=="/home/ifduktdo/public_html/training/config.php"){
	//echo "本番";
	define("dbname", "ifduktdo_MASSURU");
}else{
	//echo "ERROR<BR>";
	//exit();
}

$mysqli = new mysqli(sv, user, pass, dbname);
$mysqli->set_charset("utf8mb4");
// =========================================================
// MySQLエラーレポート用共通宣言
// =========================================================
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);





// =========================================================
// アクセスログ記録
// =========================================================

//未実装

// =========================================================
// 自動ログインチェック
// =========================================================




?>



