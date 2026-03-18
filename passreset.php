<?php
  require_once "config.php";
  //require_once "database.php";
  //$db = new Database();	

  //トランザクション処理
  //log_writer2("\$POST",$_POST,"lv3");
  if($_POST["btn"] === "パスワード更新"){
    //新規ユーザ登録画面の「登録」ボタン
		$id = ($_POST["id2"]) ?? -1;
		$birthday = !empty($_POST["birthday"])?$_POST["birthday"]:"%";
		$sql = "SELECT * from users where id=:id and height = :height and birthday like :birthday and sex = :sex";
		$row = $db->SELECT($sql,[":id" => $id,":height" => $_POST["fname"],":birthday" => $birthday,":sex" => $_POST["sex"]]);
		$row_cnt = count($row);
		if($row_cnt!==1){
			$_SESSION["msg"]="入力されたIDに紐づく情報が一致してません。";
		}
		$db->begin_tran();
		$pass = passEx($_POST["pass2"],$id);
		$sql = "UPDATE users set pass = :pass where id = :id;";
		$db->UP_DEL_EXEC($sql,[":pass" => $pass,":id" => $id]);
		$db->commit_tran();
		
		echo "<P>パスワードを更新しました。</P>";
		echo "<P>ログイン画面から再度ログインしてください。</P>";
		echo "<a href='index.php'> ログイン画面へ</a>";
		$_SESSION["msg"]="パスワードを更新しました。ログインしてください。";
	}
   
  //ログイン失敗
  //リダイレクト
  header("HTTP/1.1 301 Moved Permanently");
  header("Location: index.php");
  exit();
?>