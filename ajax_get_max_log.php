<?php
require "config.php";
log_writer2("\$_POST",$_POST,"lv3");
$shu = ($_POST["shu"]);
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

$gtype = ($_POST["gtype"]==='12M')?'year':$_POST["gtype"];


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
//$kintore_log = json_encode($dataset, JSON_UNESCAPED_UNICODE);
$kintore_log = $dataset;
$dataset_work=[];

//ぐらふでーた取得
//$sql = "SELECT ymd,DATEDIFF(now(),ymd) AS beforedate,ROW_NUMBER() OVER(ORDER BY ymd) AS No,weight,rep,rep2,max_weight FROM tr_log_max_record WHERE id = ? and shu = ? ";
if($gtype==="year"){//直近1年
	$timestamp = strtotime('-23 months first day of this month');
	// タイムスタンプを日付形式に変換
	$date = date('Y-m-d', $timestamp);
}else if($gtype==="all"){//全期間
	$date = '2017-05-01';
}else{
	exit();
}
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
		DATEDIFF(now(),cal.date) AS beforedate,
	  :shumoku2 AS shu,
	  IFNULL(TEMP.m_weight,'NaN') AS max_weight
	FROM
	  cal
	  left join (
	    SELECT
	      shu,
	      left(ymd, 7) AS ym,
				MIN(ymd) AS min_ymd,
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

$graph_title = "『".$shu."のＭＡＸ推移』";
$btn_name = "ﾄﾚｰﾆﾝｸﾞ量グラフへ";
$typ=1;

$result = $pdo_h->prepare( $sql );
$result->bindValue('id1', $id, PDO::PARAM_STR);
$result->bindValue('id2', $id, PDO::PARAM_STR);
$result->bindValue('shumoku1', $shu, PDO::PARAM_STR);
$result->bindValue('shumoku2', $shu, PDO::PARAM_STR);
$result->bindValue('shumoku3', $shu, PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);
$dataset = [];
$i=1;
//$maxline=0;
//$minline=999999;
$graph_data=[];
$graph_data2=[];
$labels = [];
foreach($dataset_work AS $row){
	$weight = ($row["max_weight"]<>"NaN")?number_format($row["max_weight"],2):"NaN";
	if($row["beforedate"]<0){
		continue;
	}
	if($gtype==="year"){//直近1年
		if($row["beforedate"]<=365){
			//if($maxline<$weight){$maxline=$weight+10;}
			//if($minline>$weight){$minline=$weight-10;}
			$graph_data[] = $weight;
			$labels[] = substr($row["ym"],-2);
		}else if($row["beforedate"]<=730){
			//if($maxline<$weight){$maxline=$weight+10;}
			//if($minline>$weight){$minline=$weight-10;}
			$graph_data2[] = $weight;	
		}
	}else if($gtype==="all"){//全期間
		//if($maxline<$weight){$maxline=$weight+10;}
		//if($minline>$weight){$minline=$weight-10;}
		
		$labels[] = $row["ym"];
		$graph_data[] = $weight;
	}else{
		exit();
	}
	
	$i++;
}

//if($minline<0){$minline=0;}

//ラベル設定
if($gtype==="year"){//直近1年
	//$btn_name2="全期間";
	//$kikan="all";
	$glabel1="今年";
	$glabel2="去年";
	$subtitle="";
}else if($gtype==="all"){//全期間
	//$btn_name2="直近1年";
	//$kikan="year";
	$glabel1="全期間";
	$glabel2="";
	$subtitle="";
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
	//,"btn_name2" => $btn_name2
	//,"kikan" => $kikan
	,"glabel1" => $glabel1
	,"glabel2" => $glabel2
	//,"maxline" => $maxline
	//,"minline" => $minline
	,"graph_title" => $graph_title
	,"subtitle" => $subtitle
	//,"btn_name" => $btn_name
	,"ms_training" => $ms_training[0]
	,"taisosiki" => $taisosiki[0]
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>
