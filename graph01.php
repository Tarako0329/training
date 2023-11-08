<?php
// 設定ファイルインクルード
require "config.php";

$now = date('Y-m-d');
//トレーニング種別
$shu = ($_POST["shu"]);
//グラフ種類（MAX0 or トレーニング量1)
$hyoji = ($_POST["hyoji"]);

if(isset($_SESSION['USER_ID'])){ //ユーザーチェックブロック
	$id = $_SESSION['USER_ID'];
	//echo "session:".$id;
}else if (check_auto_login($_COOKIE['token'])==0) {
	$id = $_SESSION['USER_ID'];
	//echo "クッキー:".$id;
}else{
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: index.php");
}	

//ユーザー確認
unset($sql);
$sql = "select * from users where ((id)='".$id."')";
$result = $mysqli->query( $sql );
$row_cnt = $result->num_rows;
$row = $result->fetch_assoc(); 
if($row_cnt==0){
	echo "<P>ＩＤ 又はパスワードが間違っています。</P>".$id.$pass;
	?><a href="index.php"> 戻る</a><?php
	exit();
}
$user_name = rot13decrypt($row["name"]);

//履歴取得
if($hyoji == "0"){//MAX表示:最も重い重量で最も回数をこなしたセットを抽出
//	$sql2 = "select * from tr_log where id = '".$id."' and shu = '".$shu."' and ymd = '".$row["ymd"]."' order by CAST(weight as SIGNED) desc,CAST(rep as SIGNED) desc ";
	$sql = "select ROW_NUMBER() OVER(partition by T.id,T.ymd,T.shu order by T.ymd,T.jun) as No,T.* from (select * from tr_log where id = ? and shu = ? ";
	$sql .= "UNION ALL select * from  tr_log_max_record where id = ? and shu = ?) as T ";
	$sql .= "order by T.ymd desc,T.jun ";
}else{//total表示
	$sql = "select ROW_NUMBER() OVER(partition by T.id,T.ymd,T.shu order by T.ymd,T.jun) as No,T.* from (select id,shu,0 as jun,sum(weight*rep*sets) as weight,0 as rep,0 as tani,0 as rep2,0 as sets,0 as cal,ymd,'' as memo,typ,0 as insdatetime ";
	$sql .= "from tr_log where id = ? and shu = ? group by ymd,shu UNION ALL select * from  tr_log where id = ? and shu = ?) as T ";
	$sql .= "order by T.ymd desc,T.jun ";
}

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
	if($hyoji == "0"){//MAX表示
		$weight = " - MAX：".number_format(max_r($row["weight"], $row["rep"] - $row["rep2"]),2);
	}else{//total表示
		$weight = " - total：".number_format($row["weight"],0);
	}
	$dataset[$i] = array_merge($row,array('head_wt'=> $weight));
	$i++;
}
$kintore_log = json_encode($dataset, JSON_UNESCAPED_UNICODE);
$dataset_work=[];

//ぐらふでーた取得
if($hyoji == "0"){//MAX表示:最も重い重量で最も回数をこなしたセットを抽出
	$sql = "select ROW_NUMBER() OVER(order by ymd) as No,weight,rep,rep2 from  tr_log_max_record where id = ? and shu = ? ";
	$sql .= "order by ymd";
	$graph_title = "『".$shu."のＭＡＸ推移』";
	$btn_name = "トレーニング量グラフへ";
	$typ=1;
}else{//total表示
	$sql = "select ROW_NUMBER() OVER(order by ymd) as No,sum(weight*rep*sets) as weight ";
	$sql .= "from tr_log where id = ? and shu = ? group by ymd,shu,id ";
	$sql .= "order by ymd";
	$graph_title = "『".$shu."トレーニング量推移』";
	$btn_name = "MAX記録グラフへ";
	$typ=0;
}

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
foreach($dataset_work as $row){
	if($hyoji == "0"){//MAX表示
		$weight = number_format(max_r($row["weight"], $row["rep"] - $row["rep2"]),2);
	}else{
		$weight = ($row["weight"]);
	}
	if($maxline<$weight){$maxline=$weight;}
	if($minline>$weight){$minline=$weight;}
	//$dataset[$i] = array('No' => $row["No"],'weight'=> $weight);
	$graph_data .= "[".$i.",".$weight."],";
	$i++;
}
//$graph_data = json_encode($dataset, JSON_UNESCAPED_UNICODE);
//var_dump($shu);
//exit();
?>
<HTML>
<HEAD>
	<?php
		require "header.php";
	?>
	<TITLE>肉体改造ネットワーク</TITLE>
