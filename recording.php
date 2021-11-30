<?php
  require "config.php";
  require "functions.php";
  //トランザクション処理
  
  if($_POST["btn"] == "ユーザー登録"){
    //新規ユーザ登録画面の「登録」ボタン
	$id = ($_POST["id2"]);
	$sql = "select * from users where ((id)='".$id."')";
	$result = $mysqli->query( $sql );
	$row_cnt = $result->num_rows;
	if($row_cnt==1){
		echo "<P>入力されたIDはすでに使用されています。</P>";
		echo "<P>他のIDで再度登録をお願いします。</P>";
		echo "<P>ヒント：後ろに「01」等をつけるという方法もあります</P>";
		?><a href="index.php"> 戻る</a><?php
		exit();
	}
	$pass = passEx($_POST["pass2"],$id);
	$sql = "insert into users values ('".$id."','".$pass."','".rot13encrypt($fname)."','".$sex."',".$_POST['height'].");";
	$stmt = $mysqli->query("LOCK TABLES users WRITE");
	$stmt = $mysqli->prepare($sql);
	$stmt->execute();
	$stmt = $mysqli->query("UNLOCK TABLES");
    $_SESSION['USER_ID'] = $id;
    //リダイレクト
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: TOP.php");
    exit();
  }
  if($_POST["btn"] == "w_rireki"){
    //体組織記録画面の「履歴」ボタン
    //リダイレクト
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: graph02.php");
    exit();
  }
  if($_POST["btn"] == "w_ins_bt"){
    //体組織記録画面の「記録」ボタン
  	$sql = "insert into taisosiki values ('".$_POST["id"]."','".$_POST["ymd"]."','".$_POST["weight"]."','".$_POST["sibo"]."','".$_POST["yobi1"]."','".$_POST["yobi2"]."','','','".$_POST["memo"]."');";
	$stmt = $mysqli->query("LOCK TABLES taisosiki WRITE");
	$stmt = $mysqli->prepare($sql);
	$stmt->execute();
	$stmt = $mysqli->query("UNLOCK TABLES");
    //リダイレクト
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: graph02.php");
    exit();
  } 
    //ログイン失敗
    //リダイレクト
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: index.php");

?>