<?php
//include_once("../../globals.php");
include_once("../../../globals.php");
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

$start_date = "$_REQUEST[start_date]";
$end_date = "$_REQUEST[end_date]";
$patientid = "$_REQUEST[patientid]";



$mysqli = new mysqli($host, $login, $pass, $dbase);

//$db_name = "openemr";


//$connection = @mysql_connect("localhost", "openemruser", "4050!abc123") or die(mysql_error());
//$db = @mysql_select_db($db_name, $connection) or die(mysql_error());

$sql = "SELECT ". 
	"fr.id, fr.pid, fr.diagnosis1, fr.diagnosis2, fr.diagnosis3, fr.diagnosis4, fr.problem, fr.deficit_problems_behavior_addressed, fr.interventions, fr.response_to_intervention".
	", fr.provider_print_name, fr.provider_credentials, fr.provider_signature_date, fr.provider_print_name, fr.time_start, fr.time_end".
	", fm.form_id, fm.form_name, fm.encounter".
	", en.date, en.encounter, en.facility".
	", pd.fname, pd.lname, pd.mname, pd.dob, pd.ss ".
	
	"FROM form_progress_note AS fr ".
	"JOIN forms AS fm ON fm.form_id = fr.id ".
	"AND form_name = 'Progress Note - PSR'  ".
	"JOIN form_encounter AS en ON en.encounter = fm.encounter ". 
	"JOIN patient_data AS pd ON pd.pid = fr.pid ".
	//"JOIN forms AS fm ON fm.form_id = fr.id ". 
	"WHERE fr.pid = '$patientid'".
	" AND en.date between '$start_date' AND '$end_date' "
	;
//echo $sql;
$result = $mysqli -> query ($sql);
//$result = @mysql_query($sql,$connection) or die(mysql_error());
while ($row = mysqli_fetch_array($result))	
{


?>

<!-- Info Header -->
<div class="header3">
	<h1><?php echo $row['form_name'];?></h1>
	<div class="info">
<!-- PIRC Info
		<div class="pirc-info">
		Assurance Of Hope Institute<br>
		5975 W. Sunrise Blvd.<br>
		Suite 115<br>
		Sunrise, Florida 33313<br>
		Tel: 954.368.6856 | Fax: 954.400.7394
		</div>
 -->
<!-- FACILITY Info -->
		<?php 
		$facility = sqlQuery("SELECT name,phone,fax,street,city,state,postal_code FROM facility WHERE facility_code = 'Print'");
		?>
		<div class="facility-info">
		<?php echo $facility['name']?><br>
		<?php echo $facility['street']?><br>
		<?php echo $facility['city']?>, <?php echo $facility['state']?> <?php echo $facility['postal_code']?><br>
		Tel: <?php echo $facility['phone']?> | Fax: <?php echo $facility['fax']?>
		</div>		
<!-- Form Info -->
		<div class="form-info">
			<span>Client Name:</span><?php echo $row['fname'] . '&nbsp' . $row['mname'] . '&nbsp;' . $row['lname'];?>
			<span>DOB:</span><?php echo $row['dob'];?><br>
			<span>SS#:</span><?php echo $row['ss'];?>
			<span>Treatment Date:</span><?php echo substr($row["date"], 0, 10); ?><br>
			<span>Therapist:</span><?php echo stripslashes($row{"provider_print_name"});?>
			<span>POS:</span><?php echo $row['facility'];?><br>
			<span>Time Started:</span><?php echo stripslashes($row{"time_start"});?>
			<span>End Time:</span><?php echo stripslashes($row{"time_end"});?><br>
		</div>
		<br class="clr">
	</div>
	<br class="clr">
</div>




<!-- Notes -->
<div>
	<div class="form-group group">
		<h3>Diagnosis:</h3>
		<div><span><?php echo stripslashes($row{"diagnosis1"});?></span></div>
		<div><span><?php echo stripslashes($row{"diagnosis2"});?></span></div>
		<div><span><?php echo stripslashes($row{"diagnosis3"});?></span></div>
		<div><span><?php echo stripslashes($row{"diagnosis4"});?></span></div>

		<ul>
	</div>
</div>
	<div class="notes">
<h2>List Specific Treatment Plan Deficit/Problems/Behavior Addressed</h2>
		<p><?php echo stripslashes($row{"deficit_problems_behavior_addressed"});?></p>
		<h2>Clinical Intervention</h2>
		<p><?php echo stripslashes($row{"interventions"});?></p>
		<h2>Response to Intervention</h2>
		<p><?php echo stripslashes($row{"response_to_intervention"});?></p>
</div>
<br class="clr">

	<!-- Signature -->
	<div class="sig">
		<div class="col1">
			Electronically Signed By:<br>
			<span class="u"><?php echo $row['provider_print_name'];?>, <?php echo $row['provider_credentials'];?></span>
			
			</div>
		
		<div class="col3">
			Date:<br>
			<span class="u"><?php echo $row['provider_signature_date'];?></span>
			
		</div>
	</div>
	<br class="clr">


<?php

}

?>
