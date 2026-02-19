<?php
require_once "config.php";
require_once "database.php";
//トランザクション処理

//結果書き込み
if(isset($_SESSION['USER_ID'])){
	$id = $_SESSION['USER_ID'];
	decho ("session:".$id);
}else if (check_auto_login($_COOKIE['token'])==0) {
	$id = $_SESSION['USER_ID'];
	decho ("クッキー:".$id);
}else{
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: index.php");
	exit();
}


try{
	/*
  $pdo_h->beginTransaction();
	$sql = "delete from tr_log where id =? and ymd = ? and jun = ?";
	$stmt = $pdo_h->prepare($sql);
	$stmt->bindValue(1, $id, PDO::PARAM_STR);
	$stmt->bindValue(2, $_POST["k_ymd"], PDO::PARAM_STR);
	$stmt->bindValue(3, $_POST["k_jun"], PDO::PARAM_STR);
	$stmt->execute();
	$pdo_h->commit();
	*/
	$db = new Database();
	$db->begin_tran();
	$sql = "DELETE from tr_log where id = :id and ymd = :ymd and jun = :jun";
	$db->UP_DEL_EXEC($sql,[":id" => $id,":ymd" => $_POST["k_ymd"],":jun" => $_POST["k_jun"]]);
	$db->commit_tran();

}catch(Exception $e){
  //$pdo_h->rollBack();
	$msg = "catch Exception \$e：".$e." [SQL = ".$sql." ]";
	$db->rollback_tran();

}
//ログイン失敗
//リダイレクト
header("HTTP/1.1 301 Moved Permanently");
header("Location: index.php");
exit();
?>