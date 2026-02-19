<?php
require_once "config.php";
require_once "database.php";
$db = new Database();

log_writer2("\$_POST",$_POST,"lv3");
if(isset($_SESSION['USER_ID'])){ //ユーザーチェックブロック
	$id = $_SESSION['USER_ID'];
}else if (check_auto_login($_COOKIE['token'])==0) {
	$id = $_SESSION['USER_ID'];
}else{
	$return_sts = array(
		"MSG" => "失敗"
	);
	header('Content-type: application/json');
	echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
	exit();
}	

$gtype = $_POST["gtype"];
$shu = $_POST["shu"];
$tani = $_POST["tani"];
$msg = "";
$alert_status = "";
$graph_title = "のﾄﾚｰﾆﾝｸﾞ量推移";


//履歴取得
$sql = "SELECT 
	ROW_NUMBER() OVER(partition by T.id,T.ymd,T.shu order by T.ymd,T.jun) as No,T.* 
	FROM (
		SELECT 0 as SEQ,id,shu,0 as jun,sum(weight*rep*sets) as weight,0 as rep,0 as tani,0 as rep2,0 as sets,0 as cal,ymd,'' as memo,typ,0 as insdatetime FROM tr_log where id = :id1 and shu = :shu1 group by ymd,shu 
		UNION ALL 
		SELECT * FROM  tr_log where id = :id2 and shu = :shu2) as T 
	order by T.ymd desc,T.jun ";

/*
$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->bindValue(2, $shu, PDO::PARAM_STR);
$result->bindValue(3, $id, PDO::PARAM_STR);
$result->bindValue(4, $shu, PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);
*/
$dataset_work = $db->SELECT($sql,[":id1" => $id,":shu1" => $shu,":id2" => $id,":shu2" => $shu]);
//log_writer2("\$dataset_work",$dataset_work,"lv3");
$dataset = [];
$i=0;
foreach($dataset_work as $row){
  $weight = " - total：".number_format($row["weight"],0);
	$dataset[$i] = array_merge($row,array('head_wt'=> $weight));
	$i++;
}

$kintore_log = $dataset;
$dataset_work=[];

//ぐらふでーた取得
if($gtype==="hikaku" || $gtype==="12M"){//前年比or直近1年
	$timestamp = strtotime('-23 months first day of this month');
	// タイムスタンプを日付形式に変換
	$date = date('Y-m-d', $timestamp);
}else if($gtype==="all"){//全期間
	$date = '2017-05-01';
}else{
	exit();
}
//
if($tani==="month"){
	$sql = "WITH RECURSIVE cal AS (
			SELECT
				DATE_FORMAT(A.min_ymd, '%Y-%m-01') AS date
			FROM
				(
					SELECT min(ymd) as min_ymd FROM tr_log
					where
						id = :id1
						and shu = :shumoku1
						and ymd >= :date1
					group by id, shu
				) as A
			UNION ALL
			SELECT DATE_ADD(cal.date, INTERVAL 1 Month) AS date FROM cal WHERE cal.date <= DATE_ADD(CURDATE(), INTERVAL 1 DAY)
		)
		SELECT
			left(cal.date, 7) as ym
			,DATEDIFF(now(),cal.date) as beforedate
			,:shumoku2 as shu
			,IFNULL(TEMP.max_volume,0) as max_volume
			,IFNULL(TEMP.total_volume,0) as total_volume
		FROM
		cal
		left join (
			SELECT shu, left(ymd, 7) as ym, max(total_weight) as max_volume, sum(total_weight) as total_volume FROM 
			(SELECT shu, left(ymd, 7) as ym, ymd, sum(weight*rep*sets) as total_weight FROM `tr_log` where id = :id2 and shu = :shumoku3 group by shu, ymd) as tmp
 			group by shu, left(ymd, 7)
 		) as TEMP ON left(cal.date, 7) = TEMP.ym
		ORDER BY left(cal.date,7)";
	$dataset_work = $db->SELECT($sql,[":id1" => $id,":shumoku1" => $shu,":date1" => $date,":id2" => $id,":shumoku2" => $shu,":shumoku3" => $shu]);
}else if($tani==="day"){
	$sql="SELECT
			TEMP.ymd as ym
			,DATEDIFF(now(),TEMP.ymd) as beforedate
			,:shumoku2 as shu
			,IFNULL(TEMP.max_volume,0) as max_volume
			,IFNULL(TEMP.total_volume,0) as total_volume
		FROM
			(
				SELECT shu, ymd, max(total_weight) as max_volume, sum(total_weight) as total_volume FROM 
					(SELECT shu, ymd, sum(weight*rep*sets) as total_weight FROM `tr_log` where id = :id2 and shu = :shumoku3 and ymd >= :date1 group by shu, ymd) as tmp
			 	group by shu, ymd
		 	) as TEMP
		ORDER BY TEMP.ymd";
	$dataset_work = $db->SELECT($sql,[":id2" => $id,":shumoku2" => $shu,":shumoku3" => $shu,":date1" => $date]);
}
/*
$result = $pdo_h->prepare( $sql );
if($tani==="month"){
	$result->bindValue('id1', $id, PDO::PARAM_STR);
	$result->bindValue('shumoku1', $shu, PDO::PARAM_STR);
}
$result->bindValue('id2', $id, PDO::PARAM_STR);
$result->bindValue('shumoku2', $shu, PDO::PARAM_STR);
$result->bindValue('shumoku3', $shu, PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);
*/

