<?php
require "config.php";
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

$shu = $_POST["shu"];
$gtype = $_POST["gtype"];
$tani = $_POST["tani"];
$msg = "";
$alert_status = "";


//目標取得
$sql = "SELECT * FROM ms_training WHERE id=:id and shu=:shu";
$result = $pdo_h->prepare( $sql );
$result->bindValue("id", $id, PDO::PARAM_STR);
$result->bindValue("shu", $shu, PDO::PARAM_STR);
$result->execute();
$ms_training = $result->fetchAll(PDO::FETCH_ASSOC);

//最新の体組織取得
$sql = "SELECT ts.* FROM taisosiki ts 
	inner join (
			SELECT id,max(ymd) AS seq FROM taisosiki WHERE id=:id group by id
		) tmp 
	on ts.id=tmp.id 
	and ts.ymd=tmp.seq";
$result = $pdo_h->prepare( $sql );
$result->bindValue("id", $id, PDO::PARAM_STR);
$result->execute();
$taisosiki = $result->fetchAll(PDO::FETCH_ASSOC);

//履歴取得
$sql = "SELECT ROW_NUMBER() OVER(partition by T.id,T.ymd,T.shu ORDER BY T.ymd,T.jun) AS No,T.* FROM (SELECT *,0 AS max_weight FROM tr_log WHERE id = ? and shu = ? 
	UNION ALL SELECT * FROM  tr_log_max_record WHERE id = ? and shu = ?) AS T 
	ORDER BY T.ymd desc,T.jun ";

$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->bindValue(2, $shu, PDO::PARAM_STR);
$result->bindValue(3, $id, PDO::PARAM_STR);
$result->bindValue(4, $shu, PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);
//log_writer2("\$dataset_work",$dataset_work,"lv3");
$dataset = [];
$i=0;
foreach($dataset_work AS $row){
  $weight = " - MAX：".number_format($row["max_weight"],2);
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

if($tani==="month"){
	$sql = "WITH RECURSIVE cal AS (
	  SELECT
	    A.min_ymd AS date
	  FROM
	    (
	      SELECT min(ymd) AS min_ymd FROM tr_log
	      WHERE
	        id = :id1
	        and shu = :shumoku1
					and ymd >= '$date'
	      group by id, shu
	    ) AS A
	  UNION ALL
	  SELECT DATE_ADD(cal.date, INTERVAL 1 Month) FROM cal WHERE cal.date <= CURDATE()
	)
	SELECT
	  left(cal.date, 7) AS ym,
		DATEDIFF(now(),IFNULL(TEMP.max_ymd,cal.date)) AS beforedate,
	  :shumoku2 AS shu,
	  IFNULL(TEMP.m_weight,'NaN') AS max_weight
	FROM
	  cal
	  left join (
	    SELECT
	      shu,
	      left(ymd, 7) AS ym,
				MAX(ymd) AS max_ymd,
	      CONVERT(max(max_weight),char) AS m_weight
	    FROM
	      `tr_log_max_record`
	    WHERE
	      id = :id2
	      and shu = :shumoku3
	    group by
	      shu,
	      left(ymd, 7)
	  ) AS TEMP ON left(cal.date, 7) = TEMP.ym
		ORDER BY left(cal.date,7)";
}else if($tani==="day"){
	$sql = "SELECT
	  TEMP.ym AS ym,
		DATEDIFF(now(),TEMP.ym) AS beforedate,
	  :shumoku2 AS shu,
	  IFNULL(TEMP.m_weight,'NaN') AS max_weight
	FROM
	  (
	    SELECT
	      shu,
	      ymd AS ym,
				MIN(ymd) AS min_ymd,
	      CONVERT(max(max_weight),char) AS m_weight
	    FROM
	      `tr_log_max_record`
	    WHERE
	      id = :id2
	      and shu = :shumoku3
	    group by
	      shu,
	      ymd
	  ) AS TEMP
		ORDER BY TEMP.ym";

}

$graph_title = "『".$shu."のＭＡＸ推移』";

$typ=1;

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

log_writer2("\$dataset_work",$dataset_work,"lv3");

$dataset = [];
$i=1;

$min_val=999999;
$max_val=0;
$graph_data=[];
$graph_data2=[];
$labels = [];
foreach($dataset_work AS $row){
	$weight = ($row["max_weight"]<>"NaN")?number_format($row["max_weight"],2):"NaN";
	if($row["beforedate"]<0){
		continue;
	}
	if($gtype==="hikaku"){//直近1年
		if($row["beforedate"]<=365){
			if($weight<>"NaN"){
				$min_val = ($min_val>$weight)?$weight:$min_val;
				$max_val = ($max_val<$weight)?$weight:$max_val;
			}
			$labels[] = substr($row["ym"],-2);
			$graph_data[] = $weight;
		}else if($row["beforedate"]<=730){
			if($weight<>"NaN"){
				$min_val = ($min_val>$weight)?$weight:$min_val;
				$max_val = ($max_val<$weight)?$weight:$max_val;
			}
			$graph_data2[] = $weight;	
		}
	}else if($gtype==="12M"){//直近１年（月集計の場合はyyyymm、日ごとの場合はN日前）
		if($row["beforedate"]<=365){
			if($weight<>"NaN"){
				$min_val = ($min_val>$weight)?$weight:$min_val;
				$max_val = ($max_val<$weight)?$weight:$max_val;
			}
			$labels[] = ($tani==="month")?substr($row["ym"],-2):$row["beforedate"];
			$graph_data[] = $weight;
		}else{
			//break;
		}
	}else if($gtype==="all"){//全期間
		if($weight<>"NaN"){
			$min_val = ($min_val>$weight)?$weight:$min_val;
			$max_val = ($max_val<$weight)?$weight:$max_val;
		}
		$labels[] = ($tani==="month")?$row["ym"]:$row["beforedate"];
		$graph_data[] = $weight;
	}else{
		exit();
	}
	
	$i++;
}

//ラベル設定
if($gtype==="hikaku"){//直近1年
	$glabel1="今年";
	$glabel2="去年";
	$subtitle="";
}else if($gtype==="all" || $gtype==="12M"){//全期間
	$glabel1="";
	$glabel2="";
	$subtitle="";
}

if($min_val===0 || $min_val <= 20){
	$min_val = 0;
}else{
	$min_val = ($min_val % 20<=10)?$min_val-20 - ($min_val % 20):$min_val - ($min_val % 20);
}

if(empty($taisosiki[0])){
	$taisosiki[0]["weight"] = 0;
}
$return_sts = array(
	"MSG" => $msg
	,"status" => $alert_status
	,"kintore_log" => $kintore_log
	,"graph_data1" => $graph_data
	,"graph_data2" => $graph_data2
	,"labels" => $labels
	,"glabel1" => $glabel1
	,"glabel2" => $glabel2
	,"min_val" => (int)$min_val
	,"max_val" => (int)$max_val
	,"graph_title" => $graph_title
	,"subtitle" => $subtitle
	,"ms_training" => $ms_training[0]
	,"taisosiki" => $taisosiki[0]
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>
