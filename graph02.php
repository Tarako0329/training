<?php
// 設定ファイルインクルード
//体組織系のグラフ・履歴画面
require "config.php";
require "functions.php";

$now = date('Y-m-d');
$hyoji = $_GET['hyoji'];

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

$sql = "select *,weight*taisibou/100 as sibouryou,weight-(weight*taisibou/100) as josibou from taisosiki where id = '".$id."' order by ymd desc ";
$result = $mysqli->query( $sql );
$row_cnt = $result->num_rows;
//$Lcounter = 0;
//$seq = 1;
echo "<CENTER>";
echo "<TABLE cellSpacing='0' border='0' >";
echo "<TR style='font-size:12px'><TD class = 'lst' align='CENTER'>日付</TD><TD class = 'lst' align='CENTER'>体重</TD><TD class = 'lst' align='right'>体脂肪率</TD><TD class = 'lst' align='right'>脂肪量</TD><TD class = 'lst' align='right'>筋骨</TD></TR>";

while($row = $result->fetch_assoc()){
	//$link = "tr_edit.php?id=".$id."&pass=".$pass."&ymd=".$row["ymd"]."&jun=".$row["jun"];
	
	echo "<TR>";
	echo "<TD class = 'lst' align='right' >".$row["ymd"].".</TD><TD class = 'lst' align='right' width='55'>".number_format($row["weight"],1)."kg</TD>";
	echo "<TD class = 'lst' align='right' width='55'>".$row["taisibou"]."%</TD><TD class = 'lst' align='right' width='55'>".number_format($row["sibouryou"],1)."kg</TD>";
	echo "<TD class = 'lst' align='right' width='60'> ".number_format($row["josibou"],1)."kg</TD>";
	echo "</TR>";
}
?>
</TABLE>
</CENTER>

<div id="headerArea2">
<?php 