$dataset = [];
//$i=1;
$graph_data_max=[];
$graph_data_max2=[];
$graph_data_total=[];
$graph_data_total2=[];
$labels=[];
$min_val = 100000;

foreach($dataset_work as $row){
	$weight = ($row["total_volume"]);
	if($row["beforedate"]<0){
		continue;
	}

	if($gtype==="hikaku"){//前年比（月集計のみ）
		if($row["beforedate"]<=365){
			$min_val = ($min_val>$row["max_volume"])?$row["max_volume"]:$min_val;
			$labels[] = substr($row["ym"],-2);
			$graph_data_max[] = $row["max_volume"];
			$graph_data_total[] = $row["total_volume"];
		}else if($row["beforedate"]<=730){
			$min_val = ($min_val>$row["max_volume"])?$row["max_volume"]:$min_val;
			$graph_data_max2[] = $row["max_volume"];
			$graph_data_total2[] = $row["total_volume"];
		}else{
			break;
		}
	}else if($gtype==="12M"){//直近１年（月集計の場合はyyyymm、日ごとの場合はN日前）
		if($row["beforedate"]<=365){
			$min_val = ($min_val>$row["max_volume"])?$row["max_volume"]:$min_val;
			$labels[] = ($tani==="month")?substr($row["ym"],-2):$row["beforedate"];
			$graph_data_max[] = $row["max_volume"];
			$graph_data_total[] = $row["total_volume"];
		}else{
			//break;
		}
	}else if($gtype==="all"){//全期間（月集計の場合はyyyymm、日ごとの場合はN日前）
		$min_val = ($min_val>$row["max_volume"])?$row["max_volume"]:$min_val;
		$labels[] = ($tani==="month")?$row["ym"]:$row["beforedate"];
		$graph_data_max[] = $row["max_volume"];
		$graph_data_total[] = $row["total_volume"];
	}else{
		exit();
	}
	
	//$i++;
}


//ラベル設定
if($gtype==="hikaku"){//前年比
	$glabel1="今年";
	$glabel2="前年";
	$subtitle = "各月の総Volと最大Vol";
}else if($gtype==="all"){//全期間
	$glabel1="1日最大Vol";
	$glabel2=($tani==="month")?"月間総Vol":"1日Vol";
	$subtitle = "全期間対象のVolume推移";
}else if($gtype==="12M"){//直近１年
	$glabel1="1日最大Vol";
	$glabel2=($tani==="month")?"月間総Vol":"1日Vol";
	$subtitle = "直近１年のVolume推移";
}

if($min_val===0 || $min_val <= 500){
	$min_val = 0;
}else{
	$min_val = ($min_val % 500<=200)?$min_val-500 - ($min_val % 500):$min_val - ($min_val % 500);
}

$return_sts = array(
	"MSG" => $msg
	,"status" => $alert_status
	,"kintore_log" => $kintore_log
	,"graph_data_max1" => $graph_data_max
	,"graph_data_total1" => $graph_data_total
	,"graph_data_max2" => $graph_data_max2
	,"graph_data_total2" => $graph_data_total2
	,"labels" => $labels
	,"glabel1" => $glabel1
	,"glabel2" => $glabel2
	,"graph_title" => $graph_title
	,"subtitle" => $subtitle
	,"min_val" => (int)$min_val
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>
