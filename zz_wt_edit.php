<?php ?>

<CENTER>
<ul class='ttl'><li class='ttl'>トレーニング記録</li></ul>
<FORM method="post" name = "form2" action="TOP.php" style="display:inline;">
<CENTER>
<TABLE style='background:#4E9ABE; color: #fff' >
<TR><TD align='right' width='80' height='30'>日付：</TD><TD><INPUT type="date" size="10" name="ymd" maxlength="10" value="<?php echo $enow?>"></TD></TR>
<TR><TD align='right' width='80' height='30'>種目選択：</TD><TD><SELECT name="shu1" id="nonrequired">
																<OPTION value=''></OPTION>
<?php
																while($row = $result->fetch_assoc()){
																	echo "<OPTION value='".$row["shu"]."'";
																	if($row["shu"] == $shu){
																		echo "Selected";
																	}
																	echo ">".$row["shu"]."</OPTION>";
																}
?>
																</SELECT>
														</TD></TR>
<TR><TD align='right' width='80' height='30'>種目追加：</TD><TD><INPUT type="text" id="required" size="20" name="shu2" maxlength="40" ></TD></TR>
<TR><TD align='right' width='80' height='30'>重量：</TD><TD><INPUT type="number" name="weight" maxlength="10" style="ime-mode:disabled; width:60px;"></TD></TR>
<TR><TD align='right' width='80' height='30'>回数：</TD><TD><INPUT type="tel" name="rep" maxlength="10" style="ime-mode:disabled; width:60px;"></TD></TR>
<TR><TD align='right' width='80' height='30'>補回数：</TD><TD><INPUT type="tel" name="rep2" maxlength="10" style="ime-mode:disabled; width:60px;"></TD></TR>
<TR><TD align='right' width='80' height='30'>SET数：</TD><TD><INPUT type="tel" name="sets" maxlength="10" style="ime-mode:disabled; width:60px;"></TD></TR>
<TR><TD align='right' width='80' height='30'>メモ：</TD><TD><INPUT type="text" name="memo" maxlength="10"></TD></TR>
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
            	elem2.selectedIndex = -1;
                elem2.setAttribute("disabled", "disabled");
            }
        }
        check();
        var loop = window.setInterval( check, 500 );
    //]]>
    </script>
<BR>
<button type="submit" onclick="return chk();" name="btn" value="記録"> 記　録 </button>
</CENTER>
<INPUT type="hidden" name="id" value="<?php echo $id?>">
<INPUT type="hidden" name="pass" value="<?php echo $pass?>">
<INPUT type="hidden" name="edit_date" value="<?php echo $now?>">
</FORM>