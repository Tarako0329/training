<?php 
function edit_usanso($id_f,$jun_f,$ymd_f){
	//ウェイトトレーニング記録画面
	//$id_f		：ユーザID
	//$jun_f	：順番(0の場合は新規)
	//$ymd_f	：実施日
	
	$mysqli = new mysqli(sv, user, pass, dbname);
	//種目リスト
	$sql = "select shu,max(ymd) from tr_log where id in ('".$id_f."','list') and typ = '1' group by shu order by max(ymd) desc,jun desc ";
	$result = $mysqli->query( $sql );
	//実施結果
	$sql2 = "select * from tr_log where id = '".$id_f."' and ymd = '".$ymd_f."' and jun = '".$jun_f."'";
	$result2 = $mysqli->query( $sql2 );
	$row2 = $result2->fetch_assoc();
	
	if($jun_f=="0"){
		$btn_nm = "記　録";
		$btn_val = "ins_bt";
		$label_nm = "トレーニング記録";
	}else{
		$btn_nm = "修　正";
		$btn_val = "upd_bt";
		$label_nm = "トレーニング記録修正";
	}
	if($_SESSION['mode']=="viewer"){
	    $disabled="disabled";
	}else if($_SESSION['mode']=="custom"){
	    $disabled="";
	}else{
	    $disabled="";
	}

?>
<CENTER>
<ul class='ttl'><li class='ttl'><?php echo $label_nm?></li></ul>
<FORM method="post" name = "form2" action="TOP.php" style="display:inline;">
<CENTER>
<TABLE style='background:#4E9ABE; color: #fff' >
<TR><TD align='right' width='80' height='30'>日付：</TD><TD><INPUT type="date" size="10" name="ymd" maxlength="10" value="<?php echo $ymd_f?>"></TD></TR>
<TR><TD align='right' width='80' height='30'>種目選択：</TD><TD><SELECT name="shu1" id="nonrequired2">
																<OPTION value=''></OPTION>
<?php
																while($row = $result->fetch_assoc()){
																	echo "<OPTION value='".$row["shu"]."'";
																	if($row["shu"] == $row2["shu"]){
																		echo "Selected";
																	}
																	echo ">".$row["shu"]."</OPTION>";
																}
?>
																</SELECT>
														</TD></TR>
<TR><TD align='right' width='80' height='30'>種目追加：</TD><TD><INPUT type="text" id="required2" size="20" name="shu2" maxlength="40" ></TD></TR>
<TR><TD align='right' width='80' height='30'>時間(分)：</TD><TD><INPUT type="tel" name="rep" value="<?php echo $row2["rep"]?>" maxlength="10" style="ime-mode:disabled; width:60px;"></TD></TR>
<TR><TD align='right' width='80' height='30'>距離(ｍ)：</TD><TD><INPUT type="tel" name="rep2" value="<?php echo $row2["rep2"]?>" maxlength="10" style="ime-mode:disabled; width:60px;"></TD></TR>
<TR><TD align='right' width='80' height='30'>カロリー：</TD><TD><INPUT type="tel" name="cal" value="<?php echo $row2["cal"]?>" maxlength="10" style="ime-mode:disabled; width:60px;"></TD></TR>
<TR><TD align='right' width='80' height='30'>メモ：</TD><TD><INPUT type="text" name="memo" value="<?php echo $row2["memo"]?>" maxlength="10"></TD></TR>
<BR>
</TABLE>
    <script type="text/javascript">
    //<![CDATA[
        var elem1 = document.getElementById("required2");
        var elem2 = document.getElementById("nonrequired2");
        function check() {
            if( elem1.value == "" ) {
                elem2.removeAttribute("disabled");
            } else {
            	elem2.selectedIndex = -1;
                elem2.setAttribute("disabled", "disabled");
            }
        }
        check();
        var loop = window.setInterval( check, 500 );
    //]]>
    </script>
<BR>
<button type="submit" style="padding:1em 2em;" onclick="return chk();" name="btn" value="<?php echo $btn_val?>" <?php echo $disabled?> > <?php echo $btn_nm?> </button>
<?php
	if($jun_f=="0"){
	}else{
		echo "<button type='submit' style='padding:1em 2em;'' onclick='return chk3();' name='btn' value='del_bt' ".$disabled."> 削　除 </button>";
	}
?>
</CENTER>
<INPUT type="hidden" name="id" value="<?php echo $id?>">
<INPUT type="hidden" name="pass" value="<?php echo $pass?>">
<INPUT type="hidden" name="edit_date" value="<?php echo $now?>">
<INPUT type="hidden" name="typ" value="1">
<INPUT type="hidden" name="tani" value="2">
<INPUT type="hidden" name="sets" value="1">
<INPUT type="hidden" name="k_ymd" value="<?php echo $ymd_f?>">
<INPUT type="hidden" name="k_jun" value="<?php echo $jun_f?>">
</FORM>
<?php 
}
?>