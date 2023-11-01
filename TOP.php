<?php
// 設定ファイルインクルード【開発中】
require "config.php";
//require "functions.php";
require "edit_wt.php"; 		//ウェイト記録画面
require "edit_usanso.php"; 	//有酸素系記録画面
require "edit_taisosiki.php"; 	//体組織記録画面

if (isset($_GET['user'])){
    $_SESSION['USER_ID'] = substr($_GET['user'],1);
    $id = substr($_GET['user'],1);
    if(substr($_GET['user'],0,1)=="V"){
        $_SESSION['mode']="viewer";
    }else if(substr($_GET['user'],0,1)=="C"){
        $_SESSION['mode']="custom";
    }
	decho ("GET:".$id."(".$_SESSION['mode'].")");
}else if(isset($_SESSION['USER_ID'])){
	$id = $_SESSION['USER_ID'];
	decho ("session:".$id);
}else if (check_auto_login($_COOKIE['token'])==0) {
	$id = $_SESSION['USER_ID'];
	decho ("クッキー:".$id);
}else{
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: index.php");
}

$now = date('Y-m-d');
$week = [
  '日', //0
  '月', //1
  '火', //2
  '水', //3
  '木', //4
  '金', //5
  '土', //6
];
if($_POST["btn"] == "ユーザー登録"){
	$id = ($_POST["id2"]);
	$pass = passEx($_POST["pass2"],$id);
	$sql = "insert into users values ('".$id."','".$pass."','".rot13encrypt($fname)."','".$sex."');";
	$stmt = $mysqli->query("LOCK TABLES users WRITE");
	$stmt = $mysqli->prepare($sql);
	$stmt->execute();
	$stmt = $mysqli->query("UNLOCK TABLES");
	$user_name = $fname;
}else{
	//ユーザー確認
	unset($sql);

	$sql = "select * from users where ((id)='".$id."')";
	$result = $mysqli->query( $sql );
	$row_cnt = $result->num_rows;
	$row = $result->fetch_assoc(); 

	if($row_cnt==0){
		echo "<P>ＩＤ 又はパスワードが間違っています。</P>";//.$id.$pass;
		?><a href="index.php"> 戻る</a><?php
		exit();
	}

	$user_name = rot13decrypt($row["name"]);
	//echo "ログインＯＫ<BR>";
}

//履歴取得
$sql = "select *,SUM(weight*rep*sets) OVER (PARTITION BY id,shu,ymd) as total,RANK() OVER(PARTITION BY id,ymd,shu order by jun ) as setjun from tr_log where id = '".$id."' and ymd >= '" .date("Y-m-d",strtotime("-1 month")). "' order by ymd desc,jun ";
$result = $mysqli->query( $sql );
$kiroku = $result->fetch_all(MYSQLI_ASSOC);
$kintore_log = json_encode($kiroku, JSON_UNESCAPED_UNICODE);
?>

<HTML>
	<HEAD>
		<?php
		require "header.php";
		?>
		<TITLE>肉体改造ネットワーク</TITLE>
	</HEAD>
	
	<BODY>
		<div id='logger'>
			<header class='headerArea'>
			ようこそ <?php echo $user_name;?> さん
			</header>
			<main class='container-fluid'>
				<template v-for='(list,index) in log_edit' :key='list.ymd+list.jun'>
				<div v-if='index==0 || (index!==0 && list.ymd !== log_edit[index-1].ymd)' class='row ymd'>{{list.ymd}}</div>
					<div v-if='index==0 || (index!==0 && list.shu !== log_edit[index-1].shu)' class='row shu'>{{list.shu}} {{Number(list.total).toLocaleString()}}kg</div>
					<div class='row lst'>
						<div class='col-1'>{{list.setjun}}</div>
						<div class='col-2 text-end' style='padding-left:0;'>{{list.weight}}kg</div>
						<div class='col-2' style='padding-right:0;'>{{list.rep}}({{list.rep2}})回</div>
						<div class='col-2' style='padding-right:0;'>{{list.sets}}sets</div>
						<div class='col-4' style='padding:0;'>{{list.memo}}</div>
					</div>
				</template>
			</main>
			<footer class="footerArea">
			<ul id="menu">
		  	<li><a href="#" id="taisosiki">体組織</a></li>
		  	<li><a href="#" id="running">有酸素系</a></li>
			  <li><a href="#" id="weight">ウェイト</a></li>
			</ul>
			</footer>
			<!--↑footerArea -->
			<!--↓体組織系記録エリア-->
			<div class="edit" id="taisosiki-edit" style="height:350px;position:fixed;bottom:55px;display:none;">
			<?php
			//edit_taisosiki($id,"0",$enow);
			?>
			</div>

			<!--↓有酸素系記録エリア-->
			<div class="edit" id="usanso-edit" style="height:370px;position:fixed;bottom:55px;display:none;">
			<?php
			//edit_usanso($id,"0",$enow);
			?>
			</div>

			<!--↓ウェイト記録エリア-->
			<div class="edit" id="wt-edit" style="height:470px;position:fixed;bottom:55px;display:none;">
			<?php
			//edit_wt($id,"0",$enow);
			?>	
			</div>
		</div>
		
		<script>//Vus.js
			const { createApp, ref, onMounted, computed, VueCookies } = Vue;
			createApp({
				setup(){
					const kintore_log = (<?php echo json_encode($kiroku, JSON_UNESCAPED_UNICODE);?>)
					const week = (date) =>{
						const WeekChars = [ "(日)", "(月)", "(火)", "(水)", "(木)", "(金)", "(土)" ];
						let dObj = new Date( date );
						let wDay = dObj.getDay();
						console_log("指定の日は、" + WeekChars[wDay] + "です。");
						return WeekChars[wDay]
					}
					const log_edit = computed(()=>{
						kintore_log.forEach((row)=>{
							row.ymd = row.ymd + ' ' + week(row.ymd)
						})
						return kintore_log
					})
					onMounted(() => {
						console_log('onMounted')
					})
					return{
						//kintore_log,
						week,
						log_edit,
					}
				}
			}).mount('#logger');
		</script>
	</BODY>
</HTML>

<?php


$mysqli->close();

?>
