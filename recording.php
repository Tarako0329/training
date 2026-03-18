<?php
	//ID・パスワードで登録した場合
  require_once "config.php";
  //require_once "database.php";
  //$db = new Database();	

  //トランザクション処理
  //log_writer2("\$POST",$_POST,"lv3");
  
	if($_POST["btn"] == "ユーザー登録"){
    //新規ユーザ登録画面の「登録」ボタン
		$id = ($_POST["id2"]) ?? -1;
		$sql = "SELECT * from users where id = :id";
		$row = $db->SELECT($sql,[':id' => $id]);
		$row_cnt = count($row);
		if($row_cnt==1){
			echo "<P>入力されたIDはすでに使用されています。</P>";
			echo "<P>他のIDで再度登録をお願いします。</P>";
			echo "<P>ヒント：後ろに「01」等をつけるという方法もあります</P>";
			?><a href="index.php"> 戻る</a><?php
			exit();
		}
		$pass = passEx($_POST["pass2"],$id);
		$db->begin_tran();
		$db->INSERT("users",["id" => $id,"pass" => $pass,"name" => ($_POST['fname']),"sex" => $_POST['sex'],"height" => $_POST['height'],"birthday" => $_POST['birthday'],"user_type" => "ipass"]);
		$db->commit_tran();

    $_SESSION['USER_ID'] = $id;
    //リダイレクト
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: index.php?logoff=sinkitouroku");
    exit();
  }
   
  //不正アクセス
  //リダイレクト
  header("HTTP/1.1 301 Moved Permanently");
  header("Location: index.php");
  exit();
?>