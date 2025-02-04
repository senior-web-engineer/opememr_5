<?php
include_once("../../globals.php");
?>



<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<link rel=stylesheet href="../themes/style-form-print.css" type="text/css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>

<!-- pop up calendar -->
<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);
</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>

<script language="JavaScript">


</script>
</head>

<div class="container">
<?php





$db_name = "openemr";


$connection = @mysql_connect("localhost", "openemruser", "4050!abc123") or die(mysql_error());
$db = @mysql_select_db($db_name, $connection) or die(mysql_error());

$sql = "SELECT ". 
	"fr.id, fr.pid, fr.subjective, fr.objective, fr.assessment, fr.txgoals".
	", fr.plan, fr.signature, fr.credentials, fr.sig_date, fr.date, fr.provider, fr.timestart, fr.timeend ".
	",pd.fname, pd.lname, pd.mname, pd.dob, pd.ss ".
	"FROM form_soap_pirc AS fr ".
	"JOIN patient_data AS pd ON pd.pid = fr.pid ".
	"WHERE fr.pid = '400'"
	;


$result = @mysql_query($sql,$connection) or die(mysql_error());
while ($row = mysql_fetch_array($result)) 
{
?>

<!-- Info Header -->
<div class="header3">
	<h1>SOAP Notes</h1>
	<div class="info">
<!-- PIRC Info -->
		<div class="pirc-info">
		PIRC Clinic<br>
		817 North Dixie Highway<br>
		Pompano Beach, Florida 33060<br>
		Tel: 954.785.8285 | Fax: 954.928.0040
		</div>

<!-- Form Info -->
		<div class="form-info">
			<span>Client Name:</span><?php echo $row['fname'] . '&nbsp' . $row['mname'] . '&nbsp;' . $row['lname'];?>
			<span>DOB:</span><?php echo $row['dob'];?><br>
			<span>SS#:</span><?php echo $row['ss'];?>
			<span>Treatment Date:</span><?php echo substr($row["date"], 0, 10); ?><br>
			<span>Therapist:</span><?php echo stripslashes($row{"provider"});?><br>
			<span>Time Started:</span><?echo stripslashes($row{"timestart"});?>
			<span>End Time:</span><?echo stripslashes($row{"timeend"});?><br>
		</div>
		<br class="clr">
	</div>
	<br class="clr">
</div>










<!-- Notes -->
	<div class="notes">
<span><strong>Treatment Goals</strong>: </span><?php echo stripslashes($row{"txgoals"});?><br><br>
<h2>Subjective:</h2>
<?php echo $row['subjective'];?></p>
<h2>Objective:</h2> 
<?php echo $row['objective'];?></p> 
<h2>Assessment:</h2>
<?php echo $row['assessment'];?></p> 
<h2>Plan:</h2>
<?php echo $row['plan'];?></p>

</div>
<br class="clr">

	<!-- Signature -->
	<div class="sig">
		<div class="col1">
			Electronically Signed By:<br>
			<span class="u"><?echo $row['signature'];?>, <?echo $row['credentials'];?></span>
			
			</div>
		
		<div class="col3">
			Date:<br>
			<span class="u"><?echo $row['sig_date'];?></span>
			
		</div>
	</div>
	<br class="clr">


<?php

}

?>
