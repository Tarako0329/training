<?php
	//GoogleAuthで登録した場合
  require_once "config.php";
	use classes\SpreadSheet\SpreadSheet;

	define("GOOGLE_AUTH",$_ENV["GOOGLE_AUTH"]);
	define("GOOGLE_AUTH_SKEY",$_ENV["GOOGLE_AUTH_SKEY"]);
  //GoogleAuth新規ユーザ登録用
  log_writer2("\$POST",$_POST,"lv3");
  
	$msg="不正なアクセスです";
	$status="false";

	$mokuhyou = $_POST['mokuhyou'] ?? "";
	// recording_ajax.php の一部
	if (U::exist($_POST['sheetname']) && U::exist($_SESSION['USER_ID'])) {
	  $refreshToken = $accessToken['refresh_token'];
		try{
			//リフレッシュトークンの取得
			$row = $db->SELECT("SELECT * FROM users WHERE id = :id",["id"=>$_SESSION['USER_ID']]);
			$refreshToken = $row[0]['google_refresh_token'];
			//スプレッドシートの作成
			$client = new Google\Client();
			$client->setClientId(GOOGLE_AUTH); // クライアントID
			$client->setClientSecret(GOOGLE_AUTH_SKEY); // クライアントシークレット
			$client->refreshToken($refreshToken);
			// 3. この「準備が整った $client」をクラスに渡す
			$SpreadSheet = new SpreadSheet($client, $_POST['sheetname']);

			if($SpreadSheet->is_new_file){
				$SpreadSheet->createLogSheet("ウェイトトレーニング");
				$SpreadSheet->G_INSERT([['0','目標', $mokuhyou]], "ウェイトトレーニング");
				$SpreadSheet->G_INSERT([['SEQ', '日付','実施順','種目' ,'重量', '回数','セット数' , '推定1RM', 'メモ']], "ウェイトトレーニング");
				$SpreadSheet->createLogSheet("有酸素運動");
				$SpreadSheet->G_INSERT([['SEQ', '日付','実施順','種目' ,'時間', '距離','消費カロリー' , 'メモ']], "有酸素運動");
				$SpreadSheet->createLogSheet("体組織計測");
				$SpreadSheet->G_INSERT([['SEQ', '日付','体重(kg)','体脂肪率(%)','筋肉量(kg)' , '骨量(kg)', '内臓脂肪レベル' , 'メモ']], "体組織計測");
				$SpreadSheet->DELETE_SHEET("シート1");
			}else{//更新
				$SpreadSheet->G_UPDATE("0",[['0','目標', $mokuhyou]],"ウェイトトレーニング");
			}

			//userテーブルの更新
			$db->UP_DEL_EXEC("UPDATE users SET spsfilename = :spsfilename, mokuhyou = :mokuhyou WHERE id = :id",[
				"spsfilename"=>$_POST['sheetname'],
				"mokuhyou"=>$mokuhyou,
				"id"=>$_SESSION['USER_ID']
			]);
			
			$msg = "正常終了";
			$status="success";
		}catch(PDOException $e){
			$db->rollback_tran($e->getMessage());
			log_writer2("$e",$e,"lv0");
		}catch(Exception $e){
			log_writer2("$e",$e,"lv0");
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