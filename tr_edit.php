<?php
// 設定ファイルインクルード
require "config.php";
//require "functions.php";
require "edit_wt.php"; 		//ウェイト記録画面
require "edit_usanso.php"; 	//有酸素系記録画面

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

$now = date('Y-m-d');
//$id = ($_GET["id"]);
//$pass = ($_GET["pass"]);
$k_ymd = ($_GET["ymd"]); //更新キー
$k_jun = ($_GET["jun"]); //更新キー

?>
<HTML>
<HEAD>
<?php
	require "header.php";
?>

<TITLE>肉体改造ネットワーク</TITLE>
</HEAD>
<BODY style='background:#4E9ABE;'>
<?php
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
	$sql2 = "select * from tr_log where id = '".$id."' and ymd = '".$k_ymd."' and jun = '".$k_jun."'";
	$result2 = $mysqli->query( $sql2 );
	$row2 = $result2->fetch_assoc();

?>

<?php
if($row2["typ"] == "0"){
	edit_wt($id,$k_jun,$k_ymd);
}else{
	edit_usanso($id,$k_jun,$k_ymd);
}
?>
<!--
<div id="headerArea">
<ul class='ttl'><li class='ttl'>トレーニング記録修正</li></ul>
</div>

<FORM method="post" name = "form2" action="TOP.php">
<CENTER>
<TABLE style='background:#4E9ABE; color: #fff;' >
<TR><TD align='right' width='70' height='40'>日付：</TD><TD><INPUT type="date" size="10" name="ymd" maxlength="10" value="<?php echo $row["ymd"]?>"></TD></TR>
<TR><TD align='right' width='80' height='40'>種目選択：</TD><TD><SELECT name="shu1" id="nonrequired">
																<OPTION value=''></OPTION>
<?php
																while($row2 = $result2->fetch_assoc()){
																	echo "<OPTION value='".$row2["shu"]."'";
																	if($row2["shu"] == $row["shu"]){
																		echo "Selected";
																	}
																	echo ">".$row2["shu"]."</OPTION>";
																}
?>
																</SELECT>
														</TD></TR>
<TR><TD align='right' width='80' height='40'>種目追加：</TD><TD><INPUT type="text" id="required" size="20" name="shu2" maxlength="40" ></TD></TR>
<TR><TD align='right' width='70' height='40'>重量：</TD><TD><INPUT type="number" name="weight" maxlength="10" value="<?php echo $row["weight"]?>" style="ime-mode:disabled; width:60px;"></TD></TR>
<TR><TD align='right' width='70' height='40'>回数：</TD><TD><INPUT type="tel" name="rep" maxlength="10" value="<?php echo $row["rep"]?>" style="ime-mode:disabled; width:60px;"></TD></TR>
<TR><TD align='right' width='70' height='40'>補回数：</TD><TD><INPUT type="tel" name="rep2" maxlength="10" value="<?php echo $row["rep2"]?>" style="ime-mode:disabled; width:60px;"></TD></TR>
<TR><TD align='right' width='70' height='40'>SET数：</TD><TD><INPUT type="tel" name="sets" maxlength="10" value="<?php echo $row["sets"]?>" style="ime-mode:disabled; width:60px;"></TD></TR>
<TR><TD align='right' width='70' height='40'>メモ：</TD><TD><INPUT type="text" name="memo" maxlength="10" value="<?php echo $row["memo"]?>"></TD></TR>
<BR>
</TABLE>
    <script type="text/javascript">
    //<![CDATA[
        var elem1 = document.getElementById("required");
        var elem2 = document.getElementById("nonrequired");
        function check() {
            if( elem1.value == "" ) {
                elem2.removeAttribute("disabled");
            } else {
            	elem2.innerHTML = "";
                elem2.setAttribute("disabled", "disabled");
            }
        }
        check();
        var loop = window.setInterval( check, 500 );
    //]]>
    </script>
<BR>
<DIV>
<button type="button" onClick="history.back()"> 戻る </button>
<button type="submit"  onclick="return chk();" name="btn" value="修正"> 修正 </button>
</CENTER>
</DIV>
<INPUT type="hidden" name="id" value="<?php echo $id?>">
<INPUT type="hidden" name="pass" value="<?php echo $pass?>">
<INPUT type="hidden" name="k_ymd" value="<?php echo $k_ymd?>">
<INPUT type="hidden" name="k_jun" value="<?php echo $k_jun?>">
</FORM>
-->

<?php



$mysqli->close();

?>

</BODY>
</HTML>
