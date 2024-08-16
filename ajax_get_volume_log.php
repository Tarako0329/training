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

//履歴取得
$sql = "select ROW_NUMBER() OVER(partition by T.id,T.ymd,T.shu order by T.ymd,T.jun) as No,T.* from (select id,shu,0 as jun,sum(weight*rep*sets) as weight,0 as rep,0 as tani,0 as rep2,0 as sets,0 as cal,ymd,'' as memo,typ,0 as insdatetime ";
$sql .= "from tr_log where id = ? and shu = ? group by ymd,shu UNION ALL select * from  tr_log where id = ? and shu = ?) as T ";
$sql .= "order by T.ymd desc,T.jun ";

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
foreach($dataset_work as $row){
  $weight = " - total：".number_format($row["weight"],0);
	$dataset[$i] = array_merge($row,array('head_wt'=> $weight));
	$i++;
}
//$kintore_log = json_encode($dataset, JSON_UNESCAPED_UNICODE);
$kintore_log = $dataset;
$dataset_work=[];

//ぐらふでーた取得
$sql = "select ymd,DATEDIFF(now(),ymd) as beforedate,ROW_NUMBER() OVER(order by ymd) as No,sum(weight*rep*sets) as weight ";
$sql .= "from tr_log where id = ? and shu = ? group by ymd,shu,id ";
$sql .= "order by ymd";
$graph_title = "『".$shu."のﾄﾚｰﾆﾝｸﾞ量推移』";
$btn_name = "MAX記録グラフへ";
$typ=0;

$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->bindValue(2, $shu, PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);
$dataset = [];
$i=1;
$maxline=0;
$minline=999999;
$graph_data=[];
$graph_data2=[];
foreach($dataset_work as $row){
  //$weight = number_format(max_r($row["weight"], $row["rep"] - $row["rep2"]),2);
	$weight = ($row["weight"]);

	if($_POST["gtype"]==="year"){//直近1年
		if($row["beforedate"]<=365){
			if($maxline<$weight){$maxline=$weight+10;}
			if($minline>$weight){$minline=$weight-10;}
			//$graph_data .= "[".(356-$row["beforedate"]).",".$weight."],";	
			$graph_data[] = [(356-$row["beforedate"]),$weight];	
		}else if($row["beforedate"]<=730){
			if($maxline<$weight){$maxline=$weight+10;}
			if($minline>$weight){$minline=$weight-10;}
			//$graph_data2 .= "[".(730-$row["beforedate"]).",".$weight."],";
			$graph_data2[] = [(730-$row["beforedate"]),$weight];	
		}
	}else if($_POST["gtype"]==="all"){//全期間
		if($maxline<$weight){$maxline=$weight+10;}
		if($minline>$weight){$minline=$weight-10;}
		
		//$graph_data .= "[".$i.",".$weight."],";
		$graph_data[] = [$i,$weight];
	}else{
		exit();
	}
	
	$i++;
}

if($minline<0){$minline=0;}

//ラベル設定
if($_POST["gtype"]==="year"){//直近1年
	$btn_name2="全期間";
	$kikan="all";
	$glabel1="直近1年";
	$glabel2="１年前";
}else if($_POST["gtype"]==="all"){//全期間
	$btn_name2="直近1年";
	$kikan="year";
	$glabel1="全期間";
	$glabel2="";
}

$return_sts = array(
	"MSG" => $msg
	,"status" => $alert_status
	,"kintore_log" => $kintore_log
	,"graph_data1" => $graph_data
	,"graph_data2" => $graph_data2
	,"btn_name2" => $btn_name2
	,"kikan" => $kikan
	,"glabel1" => $glabel1
	,"glabel2" => $glabel2
	,"maxline" => $maxline
	,"minline" => $minline
	,"graph_title" => $graph_title
	,"btn_name" => $btn_name
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>
