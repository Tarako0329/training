<?php 
function edit_wt($id_f,$jun_f,$ymd_f){
	//ウェイトトレーニング記録画面
	//$id_f		：ユーザID
	//$jun_f	：順番(0の場合は新規)
	//$ymd_f	：実施日
	
	$mysqli = new mysqli(sv, user, pass, dbname);
	//種目リスト
	$sql = "select shu,max(ymd) from tr_log where id in ('".$id_f."','list') and typ = '0' group by shu order by max(ymd) desc,jun desc ";
	$result = $mysqli->query( $sql );
	//実施結果
	$sql2 = "select * from tr_log where id = '".$id_f."' and ymd = '".$ymd_f."' and jun = '".$jun_f."' order by jun desc";
	$result2 = $mysqli->query( $sql2 );
	$row_cnt2 = $result2->num_rows;
	$row2 = $result2->fetch_assoc();
	
	if($jun_f=="0" || $row2["tani"]=="0"){
		$tani1 = "Selected";
	}else{
		$tani2 = "Selected";
	}
	
	if($jun_f=="0"){
	    $style2 = "";
		$btn_val = "ins_bt";
		$btn_nm = "記　録";
		$label_nm = "トレーニング記録";
	}else{
	    $style2 = 'style="position: absolute; left: 55%; top: 85%"';
	    $style3 = 'style="position: absolute; left: 35%; top: 85%"';
		$btn_val = "upd_bt";
		$btn_nm = "更　新";
		$label_nm = "トレーニング記録修正";
	}
	
	if($_SESSION['mode']=="viewer"){
	    $disabled="disabled";
	}else if($_SESSION['mode']=="custom"){
	    $disabled="";
	}else{
	    $disabled="";
	}

  $height = '30';
  $style = 'list-style-type: none; /*margin-left: 13px;*/ position:relative;left:-50%;';
?>

<CENTER>
<ul class='ttl'><li class='ttl' style = 'color:#fff'><?php echo $label_nm?></li></ul>
</CENTER>

<FORM method="post" name = "form2" action="TOP.php" style="display:inline;color:#fff">
<BR>
<ul style = "float:left; position:relative; left:50%;">
    <li class='editsb'>日付</li>
    <li style = "<?php echo $style?>"><INPUT type="date" size="10" name="ymd" maxlength="10" style="font-size:20px;width:200px;height:<?php echo $height?>px;" value="<?php echo $ymd_f?>"></li>
    <li class='editsb'>種目選択</li>
    <li style = "<?php echo $style?>"><SELECT name="shu1" style="font-size:20px;width:200px;height:<?php echo $height?>px;" id="nonrequired">
																<OPTION value=''></OPTION>
<?php
																while($row = $result->fetch_assoc()){
																	echo "<OPTION value='".$row["shu"]."'";
																	if($row["shu"] == $row2["shu"] || $row_cnt2 == 0){
																		echo "Selected";
																		$row_cnt2 = 1;
																	}
																	echo ">".$row["shu"]."</OPTION>";
																}
?>
																</SELECT>
	</li>
    <li class='editsb'>種目追加</li>
    <li style = "<?php echo $style?>"><INPUT type="text" id="required" size="20" name="shu2" maxlength="40" style="font-size:13px;width:200px;height:<?php echo $height?>px;"></li>
    <li class='editsb'>重量</li>
    <li style = "<?php echo $style?>"><INPUT type="number" step="0.01" value="<?php echo $row2["weight"]?>" name="weight" maxlength="8" style="ime-mode:disabled; font-size:13px;width:55px;height:<?php echo $height?>px;"> kg × 
<INPUT type="tel" value="<?php echo $row2["rep"]?>" name="rep" maxlength="3" style="ime-mode:disabled; font-size:13px;width:35px;height:<?php echo $height?>px;">
	<SELECT name="tani" style="height:<?php echo $height?>px;">
	<OPTION value='0' <?php echo $tani1?>>回</OPTION>
	<OPTION value='1' <?php echo $tani2?>>秒</OPTION>
	</SELECT> × <INPUT type="tel" name="sets" value="<?php echo $row2["sets"]?>" maxlength="3" style="ime-mode:disabled; font-size:13px;width:35px;height:<?php echo $height?>px;">set</li>
    <li class='editsb'>補助回数</li>
    <li style = "<?php echo $style?>"><INPUT type="tel" name="rep2" value="<?php echo $row2["rep2"]?>" maxlength="2" style="ime-mode:disabled;font-size:13px; width:40px;height:<?php echo $height?>px;"></li>
    <li class='editsb'>メモ</li>
    <li style = "<?php echo $style?>"><INPUT type="text" name="memo" value="<?php echo $row2["memo"]?>" maxlength="50"style="font-size:13px;width:200px;height:<?php echo $height?>px;"></li>
    <li class='editsb'>　</li>
</ul>

<CENTER>

<script type="text/javascript">
    //<![CDATA[
        var elem1 = document.getElementById("required");
        var elem2 = document.getElementById("nonrequired");
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
    
<button type="submit" <?php echo $style2?> onclick="return chk();" name="btn" value="<?php echo $btn_val?>" <?php echo $disabled?> > <?php echo $btn_nm?> </button>
<?php
	if($jun_f=="0"){
	}else{
		echo "<button type='submit' ".$style3." onclick='return chk3();' name='btn' value='del_bt' ".$disabled."> 削　除 </button>";
	}
?>

</CENTER>
<INPUT type="hidden" name="id" value="<?php echo $id?>">
<INPUT type="hidden" name="pass" value="<?php echo $pass?>">
<INPUT type="hidden" name="edit_date" value="<?php echo $now?>">
<INPUT type="hidden" name="typ" value="0">
<INPUT type="hidden" name="k_ymd" value="<?php echo $ymd_f?>">
<INPUT type="hidden" name="k_jun" value="<?php echo $jun_f?>">
</FORM>
<?php 
}
?>
