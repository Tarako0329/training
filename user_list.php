<?php
// 設定ファイルインクルード
require "config.php";
//require "functions.php";

$now = date('Y-m-d');
$id = ($_GET["id"]);
$pass = ($_GET["pass"]);
?>
<HTML>
<HEAD>
<?php
	require "header.php";
?>

<TITLE>肉体改造ネットワーク</TITLE>
</HEAD>
<BODY style='background:#4E9ABE;'>
<BR>
<?php
	//ユーザー確認
	unset($sql);
	$sql = "select * from users where ((id)='".$id."') and ((pass)='".$pass."')";
	$result = $mysqli->query( $sql );
	$row_cnt = $result->num_rows;
	$row = $result->fetch_assoc(); 
	if($row_cnt==0){
		echo "<P>ＩＤ 又はパスワードが間違っています。</P>".$id.$pass;
		?><a href="index.php"> 戻る</a><?php
		exit();
	}
	$user_name = rot13decrypt($row["name"]);
	$sql = "select * from users order by id";
	$result = $mysqli->query( $sql );
	while($row = $result->fetch_assoc()){
		echo "<a href='TOP.php?user=V".$row["id"]."'>".rot13decrypt($row["name"])."</a><br>";
	}
?>
<CENTER>

<div id="headerArea">
<ul class='ttl'><li class='ttl'>ユーザー一覧</li></ul>
</div>

<div id="footerArea2">
<button type="button" onClick="history.back()"> 戻る </button>
</div>
</CENTER>
<?php



$mysqli->close();

?>

</BODY>
</HTML>
