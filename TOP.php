<?php

// 設定ファイルインクルード【開発中】

require "config.php";
require "functions.php";
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
if($_SERVER["REQUEST_METHOD"] == "POST"){
	//$fname = ($_POST["fname"]);
	//$id = ($_POST["id"]);
	//$sex = ($_POST["sex"]);
	//$pass = passEx($_POST["pass"],$id);
}

?>

<HTML>
<HEAD>

<?php
	require "header.php";
?>

</HEAD>
<TITLE>肉体改造ネットワーク</TITLE>
<BODY>
<?php
if($_POST["btn"] == "ユーザー登録"){
	$id = ($_POST["id2"]);
	$pass = passEx($_POST["pass2"],$id);
	$sql = "insert into users values ('".$id."','".$pass."','".rot13encrypt($fname)."','".$sex."');";
	$stmt = $mysqli->query("LOCK TABLES users WRITE");
	$stmt = $mysqli->prepare($sql);
	$stmt->execute();
	$stmt = $mysqli->query("UNLOCK TABLES");
	echo "ようこそ".$fname."さん";
?>


<?php
	//exit();
}else{

	//ユーザー確認
	unset($sql);

	//$sql = "select * from users where ((id)='".$id."') and ((pass)='".$pass."')";
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



//結果書き込み

if(trim($_POST["shu2"]) == ""){
	//種目追加欄が空白の場合はリストの種目
	$shu = $_POST["shu1"];
}else{
	//種目追加欄が記入されてる場合は種目追加欄の種目
	$shu = $_POST["shu2"];
}

//if($_POST["btn"] == "記　録"){//結果記録
if($_POST["btn"] == "ins_bt"){//結果記録
	$sql = "select max(jun) as junban from tr_log where ymd = '".$_POST["ymd"]."';";
	//echo $sql;

	$result = $mysqli->query( $sql );
	$row_cnt = $result->num_rows;
	$row = $result->fetch_assoc(); 
	if($row_cnt==0){
		$jun=1;
	}else{
		$jun=$row["junban"]+1;
		//echo $row["junban"];
	}

	if($_POST["rep2"] == ""){
		$rep2 = 0;
	}else{
		$rep2 = $_POST["rep2"];
	}
	if($_POST["cal"] == ""){
		$cal = 0;
	}else{
		$cal = $_POST["cal"];
	}

	

	$sql = "insert into tr_log values('";
	$sql = $sql.$id."','";
	$sql = $sql.$shu."','";
	$sql = $sql.$jun."','";
	$sql = $sql.$_POST["weight"]."','";
	$sql = $sql.$_POST["rep"]."','";
	$sql = $sql.$_POST["tani"]."','";
	$sql = $sql.$rep2."','";
	$sql = $sql.$_POST["sets"]."','";
	$sql = $sql.$cal."','";
	$sql = $sql.$_POST["ymd"]."','";
	$sql = $sql.$_POST["memo"]."','";
	$sql = $sql.$_POST["typ"]."')";

	//echo $sql;

	$stmt = $mysqli->query("LOCK TABLES tr_log WRITE");
	$stmt = $mysqli->prepare($sql);
	$stmt->execute();
	$stmt = $mysqli->query("UNLOCK TABLES");
}

//if($_POST["btn"] == "修　正"){//結果修正
if($_POST["btn"] == "upd_bt"){//結果修正

	$sql = "select max(jun) as junban from tr_log where ymd = '".$_POST["ymd"]."';";

	//echo $sql;

	$result = $mysqli->query( $sql );
	$row_cnt = $result->num_rows;
	$row = $result->fetch_assoc(); 
	if($_POST["k_ymd"] == $_POST["ymd"]){
		$jun=$_POST["k_jun"];
	}else{
		if($row_cnt==0){
			$jun=1;
		}else{
			$jun=$row["junban"]+1;
			//echo $row["junban"];
		}
	}
	if($_POST["rep2"] == ""){
		$rep2 = 0;
	}else{
		$rep2 = $_POST["rep2"];
	}
	if($_POST["cal"] == ""){
		$cal = 0;
	}else{
		$cal = $_POST["cal"];
	}

	$sql = "update tr_log set ";
	$sql = $sql."shu = '".$shu."',";
	$sql = $sql."jun = '".$jun."',";
	$sql = $sql."weight = '".$_POST["weight"]."',";
	$sql = $sql."rep = '".$_POST["rep"]."',";
	$sql = $sql."rep2 = '".$rep2."',";
	$sql = $sql."sets = '".$_POST["sets"]."',";
	$sql = $sql."cal = '".$cal."',";
	$sql = $sql."ymd = '".$_POST["ymd"]."',";
	$sql = $sql."memo = '".$_POST["memo"]."' ";
	$sql = $sql."where id ='".$id."' and ymd = '".$_POST["k_ymd"]."' and jun = '".$_POST["k_jun"]."'";

	//echo $sql;

	$stmt = $mysqli->query("LOCK TABLES tr_log WRITE");
	$stmt = $mysqli->prepare($sql);
	$stmt->execute();
	$stmt = $mysqli->query("UNLOCK TABLES");
}

if($_POST["btn"] == "del_bt"){ //削除
	$sql = "delete from tr_log where id ='".$id."' and ymd = '".$_POST["k_ymd"]."' and jun = '".$_POST["k_jun"]."'";
	$stmt = $mysqli->query("LOCK TABLES tr_log WRITE");
	$stmt = $mysqli->prepare($sql);
	$stmt->execute();
	$stmt = $mysqli->query("UNLOCK TABLES");
}

if($_POST["edit_date"] == $now){
	$enow = $_POST["ymd"];
}else{
	$enow = $now;
}

//履歴取得

$sql = "select * from tr_log where id = '".$id."' and ymd >= '" .date("Y-m-d",strtotime("-6 month")). "' order by ymd desc,jun ";
$result = $mysqli->query( $sql );
$Lcounter = 0;

$seq = 1;

while($row = $result->fetch_assoc()){
	if($ymd_chk == $row["ymd"]){
	}else{
		if($Lcounter ==! 0){
			echo "</ul>";
			echo "<TR><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD></TR>";
			echo "</TABLE>";
			echo "</div>";
		}
		echo "<ul class = 'ymd'><li class = 'ymd'>".$row["ymd"]."(".$week[date('w', strtotime($row["ymd"]))].")</li>";
	}
	if($shu_chk == $row["shu"] && $ymd_chk == $row["ymd"]){
	}else{
		if($Lcounter ==! 0){
			?>
			</TABLE>
			</div>
			<?php
		}
		
		if($row["tani"]==0){
			$tani = "回";
			$sql2 = "select ymd,max(jun),shu,sum(weight*rep*sets) as t_weight from tr_log where id = '".$id."' and ymd = '".$row["ymd"]."' and shu = '".$row["shu"]."' group by ymd,shu";
		}else if($row["tani"]==1){
			$tani = "秒";
			$sql2 = "select ymd,max(jun),shu,avg(weight) as t_weight, sum(rep*sets) as t_time from tr_log where id = '".$id."' and ymd = '".$row["ymd"]."' and shu = '".$row["shu"]."' group by ymd,shu";
		}else{
			$tani = "分";
			$sql2 = "select ymd,max(jun),shu,sum(rep) as t_time, sum(rep2) as t_kyori, sum(cal) as t_cal from tr_log where id = '".$id."' and ymd = '".$row["ymd"]."' and shu = '".$row["shu"]."' group by ymd,shu";
		}

		
		//$sql2 = "select ymd,max(jun),shu,sum(weight*rep*sets) as t_weight from tr_log where id = '".$id."' and ymd = '".$row["ymd"]."' and shu = '".$row["shu"]."' group by ymd,shu";

		$result2 = $mysqli->query( $sql2 );
		$row2 = $result2->fetch_assoc();

		if($row["tani"]==0){
			$t_weight = " - total：".number_format($row2["t_weight"],0)." kg";
		}else if($row["tani"]==1){
			$t_weight = " - avg：".number_format($row2["t_weight"],0)." kg × ".number_format($row2["t_time"],0)." 秒";
		}else{
			$tani = "分";
			//$t_weight = " -total：".number_format($row2["t_time"],0)."分 ".number_format($row2["t_kyori"]/1000,1)."Km ".number_format($row2["t_cal"],0)."Kcal";
			$t_weight = " - total：".number_format($row2["t_time"],0)."分 ".number_format($row2["t_cal"],0)."Kcal";
		}
		
		
		?>
		<ul class = "shu">
		<!-- 折り畳み展開ポインタ -->
		<div onclick="obj=document.getElementById('<?php echo $row["ymd"].$row["jun"]?>').style; obj.display=(obj.display=='none')?'block':'none';">
		<a style="cursor:pointer;" class="open_button">
		<li class = 'shu'><?php echo "<B>+ </B>".$row["shu"].$t_weight ?>
			<FORM method="get" action="graph01.php" style='display:inline;'>
			<?php
				if($row["typ"]=="0"){//有酸素運動はグラフ未対応
					echo "<button style='position: absolute; right: 10;'><i class='fa fa-line-chart' ></i></button> <!-- グラフ表示ボタン -->";
				}
			?>
			<INPUT type="hidden" name="id" value="<?php echo $id?>">
			<INPUT type="hidden" name="pass" value="<?php echo $pass?>">
			<INPUT type="hidden" name="shu" value="<?php echo $row["shu"]?>">
			<INPUT type="hidden" name="hyoji" value="0">
			</FORM>
		</li></a>
		<!--<li class = 'shu' style = 'display: inline;'><a href = <?php echo "'graph01.php?id=".$id."&pass=".$pass."&shu=".$row["shu"]."&hyoji=0'" ?>><i class='fa fa-line-chart' ></i></a></li>-->
		</div>
		<div id="<?php echo $row["ymd"].$row["jun"]?>" style="display:none;clear:both;">
		<!-- ↓折り畳まれ部分↓ -->
		<TABLE cellSpacing='0' border='0'  width='100%'>
		<?php
		$seq = 1;
	}

	$link = "tr_edit.php?id=".$id."&pass=".$pass."&ymd=".$row["ymd"]."&jun=".$row["jun"];

	echo "<TR>";
	if($row["tani"]==0){
		echo "<TD class = 'lst' align='right' width='8%'>".$seq.".</TD><TD class = 'lst' align='right'>".number_format($row["weight"],2)." kg</TD>";
		echo "<TD class = 'lst' align='right'>".$row["rep"]."(".$row["rep2"].") ".$tani."</TD><TD class = 'lst' align='right'>".$row["sets"]." set</TD>";
	}else if($row["tani"]==1){
		echo "<TD class = 'lst' align='right' width='8%'>".$seq.".</TD><TD class = 'lst' align='right'>".number_format($row["weight"],2)." kg</TD>";
		echo "<TD class = 'lst' align='right'>".$row["rep"]." ".$tani."</TD><TD class = 'lst' align='right'>".$row["sets"]." set</TD>";
	}else{
		echo "<TD class = 'lst' align='right' width='8%'>".$seq.".</TD><TD class = 'lst' align='right'>".number_format($row["rep"],0)." 分</TD>";
		echo "<TD class = 'lst' align='right'>".number_format($row["rep2"],0)." ｍ</TD><TD class = 'lst' align='right'>".$row["cal"]." Kcal</TD>";
	}
	echo "<TD class = 'lst' align='left' width='40%'> ".$row["memo"]."</TD><TD class = 'lst' align='right'>";
	echo "<a href = '".$link."' style = 'text-decoration: none;'><i class='fa fa-edit'>　</i></a></TD>";//記録修正ボタン
	echo "</TR>";

	$ymd_chk = $row["ymd"];
	$shu_chk = $row["shu"];
	$Lcounter = $Lcounter + 1;

	$seq = $seq + 1;
}
?>
	<TR><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD></TR>
	</TABLE>
</div>

<!--↑履歴取得完了 -->

<!--↓体組織系記録エリア-->
<div class="edit" id="taisosiki-edit" style="height:350px;position:fixed;bottom:55px;display:none;">
<?php
	edit_taisosiki($id,"0",$enow);
?>
</div>
<!--↑体組織系記録エリア -->

<!--↓有酸素系記録エリア-->
<div class="edit" id="usanso-edit" style="height:370px;position:fixed;bottom:55px;display:none;">
<?php
	edit_usanso($id,"0",$enow);
?>
</div>
<!--↑有酸素系記録エリア -->

<!--↓ウェイト記録エリア-->
<div class="edit" id="wt-edit" style="height:470px;position:fixed;bottom:55px;display:none;">
<?php
	edit_wt($id,"0",$enow);
?>
</div>
<!--↑ウェイト記録エリア -->

<!--↓headerArea -->
<?php

if(__FILE__=="/home/ifduktdo/public_html/training_test/TOP.php"){
	//テスト環境カラー
	echo "<div id='headerArea' style='background-color: #8A2908;'>";
}else{
	echo "<div id='headerArea'>";
}

echo "ようこそ ".$user_name." さん<BR>";

?>

</div>

<!--↑headerArea -->


<div id="footerArea">

<ul id="menu">
  <li><a href="#" id="taisosiki">体組織</a></li>
  <li><a href="#" id="running">有酸素系</a></li>
  <li><a href="#" id="weight">ウェイト</a></li>
</ul>

</div>

<!--↑footerArea -->


<?php


$mysqli->close();

?>
</BODY>
</HTML>