if($hyoji == "0"){ ?>
	<p class="graph-title">『筋骨・脂肪量 の推移』</p>
<?php }else{ ?>
	<p class="graph-title">『体重・体脂肪率 の推移』</p>
<?php } ?>
<div id="graph"></div>
<script type="text/javascript">
(function basic(container) {

<?php
	$Lcounter = 1;
	$gymd = "";
	$min_weight1 = 99999; //最小重量・グラフの最小値に使用
	$max_weight1 = 0;
	$min_weight2 = 99999; //最小重量・グラフの最小値に使用
	$max_weight2 = 0;
	$label1 = "";
	$label2 = "";
	
	if($hyoji == "0"){//脂肪・除脂肪体重の表示
	    $label1 = "筋骨等";
	    $label2 = "脂肪";
	    //除脂肪体重のリスト取得
	    //取得結果配列を最初の行に戻す
        $result->data_seek($row_cnt-1);
	    echo "    var d1 = [";
		while($Lcounter <= $row_cnt+1){
		    $row = $result->fetch_assoc();
			if(strtotime($gymd) < strtotime($row["ymd"])){
				$M_weigt = number_format($row["josibou"],1);
				echo "[".$Lcounter.",".$M_weigt."],";
				$Lcounter = $Lcounter + 1;
				$gymd = $row["ymd"];
				if($min_weight1 > $M_weigt){
					$min_weight1 = (number_format($M_weigt,1)) - 20;
				}
				if($max_weight1 < $M_weigt){
					$max_weight1 = (number_format($M_weigt,1)) + 5;
				}
				
			}else{
			    $Lcounter = $Lcounter + 1;
			}
			$result->data_seek($row_cnt-$Lcounter);
		}
		echo "    ],";
	    //脂肪料のリスト取得
	    //取得結果配列を最初の行に戻す
        $result->data_seek($row_cnt-1);
        $Lcounter = 1;
        $gymd = "";
	    echo "    d2 = [";
		while($Lcounter <= $row_cnt+1){
		    $row = $result->fetch_assoc();
			if(strtotime($gymd) < strtotime($row["ymd"])){
				$M_weigt = number_format($row["sibouryou"],1);
				echo "[".$Lcounter.",".$M_weigt."],";
				$Lcounter = $Lcounter + 1;
				$gymd = $row["ymd"];
				if($min_weight2 > $M_weigt){
					$min_weight2 = (number_format($M_weigt,1)) - 1;
				}
				if($max_weight2 < $M_weigt){
					$max_weight2 = (number_format($M_weigt,1)) + 3;
				}
				
			}else{
			    $Lcounter = $Lcounter + 1;
			}
			$result->data_seek($row_cnt-$Lcounter);
		}
	}else{//体重・体脂肪率
	    $label1 = "体重";
	    $label2 = "体脂肪率";
	    //体重のリスト取得
	    //取得結果配列を最初の行に戻す
        $result->data_seek($row_cnt-1);
	    echo "    var d1 = [";
		while($Lcounter <= $row_cnt+1){
		    $row = $result->fetch_assoc();
			if(strtotime($gymd) < strtotime($row["ymd"])){
				$M_weigt = number_format($row["weight"],1);
				echo "[".$Lcounter.",".$M_weigt."],";
				$Lcounter = $Lcounter + 1;
				$gymd = $row["ymd"];
				if($min_weight1 > $M_weigt){
					$min_weight1 = (number_format($M_weigt,1)) - 10;
				}
				if($max_weight1 < $M_weigt){
					$max_weight1 = (number_format($M_weigt,1)) + 5;
				}
				
			}else{
			    $Lcounter = $Lcounter + 1;
			}
			$result->data_seek($row_cnt-$Lcounter);
		}
		echo "    ],";
	    //体脂肪率のリスト取得
	    //取得結果配列を最初の行に戻す
        $result->data_seek($row_cnt-1);
        $Lcounter = 1;
        $gymd = "";
	    echo "    d2 = [";
		while($Lcounter <= $row_cnt+1){
		    $row = $result->fetch_assoc();
			if(strtotime($gymd) < strtotime($row["ymd"])){
				$M_weigt = number_format($row["taisibou"],1);
				echo "[".$Lcounter.",".$M_weigt."],";
				$Lcounter = $Lcounter + 1;
				$gymd = $row["ymd"];
				if($min_weight2 > $M_weigt){
					$min_weight2 = (number_format($M_weigt,1)) - 1;
				}
				if($max_weight2 < $M_weigt){
					$max_weight2 = (number_format($M_weigt,1)) + 1;
				}
				
			}else{
			    $Lcounter = $Lcounter + 1;
			}
			$result->data_seek($row_cnt-$Lcounter);
		}
	}
?>
    ],
    data = [{
        data: d1,
        label: "<?php echo $label1 ?>"
    },{
        data: d2,
        label: "<?php echo $label2 ?>",
        yaxis:2
        
}];

    function labelFn(label) {
        return label;
    }

    graph = Flotr.draw(container, data, {
		yaxis:{
		    color:'#1E90FF',
			min:<?php echo $min_weight1 ?>,        //y軸の最小値を設定
			max:<?php echo $max_weight1 ?>},       //y軸の最大値を設定
		y2axis:{
		    color:'#6B8E23',
			min:<?php echo $min_weight2 ?>,        //y軸の最小値を設定
			max:<?php echo $max_weight2 ?>},       //y軸の最大値を設定
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
<FORM method="get" action="graph02.php">
<?php
if($hyoji == "0"){ ?>
	<INPUT type="hidden" name="hyoji" value=1>
	<button type="submit"> 体重・体脂肪率 の推移へ </button>
<?php }else{ ?>
	<INPUT type="hidden" name="hyoji" value=0>
	<button type="submit"> 筋骨・脂肪量 の推移へ </button>
<?php } ?>
<INPUT type="hidden" name="id" value="<?php echo $id?>">
</CENTER>

</div>

<div id="footerArea2">
<CENTER>
<button style ='margin-top: 1em;'><a href=<?php echo "'TOP.php'" ?> style = 'text-decoration: none;'>　戻る　</a></button>
</CENTER>
</div>

<?php
$mysqli->close();
?>

</BODY>
</HTML>