</HEAD>
<BODY class = "graphe">
	<div id="headerArea2">
		<p class="graph-title"><?php echo $graph_title ?></p>
		<div id="graph"></div>
			<FORM method="post" action="graph01.php" style='text-align: center;'>
				<button type="submit"> <?php echo $btn_name;?> </button>
				<INPUT type="hidden" name="hyoji" value=<?php echo $typ;?>>
				<INPUT type="hidden" name="id" value="<?php echo $id;?>">
				<INPUT type="hidden" name="shu" value="<?php echo $shu;?>">
			</FORM>
	</div>
	<main class='container-fluid' id='app'>
		<template v-for='(list,index) in kintore_log' :key='list.ymd+list.jun'>
			<!--<div v-if='index==0 || (index!==0 && list.ymd !== kintore_log[index-1].ymd)' class='row ymd'>{{list.ymd}}</div>-->
			<div class='accordion-item'>
				<div v-if='list.jun==="0"' class='row shu accordion-header'>
					<button type='button' class='accordion-button collapsed' data-bs-toggle='collapse' :data-bs-target='`#collapseOne${list.ymd}${list.shu}`' 
					aria-expanded='false' aria-controls='collapseOne' >
						{{list.ymd}} {{(list.head_wt)}} kg
					</button>
				</div>
				<div v-if='list.jun!=="0"'  :id='`collapseOne${list.ymd}${list.shu}`' class='accordion-collapse collapse' data-bs-parent='#accordionExample'>
					<div class='row lst accordion-body'>
						<div class='col-1' style='padding:0 0 0 6px;'>
							{{list.No - 1}}
						</div>
						<div class='col-2 text-end' style='padding:0;'>{{list.weight}}kg</div>
						<div class='col-2' style='padding-right:0;'>{{list.rep}}({{list.rep2}})回</div>
						<div class='col-2' style='padding-right:0;'>{{list.sets}}sets</div>
						<div class='col-5' style='padding:0;'>{{list.memo}}</div>
					</div>
				</div>
			</div>
		</template>
	</main>
	<div id="footerArea2" style='text-align: center;'>
		<button style ='margin-top: 1em;'><a href=<?php echo "'TOP.php?id=".$id."&pass=".$pass."'" ?> style = 'text-decoration: none;'>戻る</a></button>
	</div>
	<script type="text/javascript">
		(function basic(container) {
		  var d1 = [<?php echo $graph_data;?>
		  ],
		  data = [{
		      data: d1,
		      label: "2017年"
		  }];
		  function labelFn(label) {
		      return label;
		  }
		  graph = Flotr.draw(container, data, {
				yaxis:{
					min:<?php echo $minline; ?>,        //y軸の最小値を設定
					max:<?php echo $maxline; ?>,        //y軸の最大値を設定
					title:'(kg)'
				}, //y軸にタイトルを表示
		  	legend: {
		      position: 'se',
		      labelFormatter: labelFn,
		      backgroundColor: "#D2E8FF"
		  	},
		    HtmlText: false
		  });
		})(document.getElementById("graph"));
	</script>
	<script>//Vus.js
		const { createApp, ref, onMounted, computed, VueCookies,watch } = Vue;
		createApp({
			setup(){
				const kintore_log = ref(<?php echo $kintore_log;?>)
				onMounted(() => {
					console_log('onMounted')
				})
				return{
					kintore_log,
				}
			}
		}).mount('#app');
	</script>

</BODY>
</HTML>
