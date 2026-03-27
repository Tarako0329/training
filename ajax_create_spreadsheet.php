<?php
	//GoogleAuthで登録した場合
	require_once "config.php";
	use classes\SpreadSheet\SpreadSheet;
	use classes\Security\Security;

	define("GOOGLE_AUTH",$_ENV["GOOGLE_AUTH"]);
	define("GOOGLE_AUTH_SKEY",$_ENV["GOOGLE_AUTH_SKEY"]);
	//GoogleAuth新規ユーザ登録用
	log_writer2("\$POST",$_POST,"lv3");
	
	$msg="不正なアクセスです";
	$status="false";

	//リフレッシュトークンの取得
	$row = $db->SELECT("SELECT * FROM users WHERE id = :id",["id"=>$_SESSION['USER_ID']]);
	$SQ = new Security($_SESSION['USER_ID'],key);
	$refreshToken = $SQ->decrypt($row[0]['google_refresh_token']);
	$db_spsfilename = $row[0]['spsfilename'] ?? "";

	$mokuhyou = $_POST['mokuhyou'] ?? "";
	$new_sheetname = $_POST['sheetname'] ?? "";

	$sheetname = U::exist($db_spsfilename)?$db_spsfilename:$new_sheetname;//新規もしくは既存のファイル名をセット

	// recording_ajax.php の一部
	if (U::exist($sheetname) && U::exist($refreshToken) && U::exist($_SESSION['USER_ID'])) {
		
		try{
			//スプレッドシートの作成
			$SpreadSheet = new SpreadSheet($refreshToken, $sheetname);

			if($SpreadSheet->is_new_file){//新規作成の場合
				$SpreadSheet->createLogSheet("ウェイトトレーニング");
				$SpreadSheet->G_INSERT([['0','目標', $mokuhyou]], "ウェイトトレーニング");
				$SpreadSheet->G_INSERT([['SEQ', '日付','実施順','種目' ,'重量', '回数','セット数' , 'メモ']], "ウェイトトレーニング");
				$SpreadSheet->createLogSheet("有酸素運動");
				$SpreadSheet->G_INSERT([['SEQ', '日付','実施順','種目' ,'時間', '距離','消費カロリー' , 'メモ']], "有酸素運動");
				$SpreadSheet->createLogSheet("体組織計測");
				$SpreadSheet->G_INSERT([['SEQ', '日付','体重(kg)','体脂肪率(%)','筋肉量(kg)' , '骨量(kg)', '内臓脂肪レベル' , 'メモ']], "体組織計測");
				$SpreadSheet->DELETE_SHEET("シート1");

				$row = array_map(function($item) {
					return array_values($item);
				}, $db->SELECT("SELECT SEQ,ymd,jun,shu,if(typ=2,'自重',weight),rep,sets,memo FROM `tr_log` where id=:id and ymd > '2024-01-01' order by SEQ;",["id"=>$_SESSION['USER_ID']]));
				
				$SpreadSheet->G_INSERT($row,"ウェイトトレーニング");
			}else{//更新
				$SpreadSheet->G_UPDATE("0",[['0','目標', $mokuhyou]],"ウェイトトレーニング");
				//ファイル名更新
				if($new_sheetname !== $sheetname && U::exist($db_spsfilename)){
					$rename_result = $SpreadSheet->RENAME_FILE($new_sheetname, $sheetname);
					if($rename_result === "warning"){
						log_writer2("ファイル名の不一致",$sheetname."!=".$db_spsfilename,"lv1");
						$msg = "ファイル名の不一致のため、ファイル名は更新されませんでした。";
						$status="warning";
					}else if($rename_result === "error"){
						log_writer2("ファイル名の更新に失敗",$sheetname,"lv0");
						$msg = "ファイル名の更新に失敗しました。";
						$status="error";
					}
				}
			}

			//userテーブルの更新
			$db->begin_tran();
			$db->UP_DEL_EXEC("UPDATE users SET spsfilename = :spsfilename, mokuhyou = :mokuhyou WHERE id = :id",[
				"spsfilename"=>$sheetname,
				"mokuhyou"=>$mokuhyou,
				"id"=>$_SESSION['USER_ID']
			]);
			$db->commit_tran();

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