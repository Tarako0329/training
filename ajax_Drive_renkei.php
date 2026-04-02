<?php
	//GoogleAuthで登録した場合
	require_once "config.php";
	use classes\Security\Security;

	define("GOOGLE_AUTH",$_ENV["GOOGLE_AUTH"]);
	define("GOOGLE_AUTH_SKEY",$_ENV["GOOGLE_AUTH_SKEY"]);
	//GoogleAuth新規ユーザ登録用
	log_writer2("\$POST",$_POST,"lv3");
	
	$msg="不正なアクセスです";
	$status="false";

	// recording_ajax.php の一部
	if (isset($_POST['code'])) {
		try{
			$client = new Google\Client();
			$client->setClientId(GOOGLE_AUTH);
			$client->setClientSecret(GOOGLE_AUTH_SKEY); // .env推奨
			$client->setRedirectUri('postmessage'); // JSからの場合はこれ

			// 認可コードをトークンに交換fetchAccessTokenWithAuthCode
			if(EXEC_MODE!=="Local"){
				$accessToken = $client->fetchAccessTokenWithAuthCode($_POST['code']);
				$client->setAccessToken($accessToken);
				$payload = $client->verifyIdToken($token['id_token']);
				if ($payload) {
					$id = $payload['sub']; // これが「識別子ID」です！
					}
			}else{// これはローカルテスト用のダミーデータです。実際のコードでは、fetchAccessTokenWithAuthCode() を使用して取得します。
				$accessToken = array(
					"access_token" => "ya29.a0Aa7MYipzNvBymolzDieF-nd85Q4lOxxxxxxx"
					,"expires_in" => "3599"
					,"refresh_token" => "1//0eoRStkTdpoRtCgYIARAAGA4SNwF-Lxxxxxx"
					,"scope" => "https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/spreadsheets.readonly https://www.googleapis.com/auth/spreadsheets openid"
					,"token_type" => "Bearer"
					,"id_token" => "eyJhbGciOiJSUzI1NiIsImtpZCI6ImM0MWYxNDFhYTE5ZGYwYWM5N2RhYTU1ZTYwMxxxxxxx"
					,"created" => "1774342xxxx"
				);
				$payload = array(
					"sub" => "xxxxxxxxxxxxxxxx" // これが「識別子ID」です！
					,"email" => "x.x.x@gmail.com"
					,"name" => "xx xx"
				);
				$id = $payload['sub']; // これが「識別子ID」です！
			}
			log_writer2("\$accessToken",$accessToken,"lv3");
			if (U::exist($accessToken['refresh_token']) && U::exist($id)) {
				$SQ = new Security($id,key);
				$refreshToken = $SQ->encrypt($accessToken['refresh_token']);
				//リフレッシュトークンの登録
				$db->begin_tran();
				$db->UP_DEL_EXEC("UPDATE users set google_refresh_token = :google_refresh_token WHERE id = :id",["google_refresh_token"=>$refreshToken,"id"=>$id]);
				$db->commit_tran();

				$msg = "正常終了";
				$status="success";
			}else{
				$msg = "アクセストークンの取得に失敗しました。";
				$status="error";
				U::log("GoogleAuth - アクセストークンの取得に失敗",["accessToken"=>$accessToken,"payload"=>$payload],1);
			}
		}catch(\Throwable $e){
			$db->rollback_tran($e->getMessage());
			U::send_E($e,"Googleリフレッシュトークン登録に失敗", "Googleリフレッシュトークン登録に失敗しました。");
			$msg = "Googleリフレッシュトークン登録に失敗しました。";
			$status="false";	
		}
	}


	$return_sts = array(
		"MSG" => $msg
		,"status" => $status
	);
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
	exit();
?>