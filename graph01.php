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

?>
<!DOCTYPE html>
<HTML>
<HEAD>
	<?php
		require "header.php";
	?>
	<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
	<TITLE>肉体改造ネットワーク</TITLE>
</HEAD>
<BODY class='graph'>
	<div id='app'>
	<div id="headerArea2">
		<div class='row' style='height:100%;'>
			<div class='col-12' style='justify-content: center;height: auto;max-height:60px;'>
				<p class="graph-title">{{graph_title}}</p>
				<p v-show='graph_subtitle!==""' style='color:darkgrey;font-size:12px;'>{{graph_subtitle}}</p>
			</div>
		
			<div class='col-12' id="graph" style='height:250px;margin-bottom:5px;position:relative;max-width:900px;'>
				<canvas id="myChart"></canvas>
			</div>
			
			<div class='d-flex align-items-center justify-content-center' style='width: 100%;height:40px;'>
				<div class='text-end' style='width:50%;'><button class='btn btn-primary'   style='width:100%;max-width:200px;' type="button" @click='get_data("gtype")'>{{btn_name}}</button></div>
				<div class='text-start' style='width:50%;'><button class='btn btn-primary' style='width:100%;max-width:200px;' type="button" @click='get_data("kikan")'>{{btn_name2}}</button></div>
			</div>
		</div>
	</div>
	<main class='container'>
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
						<div class='col-12' style='padding:0  0 6px;display:flex;'>
							<div style='width: 20px;'>{{list.No - 1}}</div>
							<div class='text-end' style='width: 70px;padding:0;'>{{list.weight}}kg</div>
							<div v-if="list.tani==='0'"      class='text-end' style='width: 60px;padding-right:0;'>{{list.rep}}({{list.rep2}})回</div>
							<div v-else-if="list.tani==='1'" class='text-end' style='width: 65px;padding-right:0;'>{{list.rep}}({{list.rep2}})秒</div>
							<div class='text-end' style='padding-right:0;width:50px;'>{{list.sets}}sets</div>
							<div class='' style='padding:0 0 0 10px;'>{{list.memo}}</div>
						</div>
					</div>
				</div>
			</div>
		</template>
	</main>
	<footer id=""  class='footerArea' >
		<div class='container hf_color'>
		<div class='row'>
			<div class='col-12 text-center'>
				<a href=<?php echo "'TOP.php?id=".$id."&pass=".$pass."'" ?> class='btn btn-secondary' style = 'margin-top:0.8em;text-decoration: none;'>戻 る</a>
			</div>
		</div>
		</div>
	</footer>
	</div>
	<script>
	</script>
	<script>//Vus.js
		const { createApp, ref, onMounted, computed, VueCookies,watch } = Vue;
		createApp({
			setup(){
				const kintore_log = ref(<?php //echo $kintore_log;?>)
				//label
				const btn_name = ref('MAX記録へ')		 //max -> 量 -> 成長期
				const btn_name2 = ref('全期間へ')							//1年 -> 全期間へ
				const gtype = computed(()=>{
					if(btn_name2.value==="全期間へ"){
						return 'year'
					}else if(btn_name2.value==="前年比較へ"){
						return 'all'
					}else{
						return ''
					}
				})
				const shu = ref('<?php echo $_POST["shu"];?>')	//トレーニング種目
				const graph_title = ref('')
				const graph_subtitle = ref('')
				let datasets = []
				let labels = []

				const get_data = (p) =>{
					kintore_log.value = []
					if(p==="kikan"){
						if(btn_name2.value==="全期間へ"){
							btn_name2.value="前年比較へ"
						}else if(btn_name2.value==="前年比較へ"){
							btn_name2.value="全期間へ"
						}
						if(btn_name.value==="MAX記録へ"){
							get_growth_data()
						}else if(btn_name.value==="ﾄﾚｰﾆﾝｸﾞ量へ"){
							get_max_data()
						}else if(btn_name.value==="成長期へ"){
							get_volume_data()
						}
					}else if(p==="gtype"){
						if(btn_name.value==="MAX記録へ"){
							get_max_data()
							btn_name.value="ﾄﾚｰﾆﾝｸﾞ量へ"
						}else if(btn_name.value==="ﾄﾚｰﾆﾝｸﾞ量へ"){
							get_volume_data()
							btn_name.value="成長期へ"
						}else if(btn_name.value==="成長期へ"){
							get_growth_data()
							btn_name.value="MAX記録へ"
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
							labels = response.data.labels
							datasets = []
							let color
							if(gtype.value==='year'){
								color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 1)'
								datasets.push({
									'label':response.data.glabel1
									,'data':response.data.graph_data1
									,'backgroundColor': color
									,borderColor: color
									,fill:true
									,borderWidth: 4
									,pointRadius:2
								})
								color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 0.3)'
								datasets.push({
									'label':response.data.glabel2
									,'data':response.data.graph_data2
									,'backgroundColor': color
									,borderColor: color
									,fill:true
									,borderWidth: 2
									,pointRadius:1
								})
							}else if(gtype.value==='all'){
								color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 1)'
								datasets.push({
									'label':response.data.glabel1
									,'data':response.data.graph_data1
									, pointRadius:1
									,'backgroundColor': color
									,borderColor: color
									,borderWidth: 2
								})
							}
							graph_title.value = response.data.graph_title
							graph_subtitle.value = response.data.subtitle
							create_graph(document.getElementById('myChart'))
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
							labels = response.data.labels
							datasets = []
							let color
							if(gtype.value==='year'){
								color1 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//max
								color3 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//max
								color2 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//volume
								color4 = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))	//volume
								opacity1 = ', 1)'
								opacity2 = ', 0.6)'
								datasets.push({//去年のトレ量マックス
									'label':response.data.glabel2 + 'Mx'
									,'data':response.data.graph_data_max2
									,'backgroundColor':  color1 + opacity2
									,borderColor: color1 + opacity2
									,fill:true
									//,stepped: 'middle'
									,borderWidth: 2
									, pointRadius:1
									//,type:'bar'
								})
								datasets.push({//去年のトレ量
									'label':response.data.glabel2 + 'TTL'
									,'data':response.data.graph_data_total2
									,'backgroundColor':  color2 + opacity2
									,borderColor:  color2 + opacity2
									,fill:true
									,stepped: 'middle'
									,borderWidth: 2
									, pointRadius:1
									//,type:'bar'
								})
								color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 1)'
								datasets.push({
									'label':response.data.glabel1 + 'Mx'
									,'data':response.data.graph_data_max1
									,'backgroundColor':  color3 + opacity1
									,borderColor:  color3 + opacity1
									,fill:true
									//,stepped: 'middle'
									,borderWidth: 4
									, pointRadius:2
									//,type:'bar'
								})
								datasets.push({
									'label':response.data.glabel1 + 'TTL'
									,'data':response.data.graph_data_total1
									,'backgroundColor':  color4 + opacity1
									,borderColor:  color4 + opacity1
									,fill:true
									,stepped: 'middle'
									,borderWidth: 4
									, pointRadius:2
									//,type:'bar'
								})


							}else if(gtype.value==='all'){
								color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 1)'
								datasets.push({
									'label':response.data.glabel1 + 'Mx'
									,'data':response.data.graph_data_max1
									, pointRadius:1
									,'backgroundColor': color
									,borderColor: color
									,borderWidth: 2
								})
								datasets.push({
									'label':response.data.glabel1 + 'TTL'
									,'data':response.data.graph_data_total1
									,'backgroundColor': color
									,borderColor: color
									//,fill:true
									,borderWidth: 2
									//, pointRadius:1
									,type:'bar'
								})

							}
							graph_title.value = response.data.graph_title
							graph_subtitle.value = response.data.subtitle
							create_graph(document.getElementById('myChart'))
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
							labels = response.data.labels
							datasets = []
							color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 1)'
								datasets.push({
									'label':response.data.glabel1
									,'data':response.data.graph_data1
									, pointRadius:1
									,'backgroundColor': color
									,borderColor: color
									,borderWidth: 2
									,type:'bar'
								})
							graph_title.value = response.data.graph_title
							graph_subtitle.value = response.data.subtitle
							create_graph(document.getElementById('myChart'))
						})
						.catch((error) => {
							console_log(`get_growth_data ERROR:${error}`)
						})
						.finally(()=>{
						})
				}



				var graph_obj
				const create_graph = (ctx) =>{
					console_log("create_graph : graph_data")
					
					const graph_data = {
						labels    : labels
						,datasets : datasets
					}
				
					if(graph_obj){
						graph_obj.destroy()
					}
				
					graph_obj = new Chart(ctx, {
						type : 'line'
						,data: graph_data
						,options: {
							plugins: {
								title: {
									display: false,
									//text: open_fil.value
									text: 'test'
								},
								filler:{
									drawTime : 'beforeDraw'
								}
							},
							responsive: true,
							maintainAspectRatio: false,
							scales: {
								x: {
									stacked: false,
									ticks:{
										maxTicksLimit: 6
										,stepSize: 2,
									}
									/*display:true,
									title:{
										display:true,
										text:'月'
									}*/
								},
								y: {
									stacked: false,
									/*display:true,
									title:{
										display:true,
										text:'Kg'
									}*/
								}
							}
						}
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
					graph_subtitle,
				}
			}
		}).mount('#app');
	</script>

</BODY>
</HTML>
