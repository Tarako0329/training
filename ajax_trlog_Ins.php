<?php
require_once "config.php";
use classes\SpreadSheet\SpreadSheet;
use classes\Security\Security;
define("GOOGLE_AUTH",$_ENV["GOOGLE_AUTH"]);
define("GOOGLE_AUTH_SKEY",$_ENV["GOOGLE_AUTH_SKEY"]);

//トランザクション処理
log_writer2("\$POST",$_POST,"lv3");
$now = date("Y-m-d H:i:s");

//結果書き込み
if(isset($_SESSION['USER_ID'])){
	$id = $_SESSION['USER_ID'];
}else if (check_auto_login($_COOKIE['token'])==0) {
	$id = $_SESSION['USER_ID'];
}else{
	$return_sts = array(
		"MSG" => "UserIDが取得できませんでした"
		,"status" => "error"
	);
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
	exit();
}

//種目追加欄が空白の場合はリストの種目,種目追加欄が記入されてる場合は種目追加欄の種目
$shu = $_POST["shu1"] ?? "";
$rep2 = ($_POST["rep2"] == "")? 0:$_POST["rep2"];
$cal = ($_POST["cal"] == "")?0:$_POST["cal"];
$type = (U::exist($_POST["jiju"] ?? null) ? "2" : $_POST["typ"]);
$sheetname = ($type === "1") ? "有酸素運動" : "ウェイトトレーニング";

