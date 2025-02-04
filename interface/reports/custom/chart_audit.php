 <?php
  require_once("../../globals.php");
  include_once("$srcdir/api.inc");
  require_once("$srcdir/patient.inc");
 set_time_limit(600);
?><head>


	<meta charset="utf-8">
	<title>Chart Audit</title>

	<!-- Demo styling -->
	<link href="docs/css/jq.css" rel="stylesheet">

	<!-- jQuery: required (tablesorter works with jQuery 1.2.3+) -->
	<script src="js/jquery.min.js"></script>

	<!-- Pick a theme, load the plugin & initialize plugin -->
	<link href="css/theme.blue.css" rel="stylesheet">
	<script src="js/jquery.tablesorter.min.js"></script>
	<script src="js/jquery.tablesorter.widgets.min.js"></script>
	<script>
	
	
//	
//	
//	$(function(){
//		$('table').tablesorter({
//			widgets        : ['zebra', 'columns'],
//			usNumberFormat : false,
//			sortReset      : true,
//			sortRestart    : true
//		});
//	});
//
$(function() {

  // call the tablesorter plugin
  $("table").tablesorter({
    theme: 'blue',

    // hidden filter input/selects will resize the columns, so try to minimize the change
    widthFixed : true,

    // initialize zebra striping and filter widgets
    widgets: ["zebra", "filter", "columns"],
    
    
    
    usNumberFormat : false,
			sortReset      : true,
			sortRestart    : true,


    // headers: { 5: { sorter: false, filter: false } },

    widgetOptions : {

      // css class applied to the table row containing the filters & the inputs within that row
      filter_cssFilter   : 'tablesorter-filter',

      // If there are child rows in the table (rows with class name from "cssChildRow" option)
      // and this option is true and a match is found anywhere in the child row, then it will make that row
      // visible; default is false
      filter_childRows   : false,

      // if true, filters are collapsed initially, but can be revealed by hovering over the grey bar immediately
      // below the header row. Additionally, tabbing through the document will open the filter row when an input gets focus
      filter_hideFilters : false,

      // Set this option to false to make the searches case sensitive
      filter_ignoreCase  : true,

      // jQuery selector string of an element used to reset the filters
      filter_reset : '.reset',

      // Delay in milliseconds before the filter widget starts searching; This option prevents searching for
      // every character while typing and should make searching large tables faster.
      filter_searchDelay : 300,

      // Set this option to true to use the filter to find text from the start of the column
      // So typing in "a" will find "albert" but not "frank", both have a's; default is false
      filter_startsWith  : false,

      // if false, filters are collapsed initially, but can be revealed by hovering over the grey bar immediately
      // below the header row. Additionally, tabbing through the document will open the filter row when an input gets focus
      filter_hideFilters : false,

      // Add select box to 4th column (zero-based index)
      // each option has an associated function that returns a boolean
      // function variables:
      // e = exact text from cell
      // n = normalized value returned by the column parser
      // f = search filter input value
      // i = column index
      filter_functions : {

        // Add select menu to this column
        // set the column value to true, and/or add "filter-select" class name to header
        // 0 : true,

        // Exact match only
        1 : function(e, n, f, i) {
          return e === f;
        },

        // Add these options to the select dropdown (regex example)
        2 : {
          "A - D" : function(e, n, f, i) { return /^[A-D]/.test(e); },
          "E - H" : function(e, n, f, i) { return /^[E-H]/.test(e); },
          "I - L" : function(e, n, f, i) { return /^[I-L]/.test(e); },
          "M - P" : function(e, n, f, i) { return /^[M-P]/.test(e); },
          "Q - T" : function(e, n, f, i) { return /^[Q-T]/.test(e); },
          "U - X" : function(e, n, f, i) { return /^[U-X]/.test(e); },
          "Y - Z" : function(e, n, f, i) { return /^[Y-Z]/.test(e); }
        },

        // Add these options to the select dropdown (numerical comparison example)
        // Note that only the normalized (n) value will contain numerical data
        // If you use the exact text, you'll need to parse it (parseFloat or parseInt)
        4 : function(e, n, f, i) {
          return e === f;
        },
                 
             5 : {
          ""      : function(e, n, f, i) { return /^[YES]/.test(e); },
          "" : function(e, n, f, i) { return /^[NO]/.test(e); 
          }
                 }
    
                 
                 
      }

    }

  });

});
	
	
	
	
	
	
	
	
	</script>

	<style type="text/css">
	.auto-style1 {
		font-weight: normal;
	}
	</style>

