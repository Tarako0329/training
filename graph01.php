<?php
// 設定ファイルインクルード
require "config.php";
log_writer2("\$_POST",$_POST,"lv3");
$now = date('Y-m-d');
//トレーニング種別
$shu = ($_POST["shu"]);
//グラフ種類（MAX:0 or トレーニング量:1 or MAX更新時:2)
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
	exit();
}	

//履歴取得
/*
if($hyoji == "0"){//MAX表示:最も重い重量で最も回数をこなしたセットを抽出
	$sql = "select ROW_NUMBER() OVER(partition by T.id,T.ymd,T.shu order by T.ymd,T.jun) as No,T.* from (select *,0 as max_weight from tr_log where id = ? and shu = ? ";
	$sql .= "UNION ALL select * from  tr_log_max_record where id = ? and shu = ?) as T ";
	$sql .= "order by T.ymd desc,T.jun ";
}else if($hyoji == "1"){//total表示
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
	}else if($hyoji == "1"){//total表示
		$weight = " - total：".number_format($row["weight"],0);
	}
	$dataset[$i] = array_merge($row,array('head_wt'=> $weight));
	$i++;
}
$kintore_log = json_encode($dataset, JSON_UNESCAPED_UNICODE);
$dataset_work=[];

//ぐらふでーた取得
if($hyoji == "0"){//MAX表示:最も重い重量で最も回数をこなしたセットを抽出
	$sql = "select ymd,DATEDIFF(now(),ymd) as beforedate,ROW_NUMBER() OVER(order by ymd) as No,weight,rep,rep2 from  tr_log_max_record where id = ? and shu = ? ";
	$sql .= "order by ymd";
	$graph_title = "『".$shu."のＭＡＸ推移』";
	$btn_name = "ﾄﾚｰﾆﾝｸﾞ量グラフへ";
	$typ=1;
}else if($hyoji == "1"){//total表示
	$sql = "select ymd,DATEDIFF(now(),ymd) as beforedate,ROW_NUMBER() OVER(order by ymd) as No,sum(weight*rep*sets) as weight ";
	$sql .= "from tr_log where id = ? and shu = ? group by ymd,shu,id ";
	$sql .= "order by ymd";
	$graph_title = "『".$shu."ﾄﾚｰﾆﾝｸﾞ量推移』";
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
$graph_data2="";
foreach($dataset_work as $row){
	if($hyoji == "0"){//MAX表示
		$weight = number_format(max_r($row["weight"], $row["rep"] - $row["rep2"]),2);
	}else if($hyoji == "1"){
		$weight = ($row["weight"]);
	}

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
*/
//$graph_data = json_encode($dataset, JSON_UNESCAPED_UNICODE);
//var_dump($shu);
//exit();
?>
<!DOCTYPE html>
<HTML>
<HEAD>
	<?php
		require "header.php";
	?>
	<TITLE>肉体改造ネットワーク</TITLE>
</HEAD>
<BODY class = "graphe">
	<div id='app'>
	<div id="headerArea2">
		<p class="graph-title">{{graph_title}}</p>
		<div id="graph" style='margin-bottom:5px;'></div>
		<div class='d-flex align-items-center justify-content-center' style='width: 100%;'>
			<div><button class='btn btn-primary' style='width:150px;' type="button" @click='get_data("gtype")'>{{btn_name}}</button></div>
			<div><button class='btn btn-primary' style='width:150px;' type="button" @click='get_data("kikan")'>{{btn_name2}}</button></div>
			<!--<FORM method="post" action="graph01.php" style='width:200px;margin-left:50px;'>
				<button class='btn btn-primary' type="button" @click='get_data("gtype")'>{{btn_name}}</button>
				<INPUT type="hidden" name="hyoji" value=<?php //echo $typ;?>>
				<INPUT type="hidden" name="id" value="<?php //echo $id;?>">
				<INPUT type="hidden" name="shu" value="<?php //echo $shu;?>">
				<INPUT type="hidden" name="gtype" value="<?php //echo $_POST["gtype"];?>">
			</FORM>
			<FORM method="post" action="graph01.php" style='width:130px;'>
				<button class='btn btn-primary' type="button" @click='get_data("kikan")'>{{btn_name2}}</button>
				<INPUT type="hidden" name="hyoji" value=<?php //echo $typ;?>>
				<INPUT type="hidden" name="id" value="<?php //echo $id;?>">
				<INPUT type="hidden" name="shu" value="<?php //echo $shu;?>">
				<INPUT type="hidden" name="gtype" value="<?php //echo $kikan;?>">-->
			</FORM>
		</div>
	</div>
	<main class='container-fluid pb-5'>
		<template v-for='(list,index) in kintore_log' :key='list.ymd+list.jun'>
			<div class='accordion-item'>
				<div v-if='String(list.jun)==="0"' class='row shu accordion-header'>
					<button type='button' class='accordion-button collapsed' data-bs-toggle='collapse' :data-bs-target='`#collapseOne${list.ymd}${list.shu}`' 
					aria-expanded='false' aria-controls='collapseOne' >
						{{list.ymd}} {{(list.head_wt)}} kg
					</button>
				</div>
				<div v-if='String(list.jun)!=="0"'  :id='`collapseOne${list.ymd}${list.shu}`' class='accordion-collapse collapse' data-bs-parent='#accordionExample'>
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
		<a href=<?php echo "'TOP.php?id=".$id."&pass=".$pass."'" ?> class='btn btn-secondary' style = 'margin-top:0.8em;text-decoration: none;'>戻 る</a>
