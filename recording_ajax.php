<?php
	//GoogleAuthで登録した場合
  require_once "config.php";
	define("GOOGLE_AUTH",$_ENV["GOOGLE_AUTH"]);
	define("GOOGLE_AUTH_SKEY",$_ENV["GOOGLE_AUTH_SKEY"]);
  //GoogleAuth新規ユーザ登録用
  log_writer2("\$POST",$_POST,"lv3");
  
	$msg="不正なアクセスです";
	$status="false";
	$id = $_POST["ID"] ?? -1;
	$name = $_POST['name'] ?? "";
	
	if($_POST["token"] === $_SESSION["token"] && $id!==-1){
		$row = $db->SELECT("SELECT * from users where id = :id",[":id" => $id]);
		$row_cnt = count($row);
		try{
			if($row_cnt===1){
				$msg = "登録済ユーザ";
			}else{
				$msg = "新規ユーザ";
				$pass = $id;	//googleログインはパスワードにGoogle識別子IDをセットする
				log_writer2("\$pass",$pass,"lv3");
				$db->begin_tran();
				$db->INSERT("users",["id" => $id,"pass" => $pass,"name" => $name,"user_type" => "google"]);
				$db->commit_tran();
			}

			//トークンの作成
			$token = get_token();
			//トークンの登録
			register_token($id, $token);
			//自動ログインのトークンを4週間の有効期限でCookieにセット
			setCookie("token", $token, time()+60*60*24*28, "/", "",true,true);
			$_SESSION['USER_ID'] = $id;
			$_SESSION['login_type'] = "google";
			$msg .= "【処理完了】";
			$status="success";
		}catch(\Throwable $e){
			$db->rollback_tran("Google登録でユーザ登録に失敗 \$e: ".$e->getMessage());
			U::send_E($e,"Google登録でユーザ登録に失敗", "Google登録でユーザ登録に失敗しました。");
			$msg = "ユーザ登録に失敗しました。再度お試しください。";
			$status="false";
		}
  }

	$return_sts = array(
		"MSG" => $msg
		,"status" => $status
		,"user_sub" => $id
	);
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
  exit();
?>