<?php
require "../config.php";
require "../functions.php";
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=BK_Insert_safety.php");

//データベースに接続する
$sql = "select * from tr_log";
$result = $mysqli->query( $sql );

//Insert用php出力

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
echo "<HTML>\n";
echo "<HEAD>\n";
echo "<META name=\"GENERATOR\" content=\"IBM WebSphere Homepage Builder V6.0.1 for Windows\">\n";
echo "<META http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
echo "<?PHP\n";
echo "\n";
echo "// 設定ファイルインクルード\n";
echo "require \"../config.php\";\n";
echo "require \"../functions.php\";\n";
echo "\n";
echo "\$stmt = \$mysqli->query(\"LOCK TABLES safety WRITE\");\n";
echo "\n";
echo "\n";
echo "//テーブルのデータを消去\n";
echo "\$SQL1 = \"delete from safety\";\n";
echo "\$stmt = \$mysqli->prepare(\$SQL1);\n";
echo "\$stmt->execute();\n";
echo "\n";


$i=0;
while($row = $result->fetch_assoc()){
	call_user_func("info_disp_grid", $row);
	$i=$i+1;
}

echo "\n";
echo "echo \"safety 読込完了<br />\";\n";
echo "\n";
echo "\$stmt = \$mysqli->query(\"UNLOCK TABLES\");\n";
echo "\$mysqli->close();\n";
echo "\n";
echo "?>";

$mysqli->close();

// =========================================================
// 個別情報表示(表形式)
// =========================================================
function info_disp_grid($array){
	unset($str);
	$str  = "insert into tr_log values ('";
	foreach($array as $tmp){
		$str .= $tmp."','";
	}
	echo "\$SQL2 = \"".substr($str,0,strlen($str)-2).")\";";
	echo "\n";
	echo "\$stmt = \$mysqli->prepare(\$SQL2);\n";
	echo "\$stmt->execute();\n";
}



?>