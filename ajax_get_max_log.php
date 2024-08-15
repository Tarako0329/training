<?php
require "config.php";
$shu = ($_POST["shu"]);
if(isset($_SESSION['USER_ID'])){ //ユーザーチェックブロック
	$id = $_SESSION['USER_ID'];
}else if (check_auto_login($_COOKIE['token'])==0) {
	$id = $_SESSION['USER_ID'];
}else{
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: index.php");
	exit();
}	

//履歴取得
$sql = "select ROW_NUMBER() OVER(partition by T.id,T.ymd,T.shu order by T.ymd,T.jun) as No,T.* from (select *,0 as max_weight from tr_log where id = ? and shu = ? ";
$sql .= "UNION ALL select * from  tr_log_max_record where id = ? and shu = ?) as T ";
$sql .= "order by T.ymd desc,T.jun ";

$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->bindValue(2, $shu, PDO::PARAM_STR);
$result->bindValue(3, $id, PDO::PARAM_STR);
$result->bindValue(4, $shu, PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);
$dataset = [];
$i=0;
foreach($dataset_work as $row){
  $weight = " - MAX：".number_format($row["max_weight"],2);
	$dataset[$i] = array_merge($row,array('head_wt'=> $weight));
	$i++;
}
$kintore_log = json_encode($dataset, JSON_UNESCAPED_UNICODE);
$dataset_work=[];

//ぐらふでーた取得
$sql = "select ymd,DATEDIFF(now(),ymd) as beforedate,ROW_NUMBER() OVER(order by ymd) as No,weight,rep,rep2 from tr_log_max_record where id = ? and shu = ? ";
$sql .= "order by ymd";
$graph_title = "『".$shu."のＭＡＸ推移』";
$btn_name = "ﾄﾚｰﾆﾝｸﾞ量グラフへ";
$typ=1;

$result = $pdo_h->prepare( $sql );
$result->bindValue(1, $id, PDO::PARAM_STR);
$result->bindValue(2, $shu, PDO::PARAM_STR);
$result->execute();
$dataset_work = $result->fetchAll(PDO::FETCH_ASSOC);
$dataset = [];
$i=1;
$maxline=0;
$minline=999999;
$graph_data="";
$graph_data2="";
foreach($dataset_work as $row){
  //$weight = number_format(max_r($row["weight"], $row["rep"] - $row["rep2"]),2);
	$weight = " - MAX：".number_format($row["max_weight"],2);

	if($_POST["gtype"]==="year"){//直近1年
		if($row["beforedate"]<=365){
			if($maxline<$weight){$maxline=$weight+10;}
			if($minline>$weight){$minline=$weight-10;}
			$graph_data .= "[".(356-$row["beforedate"]).",".$weight."],";	
		}else if($row["beforedate"]<=730){
			if($maxline<$weight){$maxline=$weight+10;}
			if($minline>$weight){$minline=$weight-10;}
			$graph_data2 .= "[".(730-$row["beforedate"]).",".$weight."],";	
		}
	}else if($_POST["gtype"]==="all"){//全期間
		if($maxline<$weight){$maxline=$weight+10;}
		if($minline>$weight){$minline=$weight-10;}
		
		$graph_data .= "[".$i.",".$weight."],";
	}else{
		exit();
	}
	
	$i++;
}

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
	,"graph_data" => $graph_data
	,"graph_data2" => $graph_data2
	,"btn_name2" => $btn_name2
	,"kikan" => $kikan
	,"glabel1" => $glabel1
	,"glabel2" => $glabel2
	,"graph_title" => $graph_title
	,"btn_name" => $btn_name
);
header('Content-type: application/json');
echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);

exit();

?>