try{
	$db->begin_tran();

	//リフレッシュトークンの取得
	$row = $db->SELECT("SELECT * FROM users WHERE id = :id",["id"=>$_SESSION['USER_ID']]);
	$SQ = new Security($_SESSION['USER_ID'],key);
	$refreshToken = $SQ->decrypt($row[0]['google_refresh_token']);
	$db_spsfilename = $row[0]['spsfilename'] ?? "";

	$spread_flg = U::exist($refreshToken) && U::exist($db_spsfilename);

	if(!U::exist($_POST["NO"])){//新規登録
		$sql = "SELECT max(jun) as junban from tr_log where ymd = :ymd and id = :id;";
		$row = $db->SELECT($sql,[":ymd" => $_POST["ymd"],":id" => $id]);
		$jun = !U::exist($row[0]["junban"])?1:$row[0]["junban"]+1;
		$db->INSERT("tr_log",[
			"id" => $id
			,"shu" => $shu
			,"jun" => $jun
			,"weight" => $_POST["weight"]
			,"rep" => $_POST["rep"]
			,"tani" => $_POST["tani"]
			,"rep2" => $rep2
			,"sets" => $_POST["sets"]
			,"cal" => $cal
			,"ymd" => $_POST["ymd"]
			,"memo" => $_POST["memo"]
			,"typ" => $type
			,"insdatetime" => $now]
		);
	}else{//更新
		//更新対象の日付を取得
		$sql = "SELECT ymd as motoYMD,jun from tr_log where id = :id and SEQ = :SEQ;";
		$row = $db->SELECT($sql,[":id" => $id,":SEQ" => $_POST["SEQ"]]);

		//if($_POST["motoYMD"] == $_POST["ymd"]){//日付の変更がない場合は元の順番で更新
		if($row[0]["motoYMD"] === $_POST["ymd"]){//日付の変更がない場合は元の順番で更新
			$jun=$_POST["NO"];
		}else{
			//日付の変更がある場合は変更後の日付の順番の最後に更新
			$sql = "SELECT max(jun) as junban from tr_log where  ymd = :ymd and id = :id;";
			$row = $db->SELECT($sql,[":ymd" => $_POST["ymd"],":id" => $id]);
			$jun=(count($row)==0)?1:$jun=$row[0]["junban"]+1;
		}
	
		$sql = "UPDATE tr_log set 
			`shu` = :shu,
			`jun` = :jun,
			`weight` = :weight,
			`rep` = :rep1,
			`rep2` = :rep2,
			`sets` = :sets,
			`tani` = :tani,
			`cal` = :cal,
			`ymd` = :ymd,
			`typ` = :typ,
			`memo` = :memo ,
			`insdatetime` = :insdatetime
			where `id` =:id and `SEQ` = :SEQ";
		
		$db->UP_DEL_EXEC($sql,[
			":shu" => $shu
			,":jun" => $jun
			,":weight" => $_POST["weight"]
			,":rep1" => $_POST["rep"]
			,":rep2" => $rep2
			,":sets" => $_POST["sets"]
			,":tani" => $_POST["tani"]
			,":cal" => $cal
			,":ymd" => $_POST["ymd"]
			,":typ" => $type
			,":memo" => $_POST["memo"]
			,":insdatetime" => $now
			,":id" => $id
			,":SEQ" => $_POST["SEQ"]]);
	}

	//スプレッドシートに記録
	if($spread_flg){
		//さっき登録したデータを取得(INSERTの場合はSEQが不明なため)
		if($type === "1"){
			$sql = "SELECT SEQ,ymd,jun,shu,if(typ=2,'自重',weight) as weight,rep,sets,tani,cal,memo FROM tr_log where (id=:id and ymd = :ymd and jun = :jun) OR (id=:id2 and SEQ = :SEQ);";
		}else{
			$sql = "SELECT SEQ,ymd,jun,shu,rep as 時間,rep2 as 距離,sets,cal,memo FROM tr_log where (id=:id and ymd = :ymd and jun = :jun) OR (id=:id2 and SEQ = :SEQ);";
		}
		$row = $db->SELECT($sql,[":id" => $id,":ymd" => $_POST["ymd"],":jun" => $jun,":id2" => $id,":SEQ" => $_POST["SEQ"]]);
		$row = array_map(function($item) {
			return array_values($item);
		}, $row);
		if(!U::exist($_POST["NO"])){//新規登録
			$SpreadSheet = new SpreadSheet($refreshToken, $db_spsfilename);
			$SpreadSheet->G_INSERT($row, $sheetname);
		}else{//更新
			$SpreadSheet = new SpreadSheet($refreshToken, $db_spsfilename);
			$SpreadSheet->G_UPDATE($_POST["SEQ"], $row, $sheetname);
		}
	}

	if(U::exist($_POST["condition"])){//今日のコンディション登録
		//デリイン
		$sql = "DELETE from tr_condition where id = :id and ymd = :ymd";
		$db->UP_DEL_EXEC($sql,[":id" => $id,":ymd" => $_POST["ymd"]]);

		$db->INSERT("tr_condition",["id" => $id,"ymd"=>$_POST["ymd"],"condition"=>$_POST["condition"]]);
	}


	//種目マスタ追加
	$sql = "SELECT shu,count(*) as cnt from ms_training where id = :id and shu = :shu";
	$row = $db->SELECT($sql,[":id" => $id,":shu" => $shu]);

	if($row[0]["shu"]==$shu){
		//skip
	}else{
		$sql = "SELECT max(sort)+1 as next from ms_training where id = :id and sort < 100 group by id;";
		$row = $db->SELECT($sql,[":id" => $id]);
		$row_cnt = count($row);
	
		if($row_cnt==0){
			$next = 1;
		}else{
			$next = $row[0]["next"];
		}
		$db->INSERT("ms_training",["id" => $id,"shu"=>$shu,"sort"=>$next]);
	}

	$db->commit_tran();
	
	$return_sts = array(
		"MSG" => "success"
		,"status" => "success"
		,"filter" => $shu
	);
}catch(Exception $e){
	$msg = "catch Exception \$e：".$e;	
	$db->rollback_tran($msg);
	log_writer2("\$e",$e,"lv1");
	$return_sts = array(
		"MSG" => $msg
		,"status" => "error"
		//,"filter" => $shu
	);
}
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
exit();
?>