<?php 
function edit_taisosiki($id_f,$jun_f,$ymd_f){
	//体組織の記録画面
	//$id_f		：ユーザID
	//$jun_f	：順番(0の場合は新規)
	//$ymd_f	：実施日
	
	$mysqli = new mysqli(sv, user, pass, dbname);

	if($jun_f=="0"){
		$btn_nm = "記　録";
		$btn_val = "w_ins_bt";
		$label_nm = "体組織記録";
	}else{
		$btn_nm = "修　正";
		$btn_val = "w_upd_bt";
		$label_nm = "体組織記録修正";
	}

    $style2 = 'style="position: absolute; left: 60%; top: 85%"';
    $style3 = 'style="position: absolute; left: 30%; top: 85%"';
	
	
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

<FORM method="post" name = "form2" action="recording.php" style="display:inline;color:#fff">
<CENTER>
<TABLE style='background:#4E9ABE; color: #fff' >
<TR><TD align='right' width='80' height='30'>日付：</TD><TD><INPUT type="date" size="10" name="ymd" maxlength="10" value="<?php echo $ymd_f?>"></TD></TR>
<TR><TD align='right' width='80' height='30'>体重：</TD><TD><INPUT type="number" step="0.01"  name="weight" maxlength="5" style="ime-mode:disabled; width:60px;">Kg</TD></TR>
<TR><TD align='right' width='80' height='30'>体脂肪率：</TD><TD><INPUT type="number" step="0.01" name="sibo"  maxlength="5" style="ime-mode:disabled; width:60px;">％</TD></TR>
<TR><TD align='right' width='80' height='30'>予備１：</TD><TD><INPUT type="text" name="yobi1"  maxlength="10" style="ime-mode:disabled; width:60px;"></TD></TR>
<TR><TD align='right' width='80' height='30'>予備２：</TD><TD><INPUT type="text" name="yobi2"  maxlength="10" style="ime-mode:disabled; width:60px;"></TD></TR>
<TR><TD align='right' width='80' height='30'>メモ：</TD><TD><INPUT type="text" name="memo"  maxlength="20"></TD></TR>
<BR>
</TABLE>
<BR>
<BR>

<INPUT type="hidden" name="id" value="<?php echo $id_f?>">
<INPUT type="hidden" name="edit_date" value="<?php echo $now?>">
<INPUT type="hidden" name="typ" value="0">
<INPUT type="hidden" name="k_ymd" value="<?php echo $ymd_f?>">
<INPUT type="hidden" name="k_jun" value="<?php echo $jun_f?>">

<button type="submit" <?php echo $style2?> onclick="return chk();" name="btn" value="<?php echo $btn_val?>" <?php echo $disabled?> > <?php echo $btn_nm?> </button>
<?php
	if($jun_f=="0"){
		echo "<button type='submit' ".$style3." name='btn' value='w_rireki' > 履　歴 </button>";
	}else{
		echo "<button type='submit' ".$style3." onclick='return chk3();' name='btn' value='w_del_bt' ".$disabled."> 削　除 </button>";
	}
?>

</CENTER>
</FORM>
<?php 
}
?>
