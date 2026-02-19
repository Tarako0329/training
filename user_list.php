<?php
// 設定ファイルインクルード
require_once "config.php";

$now = date('Y-m-d');
$id = ($_GET["id"]);
$pass = ($_GET["pass"]);
?>
<!DOCTYPE html>
<HTML>
<HEAD>
<?php
	require_once "header.php";
?>

<TITLE>肉体改造ネットワーク</TITLE>
</HEAD>
<BODY style='background:#4E9ABE;'>
<BR>
<?php
	//ユーザー確認
	unset($sql);
	$sql = "select * from users where ((id)=?) and ((pass)=?)";
	$stmt = $pdo_h->query( $sql );
	$stmt->bindValue(1, $id, PDO::PARAM_STR);
	$stmt->bindValue(2, $pass, PDO::PARAM_STR);
	$stmt->execute();
	$row_cnt = $stmt->rowCount();

	if($row_cnt==0){
		echo "<P>ＩＤ 又はパスワードが間違っています。</P>".$id.$pass;
		?><a href="index.php"> 戻る</a><?php
		exit();
	}
	$user_name = ($row["name"]);
	$sql = "select * from users order by id";
	$result = $pdo_h->query( $sql );
	while($row = $result->fetch(PDO::FETCH_ASSOC)){
		echo "<a href='TOP.php?user=V".$row["id"]."'>".($row["name"])."</a><br>";
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
</BODY>
</HTML>
