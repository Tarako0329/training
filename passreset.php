<?php
  require "config.php";
  //require "functions.php";
  //トランザクション処理
  //log_writer2("\$POST",$_POST,"lv3");
  if($_POST["btn"] == "パスワード更新"){
    //新規ユーザ登録画面の「登録」ボタン
		$id = ($_POST["id2"]);
		$birthday = !empty($_POST["birthday"])?$_POST["birthday"]:"%";
		$sql = "select * from users where id=:id and height = :height and birthday like :birthday and sex = :sex";
		$stmt = $pdo_h->prepare( $sql );
		$stmt->bindValue("id", $_POST["id2"], PDO::PARAM_STR);
		$stmt->bindValue("height", ($_POST['fname']), PDO::PARAM_STR);
		$stmt->bindValue("birthday", $birthday, PDO::PARAM_STR);
		$stmt->bindValue("sex", $_POST["sex"], PDO::PARAM_STR);
		$stmt->execute();
		$row_cnt = $stmt->rowCount();
		if($row_cnt!==1){
			echo "<P>入力されたIDに紐づく情報が一致してません。</P>";
			echo "<a href='index.php'> 戻る</a>";
			echo $birthday." / ".($_POST['fname']);
			exit();
		}
		$pass = passEx($_POST["pass2"],$id);
		$sql = "update users set pass = ? where id = ?;";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue(1, $pass, PDO::PARAM_STR);
		$stmt->bindValue(2, $id, PDO::PARAM_STR);
		$stmt->execute();
		echo "<P>パスワードを更新しました。</P>";
		echo "<P>ログイン画面から再度ログインしてください。</P>";
		echo "<a href='index.php'> ログイン画面へ</a>";
		exit();
	}
   
  //ログイン失敗
  //リダイレクト
  header("HTTP/1.1 301 Moved Permanently");
  header("Location: index.php");
  exit();
?>