<?php
  require "config.php";
  //require "functions.php";
  //トランザクション処理
  
  if($_POST["btn"] == "ユーザー登録"){
    //新規ユーザ登録画面の「登録」ボタン
	$id = ($_POST["id2"]);
	$sql = "select * from users where ((id)=?)";
	$stmt = $pdo_h->prepare( $sql );
	$stmt->bindValue(1, $id, PDO::PARAM_STR);
	$stmt->execute();
	$row_cnt = $stmt->rowCount();
	if($row_cnt==1){
		echo "<P>入力されたIDはすでに使用されています。</P>";
		echo "<P>他のIDで再度登録をお願いします。</P>";
		echo "<P>ヒント：後ろに「01」等をつけるという方法もあります</P>";
		?><a href="index.php"> 戻る</a><?php
		exit();
	}
	$pass = passEx($_POST["pass2"],$id);
	$sql = "insert into users values (?,?,?,?,?);";
	$stmt = $pdo_h->prepare($sql);
	$stmt->bindValue(1, $id, PDO::PARAM_STR);
	$stmt->bindValue(2, $pass, PDO::PARAM_STR);
	$stmt->bindValue(3, rot13encrypt($_POST['fname']), PDO::PARAM_STR);
	$stmt->bindValue(4, $_POST['sex'], PDO::PARAM_STR);
	$stmt->bindValue(5, $_POST['height'], PDO::PARAM_INT);
	$stmt->execute();
    $_SESSION['USER_ID'] = $id;
    //リダイレクト
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: TOP.php");
    exit();
  }
   
  //ログイン失敗
  //リダイレクト
  header("HTTP/1.1 301 Moved Permanently");
  header("Location: index.php");
  exit();
?>