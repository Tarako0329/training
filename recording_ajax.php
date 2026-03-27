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

	// recording_ajax.php の一部
	/*if (isset($_POST['code'])) {
	  $client = new Google\Client();
	  $client->setClientId(GOOGLE_AUTH);
	  $client->setClientSecret(GOOGLE_AUTH_SKEY); // .env推奨
		$client->setRedirectUri('postmessage'); // JSからの場合はこれ

	  // 認可コードをトークンに交換fetchAccessTokenWithAuthCode
		if(EXEC_MODE!=="local"){
			$accessToken = $client->fetchAccessTokenWithAuthCode($_POST['code']);
		}else{
			$accessToken = array(
				// これはローカルテスト用のダミーデータです。実際のコードでは、fetchAccessTokenWithAuthCode() を使用して取得します。
    		"access_token" => "ya29.a0Aa7MYipzNvBymolzDieF-nd85Q4lOxxxxxxx"
    		,"expires_in" => "3599"
    		,"refresh_token" => "1//0eoRStkTdpoRtCgYIARAAGA4SNwF-Lxxxxxx"
    		,"scope" => "https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/spreadsheets.readonly https://www.googleapis.com/auth/spreadsheets openid"
    		,"token_type" => "Bearer"
    		,"id_token" => "eyJhbGciOiJSUzI1NiIsImtpZCI6ImM0MWYxNDFhYTE5ZGYwYWM5N2RhYTU1ZTYwMxxxxxxx"
    		,"created" => "1774342xxxx"
			);
		}
		log_writer2("\$accessToken",$accessToken,"lv3");
	  // この中に access_token が含まれる
	  // この中に refresh_token が含まれる
	  if (isset($accessToken['refresh_token'])) {
	    $refreshToken = $accessToken['refresh_token'];

	    // 【重要】$refreshToken を MySQL の users テーブルに保存
	    // $db->query("UPDATE users SET google_refresh_token = ? WHERE id = ?", [$refreshToken, $userId]);
	  }
		$client->setAccessToken($accessToken);
	
		// IDトークン（認証情報）からユーザー情報を取得
		if(EXEC_MODE!=="local"){
			$payload = $client->verifyIdToken($token['id_token']);
		}else{
			$payload = array(
				"sub" => "xxxxxxxxxxxxxxxx" // これが「識別子ID」です！
    		,"email" => "x.x.x@gmail.com"
    		,"name" => "xx xx"
			);
		}
		log_writer2("\$payload",$payload,"lv3");
		if ($payload) {
			$id = $payload['sub']; // これが「識別子ID」です！
			$email = $payload['email'];
			$name = $payload['name'];
		}
	}*/

	if($_POST["token"] === $_SESSION["token"] && $id!==-1){
		$row = $db->SELECT("SELECT * from users where id = :id",[":id" => $id]);
		$row_cnt = count($row);
		if($row_cnt===1){
			$msg = "登録済ユーザ";
		}else{
			$msg = "新規ユーザ";
			//$pass = passEx($id,$id);
			$pass = $id;	//googleログインはパスワードにGoogle識別子IDをセットする
			log_writer2("\$pass",$pass,"lv3");
			$db->begin_tran();
			$db->INSERT("users",["id" => $id,"pass" => $pass,"name" => $name,"user_type" => "google"]);
			$db->commit_tran();
		}
		$_SESSION['USER_ID'] = $id;
		$_SESSION['login_type'] = "google";
		$msg .= "【処理完了】";
		$status="success";
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