</head>












<table style="width: 100%" class="tablesorter">
<thead> 
		<tr>
			<th class='auto-style1'>PID</th>       
			<th class="auto-style1" data-placeholder="Select a last name">Last Name</th> 
			<th class='auto-style1'>First Name</th>
			<th class='auto-style1'>Insurance</th>
			<th class='auto-style1'>Provider</th>  
			<th class='style4'><B>Last Encounter</B></th>
			<th class='auto-style1'>Days</th>
			<th class='style4' style="width: 95px"><B>Last ASSMT</B></th>
			<th class='auto-style1'>Days</th> 
			<th class='style4'><B>Last T.P</B></th> 
			<th class='auto-style1'>Days</th> 
			<th class='style4'><B>Last T.P.R</B></th> 
			<th class='style4'>Days</th>
			<th class='style4' style="width: 95px"><B>Last C/FARS</B></th>
			<th class='auto-style1'>Days</th>
			<th class='style4'><B>Last Psy. Eval</B></th>
			<th class='auto-style1'>Days</th>
		</tr>
</thead>
<tbody>
<?php
 //require_once("../globals.php");
 //require_once("$srcdir/patient.inc");
 //require_once("$srcdir/formatting.inc.php");
 //require_once("$srcdir/options.inc.php");
 
 // ADDED BY DNUNEZ 8-5-15 TO ALLOW LONGER PHP PROCESSING TIME BEFORE TIMEOUT
 set_time_limit(600);




$provider = "$_REQUEST[providerid]";

if ($provider == '')
	$provider = '*';
$db_name = "openemr";

//echo "<td class='style4'>PID</td>       <td class='style4'>Last Name</td> <td class='style4'>First Name</td><td class='style4'>Provider</td>  <td class='style4'><B>Last Encounter</B></td><td class='style4'> Days Since Last Encounter</td> <td class='style4'><B>Last Treatment Plan</B></td> <td class='style4'>Days Since Last TP</td> <td class='style4'><B>Last Treatment Plan Review</B></td> <td class='style4'>Days Since Last TP/R</td><td class='style4'><B>Last C/FARS</B></td><td class='style4'>Days Since Last C/FARS</td><td class='style4'><B>Last Psych Eval</B></td><td class='style4'> Days Since Last Psych Eval</td></tr>";
$connection = new mysqli($host, $login, $pass, $dbase); 

//$db = @mysql_select_db($db_name, $connection) or die(mysql_error());

//****************************    START FORM **************************************

$sql = "SELECT ". 
	"p.pid, p.lname AS plname, p.fname AS pfname, p.patient_active, p.providerID, ".
	"pr.lname, pr.fname, pr.id ". 
"FROM patient_data AS p ".
	"LEFT JOIN users AS pr ON pr.id = p.providerID ".
//"AS c ".
"WHERE ".
	"p.patient_active = 'YES' ".
	"AND p.providerID = $provider ".
"ORDER BY p.lname ASC "
//"c.pid = '1' ".
//"AND patient_active = 'YES'"
//."LIMIT 0, 20"
;
echo $sql;
$result = $connection -> query ($sql) ;