</div>
	</div>
	<script>
		/*
		(function basic(container) {
		  var d1 = [<?php //echo $graph_data;?>],
			d2 = [<?php //echo $graph_data2;?>],
		  data = [
				{
		      data: d1,
		      label: "<?php //echo $glabel1;?>"
				},{
		      data: d2,
					label: "<?php //echo $glabel2;?>"
		      
				}
		  ];
		  function labelFn(label) {
		      return label;
		  }
		  graph = Flotr.draw(container, data, {
				yaxis:{
					min:<?php //echo $minline; ?>,        //y軸の最小値を設定
					max:<?php //echo $maxline; ?>,        //y軸の最大値を設定
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
		*/
		

		
		//drow_chart(document.getElementById("graph"));
	</script>
	<script>//Vus.js
		const { createApp, ref, onMounted, computed, VueCookies,watch } = Vue;
		createApp({
			setup(){
				const kintore_log = ref(<?php //echo $kintore_log;?>)
				//label
				const btn_name = ref('MAX記録グラフへ')		 //max -> 量 -> 成長期
				const btn_name2 = ref('直近1年')							//1年 -> 全期間
				const gtype = computed(()=>{
					if(btn_name2.value==="直近1年"){
						return 'year'
					}else if(btn_name2.value==="全期間"){
						return 'all'
					}else{
						return ''
					}
				})
				const shu = ref('<?php echo $_POST["shu"];?>')	//トレーニング種目
				const graph_title = ref('')

				const drow_chart =(container,data1,data2,lb1,lb2,maxl,minl)=> {
				  let d1 = data1,
					d2 = data2,
				  data = [
						{
				      data: d1,
				      label: lb1
						},{
				      data: d2,
							label: lb2
						}
				  ];
				  function labelFn(label) {
				      return label;
				  }
					
				  graph = Flotr.draw(container, data, {
						yaxis:{
							min:minl,        //y軸の最小値を設定
							max:maxl,        //y軸の最大値を設定
							title:'(kg)'
						}, //y軸にタイトルを表示
				  	legend: {
				      position: 'se',
				      labelFormatter: labelFn,
				      backgroundColor: "#D2E8FF"
				  	},
				    HtmlText: false
				  });
				}
				const get_data = (p) =>{
					kintore_log.value = []
					if(p==="kikan"){
						if(btn_name2.value==="直近1年"){
							btn_name2.value="全期間"
						}else if(btn_name2.value==="全期間"){
							btn_name2.value="直近1年"
						}
						if(btn_name.value==="MAX記録グラフへ"){
							get_growth_data()
						}else if(btn_name.value==="ﾄﾚｰﾆﾝｸﾞ量グラフへ"){
							get_max_data()
						}else if(btn_name.value==="成長期グラフへ"){
							get_volume_data()
						}
					}else if(p==="gtype"){
						if(btn_name.value==="MAX記録グラフへ"){
							get_max_data()
							btn_name.value="ﾄﾚｰﾆﾝｸﾞ量グラフへ"
						}else if(btn_name.value==="ﾄﾚｰﾆﾝｸﾞ量グラフへ"){
							get_volume_data()
							btn_name.value="成長期グラフへ"
						}else if(btn_name.value==="成長期グラフへ"){
							get_growth_data()
							btn_name.value="MAX記録グラフへ"
						}
					}
				}
				
				const get_max_data = () =>{
					console_log("start get_max_data")
					const form_data = new FormData()
					form_data.append(`shu`, shu.value)
					form_data.append(`gtype`, gtype.value)
					axios
						.post("ajax_get_max_log.php",form_data, {headers: {'Content-Type': 'multipart/form-data'}})
						.then((response) => {
							console_log(response.data)
							kintore_log.value = response.data.kintore_log
							drow_chart(document.getElementById("graph"),
								response.data.graph_data1,
								response.data.graph_data2,
								response.data.glabel1,
								response.data.glabel2,
								response.data.maxline,
								response.data.minline)
							graph_title.value = response.data.graph_title
						})
						.catch((error) => {
							console_log(`get_max_data ERROR:${error}`)
						})
						.finally(()=>{
						})
				}
				const get_volume_data = () =>{
					console_log("start get_volume_data")
					const form_data = new FormData()
					form_data.append(`shu`, shu.value)
					form_data.append(`gtype`, gtype.value)
					axios
						.post("ajax_get_volume_log.php",form_data, {headers: {'Content-Type': 'multipart/form-data'}})
						.then((response) => {
							console_log(response.data)
							kintore_log.value = response.data.kintore_log
							drow_chart(document.getElementById("graph"),
								response.data.graph_data1,
								response.data.graph_data2,
								response.data.glabel1,
								response.data.glabel2,
								response.data.maxline,
								response.data.minline)
							graph_title.value = response.data.graph_title
						})
						.catch((error) => {
							console_log(`get_volume_data ERROR:${error}`)
						})
						.finally(()=>{
						})
				}
				const get_growth_data = () =>{
					console_log("start get_growth_data")
					const form_data = new FormData()
					form_data.append(`shu`, shu.value)
					axios
						.post("ajax_get_growth_log.php",form_data, {headers: {'Content-Type': 'multipart/form-data'}})
						.then((response) => {
							console_log(response.data)
							kintore_log.value = response.data.kintore_log
							drow_chart(document.getElementById("graph"),
								response.data.graph_data1,
								response.data.graph_data2,
								response.data.glabel1,
								response.data.glabel2,
								response.data.maxline,
								response.data.minline)
							graph_title.value = response.data.graph_title
						})
						.catch((error) => {
							console_log(`get_growth_data ERROR:${error}`)
						})
						.finally(()=>{
						})
				}

				onMounted(() => {
					console_log('onMounted')
					get_data('gtype')
				})
				return{
					kintore_log,
					get_data,
					btn_name,
					btn_name2,
					graph_title,
				}
			}
		}).mount('#app');
	</script>

</BODY>
</HTML>
