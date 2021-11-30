<?php
// 設定ファイルインクルード
require "config.php";
require "functions.php";

$now = date('Y-m-d');

$id = ($_GET["id"]);
$pass = ($_GET["pass"]);
//トレーニング種別
$shu = ($_GET["shu"]);
//グラフ種類（MAX0 or トレーニング量1)
$hyoji = ($_GET["hyoji"]);

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


?>
<HTML>
<HEAD>
<?php
	require "header.php";
?>
<TITLE>肉体改造ネットワーク</TITLE>
</HEAD>
<BODY class = "graphe">

<?php
//履歴取得
$sql = "select * from tr_log where id = '".$id."' and shu = '".$shu."' order by ymd desc,jun ";
$result = $mysqli->query( $sql );
$Lcounter = 0;
$seq = 1;
while($row = $result->fetch_assoc()){
	if($ymd_chk == $row["ymd"]){
	}else{
		if($Lcounter ==! 0){
			//echo "</ul>";
			echo "<TR><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD></TR>";
			echo "</TABLE>";
			echo "</div>";
		}
		//echo "<ul class = 'ymd'><li class = 'ymd'>".$row["ymd"]."</li>";
		$shu_chk = "";
	}
	
	if($shu_chk == $row["shu"]){
	}else{
		if($Lcounter ==! 0){
			?>
			</TABLE>
			</div>
			<?php
		}
		if($hyoji == "0"){//MAX表示
			$sql2 = "select * from tr_log where id = '".$id."' and shu = '".$shu."' and ymd = '".$row["ymd"]."' order by CAST(weight as SIGNED) desc,CAST(rep as SIGNED) desc ";
		}else{//total表示
			$sql2 = "select ymd,max(jun),shu,sum(weight*rep*sets) as t_weight from tr_log where id = '".$id."' and ymd = '".$row["ymd"]."' and shu = '".$row["shu"]."' group by ymd,shu";
		}
		$result2 = $mysqli->query( $sql2 );
		$row2 = $result2->fetch_assoc();
		
		if($hyoji == "0"){//MAX表示
			$weight = " - MAX：".number_format(max_r($row2["weight"], $row2["rep"] - $row2["rep2"]),2);
		}else{//total表示
			$weight = " - total：".number_format($row2["t_weight"],0);
		}

		?>
		<ul class = "shu">
		<!-- 折り畳み展開ポインタ -->
		<div onclick="obj=document.getElementById('<?php echo $row["ymd"].$row["jun"]?>').style; obj.display=(obj.display=='none')?'block':'none';">
		<a style="cursor:pointer;" class="open_button">
		<li class = 'shu'><?php echo "<B>+ </B>".$row["ymd"].$weight." kg" ?></li></a>
		</div>
		<div id="<?php echo $row["ymd"].$row["jun"]?>" style="display:none;clear:both;">
		<!-- ↓折り畳まれ部分↓ -->
		<TABLE cellSpacing='0' border='0'>
		<?php
		$seq = 1;
	}
	$link = "tr_edit.php?id=".$id."&pass=".$pass."&ymd=".$row["ymd"]."&jun=".$row["jun"];
	echo "<TR>";
	echo "<TD class = 'lst' align='right' width='20'>".$seq.".</TD><TD class = 'lst' align='right' width='75'>".number_format($row["weight"],2)." kg</TD>";
	echo "<TD class = 'lst' align='right' width='75'>".$row["rep"]."(".$row["rep2"].") 回</TD><TD class = 'lst' align='right' width='50'>".$row["sets"]." set</TD>";
	echo "<TD class = 'lst' align='left' width='200'> ".$row["memo"]."</TD>";
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

<div id="headerArea2">
<?php 
if($hyoji == "0"){ ?>
	<p class="graph-title">『<?php echo $shu ?> のＭＡＸ推移』</p>
<?php }else{ ?>
	<p class="graph-title">『<?php echo $shu ?> のトレーニング量推移』</p>
<?php } ?>
<div id="graph"></div>
<script type="text/javascript">
(function basic(container) {
    var d1 = [
<?php
	$Lcounter = 1;
	$gymd = "";
	$min_weight = 99999; //最小重量・グラフの最小値に使用
	$max_weight = 0;
	$t_weight = 0;
	if($hyoji == "0"){//MAX表示
		$sql = "select * from tr_log where id = '".$id."' and shu = '".$shu."' order by ymd ,CAST(weight as SIGNED) desc,rep desc ";
		$result = $mysqli->query( $sql );
		$row_cnt = $result->num_rows;
		while($row = $result->fetch_assoc()){
			if(strtotime($gymd) < strtotime($row["ymd"])){
				$M_weigt = number_format(max_r($row["weight"], $row["rep"] - $row["rep2"]),2);
				echo "[".$Lcounter.",".$M_weigt."],";
				$Lcounter = $Lcounter + 1;
				$gymd = $row["ymd"];
				if($min_weight > $M_weigt){
					$min_weight = (number_format($M_weigt/10,0)*10)-5;
				}
				if($max_weight < $M_weigt){
					$max_weight = (number_format($M_weigt/10,0)*10) + 5;
				}
			}
		}
	}else{//総量表示
		$sql = "select ymd,sum(weight*rep*sets) as t_weight from tr_log where id = '".$id."' and shu = '".$shu."' group by ymd order by ymd";
		$result = $mysqli->query( $sql );
		$row_cnt = $result->num_rows;

		while($row = $result->fetch_assoc()){//総量表示
			echo "[".$Lcounter.",".$row["t_weight"]."],";
			$Lcounter = $Lcounter + 1;
			if($min_weight > $row["t_weight"]){
				$min_weight = (number_format($row["t_weight"]/10,0)*10) - 300;
				//echo "<--".$t_weight."-->";
			}
			if($max_weight < $row["t_weight"]){
				//$max_weight = (number_format($row["t_weight"]/10,0))*10) + 300;
				$max_weight = (ceil($row["t_weight"]/10)*10) + 300;
				//echo "<!--".number_format($row["t_weight"]/10,0)."/-->";
			}
		}
	}
?>
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
			min:<?php echo $min_weight ?>,        //y軸の最小値を設定
			max:<?php echo $max_weight ?>,        //y軸の最大値を設定
			title:'(<?php if($hyoji == "1"){
							echo "総重量-";
			}?>kg)'}, //y軸にタイトルを表示
        legend: {
            position: "se",
            labelFormatter: labelFn,
            backgroundColor: "#D2E8FF"
        },
        HtmlText: false
    });
})(document.getElementById("graph"));
</script>
<CENTER>
<FORM method="get" action="graph01.php">
<?php
if($hyoji == "0"){ ?>
	<INPUT type="hidden" name="hyoji" value=1>
	<button type="submit"> トレーニング量グラフへ </button>
<?php }else{ ?>
	<INPUT type="hidden" name="hyoji" value=0>
	<button type="submit"> MAX記録グラフへ </button>
<?php } ?>
<INPUT type="hidden" name="id" value="<?php echo $id?>">
<INPUT type="hidden" name="pass" value="<?php echo $pass?>">
<INPUT type="hidden" name="shu" value="<?php echo $shu?>">
</CENTER>


</div>


<div id="footerArea2">
<CENTER>
<button style ='margin-top: 1em;'><a href=<?php echo "'TOP.php?id=".$id."&pass=".$pass."'" ?> style = 'text-decoration: none;'>　戻る　</a></button>
</CENTER>
</div>

<?php



$mysqli->close();

?>

</BODY>
</HTML>
