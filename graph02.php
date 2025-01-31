<?php
// 設定ファイルインクルード 
require "config.php";
log_writer2("\$_POST",$_POST,"lv3");


if(isset($_SESSION['USER_ID'])){ //ユーザーチェックブロック
	$id = $_SESSION['USER_ID'];
	//echo "session:".$id;
}else if (check_auto_login($_COOKIE['token'])==0) {
	$id = $_SESSION['USER_ID'];
	//echo "クッキー:".$id;
}else{
	//header("HTTP/1.1 301 Moved Permanently");
	//header("Location: index.php");
	var_dump($_SESSION);
	exit();
}	

//履歴取得
//var_dump($kintore_log);
//exit();

//履歴取得


//ぐらふでーた取得


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
<BODY class = "graph" style='padding-top:392px;'>
<div  id='app'>
	<div id="headerArea2">
		<div class='row' style='height:100%;'>
			<div class='col-12' style='justify-content: center;height: auto;max-height:60px;'>
				<p class="graph-title">{{graph_title}}</p>
				<p v-show='graph_subtitle!==""' style='color:darkgrey;font-size:12px;'>{{graph_subtitle}}</p>
			</div>
		
			<div class='col-12' id="graph" style='height:250px;margin-bottom:5px;position:relative;max-width:900px;'>
				<canvas id="myChart"></canvas>
			</div>
		
			<div  class='d-flex align-items-center justify-content-center' style='width: 100%;height:40px;'>
				<div class='text-end' style='width:50%;max-width:200px;'>
					<select v-model='shu' class='form-select' style='width:100%;max-width:200px;'>
						<option value='taiju'>体重・体脂肪率</option>
						<option value='kinryou'>骨格筋量・脂肪量</option>
					</select>
				</div>
				<div class='text-start' style='width:50%;max-width:200px;'>
					<select v-model='gtype' class='form-select' style='width:100%;max-width:200px;'>
						<option value='all'>全期間</option>
						<option value='year'>前年比較</option>
					</select>
				</div>
			</div>
		</div>
	</div>
	<main class='container-fluid'>
		<div class='' style='position:fixed;top:350px;left:12px; width: 100%;'>
			<table class='table m-0' style='max-width:400px;width:100%;'>
				<thead class=''>
					<th class='text-end p-0' style='width:50px;'>体重<br></th>
					<th class='text-end p-0' style='width:50px;'>脂肪率<br></th>
					<th class='text-end p-0' style='width:50px;'>脂肪量<br></th>
					<th class='text-end p-0' style='width:50px;'>筋肉量<br></th>
					<th class='text-end p-0' style='width:50px;'>骨格<br>筋率</th>
					<th class='text-end p-0' style='width:50px;'>BMI<br></th>
				</thead>
			</table>
		</div>
		<div >
			<table class='table ' style='max-width:400px;width:100%;'>
				<template v-for='(list,index) in taisosiki_log' :key='list.ymd+list.No'>
					<tr class='lst'>
						<td class='text-left p-0' colspan="6" style=''>{{list.ymd}} memo:{{list.memo}}</td>
					</tr>
					<tr class='lst'>
						<td class='text-end p-0' style='width:50px;'>{{list.weight}}kg</td>
						<td class='text-end p-0' style='width:50px;'>{{list.taisibou}}%</td>
						<td class='text-end p-0' style='width:50px;'>{{list.sibouryou}}kg</td>
						<td class='text-end p-0' style='width:50px;'>{{list.josibou / 2}}kg</td>
						<td class='text-end p-0' style='width:50px;'>{{Math.round((list.josibou / 2) / list.weight * 100)}}%</td>
						<td class='text-end p-0' style='width:50px;'>{{Math.round((list.weight / (tall * tall)) * 10) /10}}</td>
					</tr>
				</template>
			</table>
		</div>
	</main>
	<footer id=""  class='footerArea text-center' >
		<a href=<?php echo "'TOP.php?id=".$id."&pass=".$pass."'" ?> class='btn btn-secondary' style = 'margin-top:0.8em;text-decoration: none;'>戻 る</a>
	</footer>
</div>
	<script>
	</script>
	<script>//Vus.js
		const { createApp, ref, onMounted, computed, VueCookies,watch } = Vue;
		createApp({
			setup(){
				const taisosiki_log = ref([])
				const tall = ref(0)

				const gtype = ref('all')
				const shu = ref('taiju')
				const graph_title = ref('')
				const graph_subtitle = ref('')
				let G_data1 = []
				let G_data2 = []
				let G_data3 = []
				let G_data4 = []
				let labels = []
				
				let datasets = []

				var graph_obj
				const create_graph = (ctx) =>{
					console_log("create_graph : graph_data")
					
					const graph_data = {
						labels    : labels
						,datasets : datasets
					}
					
					console_log(graph_data)

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
									,display:true,
									title:{
										display:true,
										text:'ヵ月前'
									}
								},
								y1: {
									stacked: false
									//,min: 30
									/*display:true,
									title:{
										display:true,
										text:'Kg'
									}*/
								}
								,y2: {
									stacked: false,
									position:     "right",
									grid: {
										drawOnChartArea: false,
        					},
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

				const get_taiju_data = () =>{
					console_log("start get_taiju_data")
					const form_data = new FormData()
					form_data.append(`hyoji`, shu.value)
					form_data.append(`gtype`, gtype.value)
					axios
						.post("ajax_get_taiju_sibouritu_log.php",form_data, {headers: {'Content-Type': 'multipart/form-data'}})
						.then((response) => {
							console_log(response.data)
							taisosiki_log.value = response.data.taisosiki_log
							tall.value = response.data.height
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
								datasets.push({//去年の体重
									'label':response.data.glabel1
									,'data':response.data.graph_data1
									,'backgroundColor':  color1 + opacity2
									,borderColor: color1 + opacity2
									,fill:true
									//,stepped: 'middle'
									,borderWidth: 2
									, pointRadius:1
									,type:'bar'
								})
								datasets.push({//去年の体脂肪率
									'label':response.data.glabel2
									,'data':response.data.graph_data2
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
									'label':response.data.glabel1
									,'data':response.data.graph_data3
									,'backgroundColor':  color3 + opacity1
									,borderColor:  color3 + opacity1
									,fill:true
									//,stepped: 'middle'
									,borderWidth: 4
									, pointRadius:2
									,type:'bar'
								})
								datasets.push({
									'label':response.data.glabel2
									,'data':response.data.graph_data4
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
									'label':response.data.glabel1
									,'data':response.data.graph_data1
									, pointRadius:1
									,'backgroundColor': color
									,borderColor: color
									,borderWidth: 2
									//,type:'bar'
									,yAxisID:"y1"
									
								})
								color = 'rgba('+(~~(256 * Math.random()))+','+(~~(256 * Math.random()))+','+ (~~(256 * Math.random()))+', 1)'
								datasets.push({
									'label':response.data.glabel2
									,'data':response.data.graph_data2
									,'backgroundColor': color
									,borderColor: color
									//,fill:true
									,borderWidth: 2
									//, pointRadius:1
									//,type:'bar'
									,yAxisID:"y2"
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

				watch([gtype,shu],()=>{
					if(shu.value==='taiju'){
						get_taiju_data()
					}else if(shu.value==='kinryou'){
						get_taiju_data()
					}else{

					}
				})
				onMounted(() => {
					console_log('onMounted')
					get_taiju_data()
				})

				return{
					taisosiki_log,
					tall,
					graph_title,
					graph_subtitle,
					gtype,
					shu,
				}
			}
		}).mount('#app');
	</script>

</BODY>
</HTML>