while ($row = mysqli_fetch_array($result)) 
{
 $pid = $row['pid'];
 $lname = $row['plname'];
 $fname = $row['pfname'];
 $prlname = $row['lname'];
 $prfname = $row['fname'];

 
//echo "<tr><td class='style1'>$pid</td><td class='style1'>$lname</td> <td class='style1'>$fname</td><td class='style1'>";
//SEARCH FOR LAST ENCOUNTER 
$sql_1 = "SELECT ".
	"f.pid, f.encounter, f.form_id, f.form_name, f.deleted,".
	" MAX(e.date) AS edate_1, e.encounter ".
"FROM forms AS f ".
	"JOIN form_encounter AS e ON e.encounter = f.encounter ".
"WHERE f.deleted = '0' AND f.pid = $pid "
."GROUP BY f.pid "
."ORDER BY f.form_id DESC LIMIT 1"

;
//echo $sql_1;
$result_1 = $connection -> query ($sql_1) ;
$row_1 = mysqli_fetch_array($result_1);
 $form_name_1 = $row_1['form_name'];
 $dos_1 = $row_1['edate_1']; 
 $client_insurance = getInsurancePnDataByDate($pid, $dos_1, "primary", "provider", "subscriber_country", policy_number);
 $insurance_info = sqlQuery("SELECT id, name, billing_code_type FROM insurance_companies WHERE id = '$client_insurance[provider]'");
 $insurance = $insurance_info["name"];

//SEARCH FOR LAST ASSESMENT
$sql_6 = "SELECT ".
	"f.pid, f.encounter, f.form_id, f.form_name, f.deleted,".
	" MAX(e.date) AS edate, e.encounter".
	", asm.provider_print_name, asm.id ".
"FROM forms AS f ".
	"JOIN form_encounter AS e ON e.encounter = f.encounter ".
	"LEFT JOIN form_assessment_cmh AS asm ON asm.id = f.form_id ".
"WHERE (f.deleted = '0' AND f.pid = $pid) ".
	"AND f.form_name = 'In-Depth Assessment' "
//	"AND tp.service_code = 'H0032' "
."GROUP BY f.pid "
."ORDER BY f.form_id DESC LIMIT 1"

;
//echo $sql_6;
$result_6 =  $connection -> query ($sql_6) ;
$row_6 = mysqli_fetch_array($result_6);
 //$encounter = $row_2['encounter'];
 $form_id = $row_6['form_id'];
 $form_name_6 = $row_6['form_name'];
 $dos_6 = $row_6['edate']; 

//SEARCH FOR LAST TREATMENT PLAN
$sql_2 = "SELECT ".
	"f.pid, f.encounter, f.form_id, f.form_name, f.deleted,".
	" MAX(e.date) AS edate, e.encounter".
	", tp.provider_print_name, tp.service_code, tp.status, tp.id ".
"FROM forms AS f ".
	"JOIN form_encounter AS e ON e.encounter = f.encounter ".
	"LEFT JOIN form_treatment_plan AS tp ON tp.id = f.form_id ".
"WHERE (f.deleted = '0' AND f.pid = $pid) ".
	"AND f.form_name = 'Treatment Plan' ".
	"AND tp.service_code = 'H0032' ".
	"AND tp.status LIKE '%Ready%' "
."GROUP BY f.pid "
."ORDER BY f.form_id DESC LIMIT 1"

;

$result_2 =  $connection -> query ($sql_2) ;
$row_2 = mysqli_fetch_array($result_2);
 //$encounter = $row_2['encounter'];
 $form_id = $row_2['form_id'];
 $form_name_2 = $row_2['form_name'];
 $dos_2 = $row_2['edate']; 
 //$provider_print_name = $row_2['provider_print_name'];
 //$servicecode = $row_2['servicecode'];
 //$peservicecode = $row_2['peservicecode'];

//SEARCH FOR LAST TREATMENT PLAN REVIEW
//Edited by DNUNEZ 11/12/15 removed (OR f.form_name = 'Treatment Plan - Uploaded') from form.name
$sql_3 = "SELECT ".
	"f.pid, f.encounter, f.form_id, f.form_name, f.deleted,".
	" MAX(e.date) AS edate, e.encounter".
	", tp.provider_print_name, tp.service_code AS servicecode_3, tp.status, tp.id ".
"FROM forms AS f ".
	"JOIN form_encounter AS e ON e.encounter = f.encounter ".
	"LEFT JOIN form_treatment_plan AS tp ON tp.id = f.form_id ".
"WHERE (f.deleted = '0' AND f.pid = $pid) ".
	"AND f.form_name = 'Treatment Plan Review' ".
	"AND tp.service_code = 'H0032TS' ".
	"AND tp.status LIKE '%Ready%' "
."GROUP BY f.pid "
."ORDER BY f.form_id DESC LIMIT 1"

;

$result_3 =  $connection -> query ($sql_3) ;
 $row_3 = mysqli_fetch_array($result_3);
 $dos_3 = $row_3['edate'];
// $servicecode_3 = $row_3['servicecode_3'];
 $form_name_3 = $row_3['form_name'];
 
//SEARCH FOR LAST C/FARS
$sql_4 = "SELECT ".
	"f.pid, f.encounter, f.form_id, f.form_name, f.deleted, cf.status,".
	" MAX(e.date) AS edate, e.encounter". 
	", cf.servicecode AS psservicecode ".
"FROM forms AS f ".
	"JOIN form_encounter AS e ON e.encounter = f.encounter ".
	"LEFT JOIN form_cfars AS cf ON cf.id = f.form_id ".
"WHERE (f.deleted = '0' AND f.pid = $pid) ".
	"AND (f.form_name = 'FARS' OR f.form_name = 'CFARS') ".
	"AND cf.status LIKE '%Ready%' "
."GROUP BY f.pid "
."ORDER BY f.form_id DESC LIMIT 1"

;

$result_4 =$connection -> query ($sql_4) ;
 $row_4 = mysqli_fetch_array($result_4);
 $dos_4 = $row_4['edate'];
// $provider = $row_4['provider'];
// $servicecode_4 = $row_4['psservicecode'];
 $form_name_4 = $row_4['form_name'];


//SEARCH FOR LAST PSYCH EVAL
$sql_5 = "SELECT ".
	"f.pid, f.encounter, f.form_id, f.form_name, f.deleted,".
	" MAX(e.date) AS edate, e.encounter".
	", pe.provider". 
	", pe.servicecode AS psservicecode ".
"FROM forms AS f ".
	"JOIN form_encounter AS e ON e.encounter = f.encounter ".
	"LEFT JOIN form_psych_eval AS pe ON pe.id = f.form_id ".
"WHERE (f.deleted = '0' AND f.pid = $pid) ".
	"AND (f.form_name = 'Psychiatric Evaluation' OR f.form_name = 'Psych Eval - Uploaded') "
."GROUP BY f.pid "
."ORDER BY f.form_id DESC LIMIT 1"

;
$result_5 = $connection -> query ($sql_5) ;
 $row_5 = mysqli_fetch_array($result_5);
 $dos_5 = $row_5['edate'];
// $provider = $row_4['provider'];
// $servicecode_4 = $row_4['psservicecode'];
 $form_name_5 = $row_5['form_name'];


 $now = time();
 $date_1 = date("m-d-Y", strtotime($dos_1));
 $datediff_1 = abs($now - (strtotime($dos_1)));
 $elapsetime_1 = floor($datediff_1/(60*60*24));

 $date_6 = date("m-d-Y", strtotime($dos_6));
 $datediff_6 = abs($now - (strtotime($dos_6)));
 $elapsetime_6 = floor($datediff_6/(60*60*24));

 $date_2 = date("m-d-Y", strtotime($dos_2));
 $datediff_2 = abs($now - (strtotime($dos_2)));
 $elapsetime_2 = floor($datediff_2/(60*60*24));

 $date_3 = date("m-d-Y", strtotime($dos_3));
 $datediff_3 = abs($now - (strtotime($dos_3)));
 $elapsetime_3 = floor($datediff_3/(60*60*24));

 $date_4 = date("m-d-Y", strtotime($dos_4));
 $datediff_4 = abs($now - (strtotime($dos_4)));
 $elapsetime_4 = floor($datediff_4/(60*60*24));

 $date_5 = date("m-d-Y", strtotime($dos_5));
 $datediff_5 = abs($now - (strtotime($dos_5)));
 $elapsetime_5 = floor($datediff_5/(60*60*24));
   
 if ($dos_3 > $dos_2) {
    $bigdate = $dos_3;
}else {
$bigdate = $dos_2;
};
$now = time();
$tp_date = strtotime($bigdate);
$datediff = abs($now - $tp_date);
$elapsetime = floor($datediff/(60*60*24));

 
 
 
 
 
 
 
 
echo "<tr><td class='style1'>$pid</td><td class='style1'><b>$lname</b></td> <td class='style1'><b>$fname</b></td><td class='style1'><b>$insurance</b></td><td class='style1'>$prlname, $prfname</td>";
echo "<td class='style1'><B> ";
if ($date_1 == '12-31-1969')
	echo "None";
	else echo $date_1;//encounter
echo "</B></td><td class='style1'>";
 if ($elapsetime_1 <= 70)
	 echo "<span style=\"color: #00FF00\"> ". $elapsetime_1."</span>";
	else if ($elapsetime_1 > '5000')
	 echo "<span style=\"color: #FF0000\"> *</span>";
	else if ($elapsetime_1 >= 71 && $elapsetime_1 <=90)
	 echo "<span style=\"color: #FFA500\"><strong> ". $elapsetime_1."</strong></span>";
	else if ($elapsetime_1 >= 91)
	 echo "<span style=\"color: #FF0000\"><strong> ". $elapsetime_1."</strong></span>";
//echo " - 001 - Days - $form_name_1</td>";
echo "<td class='style1'><B> ";



if ($date_6 == '12-31-1969')
	echo "None";
	else echo $date_6;//ASSMT

echo "</B></td><td class='style1'>";
if ($elapsetime_6 >= 0 && $elapsetime_6 <= 340)
	 echo "<span style=\"color: #00FF00\"> ". $elapsetime_6. "</span>";
	else if ($elapsetime_6 >= 341 && $elapsetime_6 <=365)
	 echo "<span style=\"color: #FFA500\"><strong> ". $elapsetime_6. "</strong></span>";
	else if ($elapsetime_6 >= 366 && $elapsetime_6 <= 4999)
	 echo "<span style=\"color: #FF0000\"><strong> ". $elapsetime_6. "</strong></span>";
	else if ($elapsetime_6 >= 5000)
	 echo "<span style=\"color: #FF0000\"> *</span>";
//echo " - 002 - Days - $form_name_2</td>"; 
echo "<td class='style1'><B> ";





if ($date_2 == '12-31-1969')
	echo "None";
	else echo $date_2;//TP

echo "</B></td><td class='style1'>";
if ($elapsetime_2 >= 0 && $elapsetime_2 <= 340)
	 echo "<span style=\"color: #00FF00\"> ". $elapsetime_2. "</span>";
	else if ($elapsetime_2 >= 341 && $elapsetime_2 <=365)
	 echo "<span style=\"color: #FFA500\"><strong> ". $elapsetime_2. "</strong></span>";
	else if ($elapsetime_2 >= 366 && $elapsetime_2 <= 4999)
	 echo "<span style=\"color: #FF0000\"><strong> ". $elapsetime_2. "</strong></span>";
	else if ($elapsetime_2 >= 5000)
	 echo "<span style=\"color: #FF0000\"> *</span>";
//echo " - 002 - Days - $form_name_2</td>"; 
echo "<td class='style1'><B> ";

//Checks how old is Treatment Plan before flagging TPR red
if ($elapsetime_2 <= 89){
								if ($date_3 == '12-31-1969')
									echo "None";
									else echo $date_3;//TPR

								echo "</B></td><td class='style1'>";
								if ($elapsetime_3 <= 70)
									 echo "<span style=\"color: #00FF00\"> ". $elapsetime_3."</span>";
										else if ($elapsetime_3 >= 71 && $elapsetime_3 <=89)
									 echo "<span style=\"color: #00FF00\"><strong> ". $elapsetime_3."</strong></span>";
									else if ($elapsetime_3 >= 90 && $elapsetime_3 <= 4999)
									 echo "<span style=\"color: #00FF00\"><strong> ". $elapsetime_3."</strong></span>";
									else if ($elapsetime_3 >= '5000')
									 echo "<span style=\"color: #00FF00\"> *</span>";
								//echo " - 003 - Days - $form_name_3</td>";
								echo "<td class='style1'><B> ";
						}else{
								if ($date_3 == '12-31-1969')
									echo "None";
									else echo $date_3;//TPR
								echo "</B></td><td class='style1'>";
								if ($elapsetime_3 <= 70)
									 echo "<span style=\"color: #00FF00\"> ". $elapsetime_3."</span>";
										else if ($elapsetime_3 >= 71 && $elapsetime_3 <=89)
									 echo "<span style=\"color: #FFA500\"><strong> ". $elapsetime_3."</strong></span>";
									else if ($elapsetime_3 >= 90 && $elapsetime_3 <= 4999)
									 echo "<span style=\"color: #FF0000\"><strong> ". $elapsetime_3."</strong></span>";
									else if ($elapsetime_3 >= '5000')
									 echo "<span style=\"color: #FF0000\"> *</span>";
								//echo " - 003 - Days - $form_name_3</td>";
								echo "<td class='style1'><B> ";
						};

if ($date_4 == '12-31-1969')
	echo "None";
	else echo $date_4;//CFAR - Every 90 Days

echo "</B></td><td class='style1'>";
if ($elapsetime_4 <= 70)
	 echo "<span style=\"color: #00FF00\"> ". $elapsetime_4."</span>";
	else if ($elapsetime_4 >= 71 && $elapsetime_4 <=89)
	 echo "<span style=\"color: #FFA500\"><strong> ". $elapsetime_4."</strong></span>";
	else if ($elapsetime_4 >= 90 && $elapsetime_4 <= 4999)
	 echo "<span style=\"color: #FF0000\"><strong> ". $elapsetime_4."</strong></span>";
	else if ($elapsetime_4 >= '5000')
	 echo "<span style=\"color: #FF0000\"> *</span>";
//echo "- 004 - Days - $form_name_4</td>";
echo "<td class='style1'><B> ";





if ($date_5 == '12-31-1969')
	echo "None";
	else echo $date_5;//Psych Eval

echo "</B></td><td class='style1'>";
if ($elapsetime_5 <= 340)
	 echo "<span style=\"color: #00FF00\"> ". $elapsetime_5."</span>";
	else if ($elapsetime_5 >= 341 && $elapsetime_5 <=365)
	 echo "<span style=\"color: #FFA500\"><strong> ". $elapsetime_5."</strong></span>";
	else if ($elapsetime_5 >= 366 && $elapsetime_5 <= 4999)
	 echo "<span style=\"color: #FF0000\"><strong> ". $elapsetime_5."</strong></span>";
	else if ($elapsetime_5 >= '5000')
	 echo "<span style=\"color: #FF0000\"> *</span>";
//echo " - 005 - Days - $form_name_5</td>";
echo "</td>";
}

//End SetValues Method
 //echo "$display_block_details";
 reset($row);
 reset($row_1);
 reset($row_2);
 reset($row_3);
 reset($row_4);
 reset($row_5);
//****************************    END FORM **************************************
?>
<script>
    document.write('<a href="' + document.referrer + '">Go Back</a>');
</script>
