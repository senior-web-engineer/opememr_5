<!-- Forms generated from formsWiz -->
<?php
include_once("../../globals.php");
?>
<html><head>
<?php html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<link rel=stylesheet href="../../themes/style-form.css" type="text/css">
<style type="text/css">
.style1 {
	font-size: x-small;
}
.style3 {
	text-align: center;
	font-size: x-small;
}
</style>
</head>

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.timeentry.package-1.4.9/jquery.timeentry.js"></script>

<!-- pop up calendar -->
<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);
.style4 {
	font-size: small;
}
.style5 {
	text-align: center;
}
</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>




<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
include_once("$srcdir/api.inc");

//$obj = formFetch("form_soap_pirc", $_GET["id"]);
//$obj = $formid ? formFetch("form_treatment_plan", $formid) : array();
$obj = $formid ? formFetch("form_treatment_plan", $formid) : array();
?>

	<div class="style5">
<span class="title"><strong>Treatment Plan</strong></span>
<span class="title"><strong>Signature Page</strong></span><br><br>

	</div>
<form method=post action="<?echo $rootdir?>/forms/treatment_plan/save.php?mode=update&id=<?echo $_GET["id"];?>" name="SigForm" id="SigForm">


<?php /* From New */ ?>

<?php $res = sqlStatement("SELECT fname,mname,lname,ss,street,city,state,postal_code,phone_home,DOB FROM patient_data WHERE pid = $pid");
$result = SqlFetchArray($res); 

echo $formid;

?>
Encounter#:<?php echo $encounter; ?><input type="hidden" name="encounter" id="encounter" value="<?php echo $encounter; ?>" readonly="readonly">(System use only)

<?echo "hello". $_GET["id"];?>

<INPUT NAME="signatureid" id="signatureid" value="<?php echo $formid;?>">






<script type="text/javascript" src="SigWebTablet.js"></script>

<SCRIPT language="Javascript">


// required for textbox date verification
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';


    var Index;
  	var tmr;	   
	var tmr1;
	var cursig=0;
	    

		      function onReturnSampleSig1()
					{
					 	SetSigCompressionMode(1);
					 	SetTabletState(0, tmr);
					 	var ctx1 = document.getElementById('sigplus1').getContext('2d'); 
					 	   SetDisplayXSize( 500 );
						   SetDisplayYSize( 100 );
						   SetJustifyMode(0);      
						   ClearTablet();
						   tmr = SetTabletState(1, ctx1, 50) || tmr;
     					var mySig1 = "<?php echo  stripslashes($obj{"patient_signature"});?>";
	   						SetSigString(mySig1, ctx1);
	   				}

          	  function onReturnSampleSig2()
					{
					 	SetSigCompressionMode(1);
					 	SetTabletState(0, tmr);
					 	var ctx2 = document.getElementById('sigplus2').getContext('2d'); 
					 	   SetDisplayXSize( 500 );
						   SetDisplayYSize( 100 );
						   SetJustifyMode(0);      
						   ClearTablet();
						   tmr = SetTabletState(1, ctx2, 50) || tmr;
     					var mySig2 = "<?php echo  stripslashes($obj{"guardian_signature"});?>";
	   						SetSigString(mySig2, ctx2);
	   				}
				
			  function onReturnSampleSig3()
					{
					 	SetSigCompressionMode(1);
					 	SetTabletState(0, tmr);
					 	var ctx3 = document.getElementById('sigplus3').getContext('2d'); 
					 	   SetDisplayXSize( 500 );
						   SetDisplayYSize( 100 );
						   SetJustifyMode(0);      
						   ClearTablet();
						   tmr = SetTabletState(1, ctx3, 50) || tmr;
     					var mySig3 = "<?php echo  stripslashes($obj{"provider_signature"});?>";
	   						SetSigString(mySig3, ctx3);
	   				}
	   				
              function onReturnSampleSig4()
					{
					 	SetSigCompressionMode(1);
					 	SetTabletState(0, tmr);
					 	var ctx4 = document.getElementById('sigplus4').getContext('2d');
                           SetDisplayXSize( 500 );
						   SetDisplayYSize( 100 );
						   SetJustifyMode(0);    
						   ClearTablet();
						   tmr = SetTabletState(1, ctx4, 50) || tmr;
					 	var mySig4 = "<?php echo  stripslashes($obj{"supervisor_signature"});?>";
	   						SetSigString(mySig4, ctx4);
                     }
                     
              function onReturnSampleSig5()
					{
					 	SetSigCompressionMode(1);
					 	SetTabletState(0, tmr);
					 	var ctx5 = document.getElementById('sigplus5').getContext('2d'); 
					 	   SetDisplayXSize( 500 );
						   SetDisplayYSize( 100 );
						   SetJustifyMode(0);      
						   ClearTablet();
						   tmr = SetTabletState(1, ctx5, 50) || tmr;
     					var mySig5 = "<?php echo  stripslashes($obj{"physician_signature"});?>";
	   						SetSigString(mySig5, ctx5);
	   				}
	   				
           	//  function onReturnSampleSigAll()
        	//		{
        	//		onReturnSampleSig3();
			//			setTimeout(function(){
			//		onReturnSampleSig4();		
			//			setTimeout(function(){
			//		onReturnSampleSig5();
			//				},1000);
			//			},1000);

        	//		}
        			            
       		  function onReturnSampleSigAll()
        			{
	        			onReturnSampleSig5();
						setTimeout(function(){
							onReturnSampleSig4();		
							setTimeout(function(){
								onReturnSampleSig3();
								setTimeout(function(){
									onReturnSampleSig2();
									setTimeout(function(){
										onReturnSampleSig1();
										
									},1000);
								},1000);
							},1000);
						},1000);
        			}

 							
			function onSign1() 
						{
				 		   disableSignButtons();
						   cursig = 1;
						   SetSigCompressionMode(1);
						   SetTabletState(0, tmr);
						  var ctx = document.getElementById('sigplus1').getContext('2d');
						   SetDisplayXSize( 500 );
						   SetDisplayYSize( 100 );
						   SetJustifyMode(0);      
						   ClearTablet();
						   tmr = SetTabletState(1, ctx1, 50) || tmr;
		    			}
		    			
			function onSign2() 
						{
				 		   disableSignButtons();
						   cursig = 2;
						   SetSigCompressionMode(1);
						   SetTabletState(0, tmr);
						  var ctx = document.getElementById('sigplus2').getContext('2d');
						   SetDisplayXSize( 500 );
						   SetDisplayYSize( 100 );
						   SetJustifyMode(0);      
						   ClearTablet();
						   tmr = SetTabletState(1, ctx2, 50) || tmr;
		    			}
		    			
			function onSign3() 
						{
				 		   disableSignButtons();
						   cursig = 3;
						   SetSigCompressionMode(1);
						   SetTabletState(0, tmr);
						  var ctx = document.getElementById('sigplus3').getContext('2d');
						   SetDisplayXSize( 500 );
						   SetDisplayYSize( 100 );
						   SetJustifyMode(0);      
						   ClearTablet();
						   tmr = SetTabletState(1, ctx3, 50) || tmr;
		    			}
		    			
		    function onSign4() 
						{
				 		   disableSignButtons();
						   cursig = 4;
						   SetSigCompressionMode(1);
						   SetTabletState(0, tmr);
						  var ctx = document.getElementById('sigplus4').getContext('2d');
						   SetDisplayXSize( 500 );
						   SetDisplayYSize( 100 );
						   SetJustifyMode(0);      
						   ClearTablet();
						   tmr = SetTabletState(1, ctx4, 50) || tmr;
					    }
					    
		 function onSign5() 
						{
				 		   disableSignButtons();
						   cursig = 5;
						   SetSigCompressionMode(1);
						   SetTabletState(0, tmr);
						  var ctx = document.getElementById('sigplus5').getContext('2d');
						   SetDisplayXSize( 500 );
						   SetDisplayYSize( 100 );
						   SetJustifyMode(0);      
						   ClearTablet();
						   tmr = SetTabletState(1, ctx5, 50) || tmr;
		    			}
		    			
		   
			function onClear1() 
		    		{
		        	 var ctx1 = document.getElementById('sigplus1').getContext('2d');
					 ctx1.clearRect(0, 0, sigplus1.width, sigplus1.height);
					}

		    function onClear2() 
		    		{
		        	 var ctx2 = document.getElementById('sigplus2').getContext('2d');
					 ctx2.clearRect(0, 0, sigplus2.width, sigplus2.height);
					}

		    function onClear3() 
		    		{
		        	 var ctx3 = document.getElementById('sigplus3').getContext('2d');
					 ctx3.clearRect(0, 0, sigplus3.width, sigplus3.height);
					}
		    function onClear4() 
		    		{
		        	 var ctx4 = document.getElementById('sigplus4').getContext('2d');
					 ctx4.clearRect(0, 0, sigplus4.width, sigplus4.height);
					 }
			function onClear5() 
		    		{
		        	 var ctx5 = document.getElementById('sigplus5').getContext('2d');
					 ctx5.clearRect(0, 0, sigplus5.width, sigplus5.height);
					 }
       
    		function onDoneForm()
					{
		 				document.SigForm.submit();
          	        }


		
			function saveSigs2()
					{
   						if(NumberOfTabletPoints() == 0)
   					{
      				//no signature, exit
      							return;
   					}

   if(cursig == 1)
   {
      document.FORM1.bioSigData.value = GetSigString(); //assign sigstring to hidden field
      document.FORM1.sigStringDataText.value = document.FORM1.bioSigData.value; //show current sigstring data in text area
   }
   if(cursig == 2)
   {
      document.FORM1.bioSigData2.value = GetSigString(); //assign sigstring to hidden field
      document.FORM1.sigStringDataText.value = document.FORM1.bioSigData2.value; //show current sigstring data in text area
   }
   if(cursig == 3)
   {
      document.FORM1.bioSigData3.value = GetSigString(); //assign sigstring to hidden field
      document.FORM1.sigStringDataText.value = document.FORM1.bioSigData3.value; //show current sigstring data in text area
   }
   if(cursig == 4)
   {
      document.FORM1.bioSigData4.value = GetSigString(); //assign sigstring to hidden field
      document.FORM1.sigStringDataText.value = document.FORM1.bioSigData4.value; //show current sigstring data in text area
   }
   if(cursig == 5)
   {
      document.FORM1.bioSigData5.value = GetSigString(); //assign sigstring to hidden field
      document.FORM1.sigStringDataText.value = document.FORM1.bioSigData5.value; //show current sigstring data in text area
   }

      SetImageXSize(500);
      SetImageYSize(100);
      SetImagePenWidth(1);
      GetSigImageB64(SigImageCallback);
}
		
	
		
				
		        function onDone1()
							{
	           					if(NumberOfTabletPoints() == 0)
	   							{
	      							//no signature, exit
	     							 return;
	   							}
	                   				document.SigForm.bioSigData.value = GetSigString();
	                   				document.SigForm.sigStringData.value  = document.SigForm.bioSigData.value;
								$.post("<?php echo $GLOBALS['webroot'] ?>/interface/forms/treatment_plan/save_patient_signature.php?mode=update&id=<?echo $_GET['id'];?>",
										{form_id: $("#signatureid").val() , patient_print_name: $("#patient_print_name").val() , patient_signature_date: $("#patient_signature_date").val(), patient_signature: $('#sigStringData').val()},
											function(data) 
												{
										  			alert (data);
													  alert("Patient Signature Saved");
												}								
										);		
									SetImageXSize(500);
								    SetImageYSize(100);
								    SetImagePenWidth(1);
								    SetJustifyMode(0);
								    GetSigImageB64(SigImageCallback);
						    }

				function onDone2()
							{
	           					if(NumberOfTabletPoints() == 0)
	   							{
	      							//no signature, exit
	     							 return;
	   							}
	                   				document.SigForm.bioSigData.value = GetSigString();
	                   				document.SigForm.sigStringData.value  = document.SigForm.bioSigData.value;
								$.post("<?php echo $GLOBALS['webroot'] ?>/interface/forms/treatment_plan/save_guardian_signature.php?mode=update&id=<?echo $_GET['id'];?>",
										{form_id: $("#signatureid").val() , guardian_print_name: $("#guardian_print_name").val() , guardian_signature_date: $("#guardian_signature_date").val(), guardian_signature: $('#sigStringData').val()},
											function(data) 
												{
													alert (data);
										  			alert("Guardian Signature Saved");
												}								
										);		
									SetImageXSize(500);
								    SetImageYSize(100);
								    SetImagePenWidth(1);
								    SetJustifyMode(0);
								    GetSigImageB64(SigImageCallback);
						    }

				function onDone3()
							{
	           					if(NumberOfTabletPoints() == 0)
	   							{
	      							//no signature, exit
	     							 return;
	   							}
	                   				document.SigForm.bioSigData.value = GetSigString();
	                   				document.SigForm.sigStringData.value  = document.SigForm.bioSigData.value;
								$.post("<?php echo $GLOBALS['webroot'] ?>/interface/forms/treatment_plan/save_provider_signature.php?mode=update&id=<?echo $_GET['id'];?>",
										{form_id: $("#signatureid").val() , provider_print_name: $("#provider_print_name").val() ,provider_credentials: $("#provider_credentials").val() , provider_signature_date: $("#provider_signature_date").val(), provider_signature: $('#sigStringData').val()},
											function(data) 
												{
										  			alert (data);
													  alert("Provider Signature Saved");
												}								
										);		
									SetImageXSize(500);
								    SetImageYSize(100);
								    SetImagePenWidth(1);
								    SetJustifyMode(0);
								    GetSigImageB64(SigImageCallback);
						    }
			
				
					function onDone4()
							{
	                			if(NumberOfTabletPoints() == 0)
	   							{
	      							//no signature, exit
	     							 return;
	   							}
	                   				document.SigForm.bioSigData1.value = GetSigString();
	                   				document.SigForm.sigStringData1.value  = document.SigForm.bioSigData1.value;
								$.post("<?php echo $GLOBALS['webroot'] ?>/interface/forms/treatment_plan/save_supervisor_signature.php?mode=update&id=<?echo $_GET['id'];?>",
									{form_id: $("#signatureid").val() ,supervisor_print_name: $("#supervisor_print_name").val() ,supervisor_credentials: $("#supervisor_credentials").val() , supervisor_signature_date: $("#supervisor_signature_date").val(), supervisor_signature: $('#sigStringData1').val()},
										function(data) 
												{
													alert (data);
										  			alert("Supervisor Signature Saved");
												}								
										);		
									SetImageXSize(500);
								    SetImageYSize(100);
								    SetImagePenWidth(1);
								    SetJustifyMode(0);
								    GetSigImageB64(SigImageCallback);
							}
							
					function onDone5()
							{
	           					if(NumberOfTabletPoints() == 0)
	   							{
	      							//no signature, exit
	     							 return;
	   							}
	                   				document.SigForm.bioSigData.value = GetSigString();
	                   				document.SigForm.sigStringData.value  = document.SigForm.bioSigData.value;
								$.post("<?php echo $GLOBALS['webroot'] ?>/interface/forms/treatment_plan/save_physician_signature.php?mode=update&id=<?echo $_GET['id'];?>",
										{form_id: $("#signatureid").val() , physician_print_name: $("#physician_print_name").val() ,physician_credentials: $("#physician_credentials").val() , physician_signature_date: $("#physician_signature_date").val(), physician_signature: $('#sigStringData').val()},
											function(data) 
												{
										  			alert("Physician Signature Saved");
												}								
										);		
									SetImageXSize(500);
								    SetImageYSize(100);
								    SetImagePenWidth(1);
								    SetJustifyMode(0);
								    GetSigImageB64(SigImageCallback);
						    }
		


		    function Refresh() {
		                
                        document.getElementById('sigplus').refreshEvent();
                                               
		    }
		    
		    function Display() {
		                alert(document.getElementById('sigplus').sigString);		   
		                 }
		                 
		                 
		              

  
	
	function providersignature()
					{
							$.post("<?php echo $GLOBALS['webroot'] ?>/interface/forms/treatment_plan/get-signature.php",
								{provider_print_name: $("#provider_print_name").val() , PIN: $("#clinician_pin").val() },
								function(data) {
								  if($.trim(data).length > 0){
								  		var ctx = document.getElementById('sigplus3').getContext('2d');         
   											SetDisplayXSize( 500 );
   											SetDisplayYSize( 100 );
   											SetJustifyMode(0);
   											ClearTablet();
 										var  tmr = SetTabletState(1, ctx, 50) || tmr;								  	
								  //document.getElementById('sigplus2').sigString =  "<?php echo $signature_result; ?>";
                                    var mySig = data;
                                    SetSigString(mySig, ctx);
   									SetJustifyMode(0);
							 	//alert(data.length);
								 	//alert(data);
								 } else {
								  	alert("Incorrect PIN or Clinician Name");
								  }
								}								
							);
						}

	
	function supervisorsignature()
					{
							$.post("<?php echo $GLOBALS['webroot'] ?>/interface/forms/treatment_plan/get-signature.php",
								{provider_print_name: $("#supervisor_print_name").val() , PIN: $("#supervisor_pin").val() },
								function(data) {
								  if($.trim(data).length > 0){
								  		var ctx2 = document.getElementById('sigplus4').getContext('2d');         
   											SetDisplayXSize( 500 );
   											SetDisplayYSize( 100 );
   											SetJustifyMode(0);
   											ClearTablet();
 										var  tmr1 = SetTabletState(1, ctx2, 50) || tmr1;								  	
								  //document.getElementById('sigplus2').sigString =  "<?php echo $signature_result; ?>";
                                    var mySig2 = data;
                                    SetSigString(mySig2, ctx2);
   									SetJustifyMode(0);
							 	//alert(data.length);
								 	//alert(data);								
								 	 } else {
								  	alert("Incorrect PIN or Supervisor Name");
								  }
								}								
							);
						}
	
	function physiciansignature(){
								$.post("<?php echo $GLOBALS['webroot'] ?>/interface/forms/individualized_tpr/get-signature.php",
									{provider_print_name: $("#physician_print_name").val() , PIN: $("#physician_pin").val() },
									function(data) {
									 if($.trim(data).length > 0){
								  		var ctx2 = document.getElementById('sigplus5').getContext('2d');         
   											SetDisplayXSize( 500 );
   											SetDisplayYSize( 100 );
   											SetJustifyMode(0);
   											ClearTablet();
 										var  tmr1 = SetTabletState(1, ctx2, 50) || tmr1;								  	
								  //document.getElementById('sigplus2').sigString =  "<?php echo $signature_result; ?>";
                                    var mySig2 = data;
                                    SetSigString(mySig2, ctx2);
   									SetJustifyMode(0);
							 	//alert(data.length);
								 	//alert(data);		
									} else {
										alert("Incorrect PIN or Physician Name");
									}
									}								
								);
							}
		
	
function disableSignButtons()
{
document.getElementById("SignBtn").disabled = true; 
document.getElementById("SignBtn1").disabled = true; 
//document.getElementById("Sign3Btn").disabled = true; 
}

function enableSignButtons()
{
document.getElementById("SignBtn").disabled = false; 
document.getElementById("SignBtn1").disabled = false; 
//document.getElementById("Sign3Btn").disabled = false; 
}


function saveSigs()
{
   if(NumberOfTabletPoints() == 0)
   {
      //no signature, exit
      return;
   }

   if(cursig == 1)
   {
      document.FORM1.bioSigData.value = GetSigString(); //assign sigstring to hidden field
      document.FORM1.sigStringDataText.value = document.FORM1.bioSigData.value; //show current sigstring data in text area
   }
   if(cursig == 2)
   {
      document.FORM1.bioSigData2.value = GetSigString(); //assign sigstring to hidden field
      document.FORM1.sigStringDataText.value = document.FORM1.bioSigData2.value; //show current sigstring data in text area
   }
   if(cursig == 3)
   {
      document.FORM1.bioSigData3.value = GetSigString(); //assign sigstring to hidden field
      document.FORM1.sigStringDataText.value = document.FORM1.bioSigData3.value; //show current sigstring data in text area
   }

      SetImageXSize(500);
      SetImageYSize(100);
      SetImagePenWidth(5);
      GetSigImageB64(SigImageCallback);
}


</script>


<table>

<tr>
		<td align="left" style="width: 166px"><strong>Client Name:</strong></td>
		<td style="width: 10%">

 <br><?php echo $result['fname'] . '&nbsp' . $result['mname'] . '&nbsp;' . $result['lname'];?> 
<img src="../../../images/space.gif" width="292" height="1"> <br><br>
</td>
	</tr>
<tr>
		<td align="left" style="width: 166px"><strong>Therapist:</strong></td>
		<td style="width: 10%">


<input type="text" name="provider" id="provider" value="<? echo stripslashes($obj{"provider"});?>" style="width: 185px" readonly="readonly" >

</td>
	</tr>





		
	</table>
	
<br>

<hr style="
width: 610px; height: -12px" class="auto-style1">	
<body onload="onReturnSampleSigAll()">
	<!--PATIENT SIGNATURE-->

<canvas id="sigplus1" width="400" height="80">

</canvas>

<br>
<input id="SignBtn1" name="SignBtn1" type="button" value="Sign"  onclick="javascript:onSign1()"/>&nbsp;&nbsp;&nbsp;&nbsp;
<input id="clear1" name="ClearBtn1" type="button" value="Clear" onclick="javascript:onClear1()"/>&nbsp;&nbsp;&nbsp;&nbsp;
<input id="done1" name="doneBtn1" type="button" value="Done" onclick="javascript:onDone1()"/>&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;

<label class="description" for="patient_print_name"> </label>
			<div>
				Patient Print Name:<input id="patient_print_name" name="patient_print_name"  class="element text medium" type="text" value="<?php echo stripslashes($obj{"patient_print_name"});?>"   /><label> Signature Date:</label>
				<input type='text' size='10' name='patient_signature_date' id='patient_signature_date' value="<?echo stripslashes($obj{"patient_signature_date"});?>" title='<?php xl('yyyy-mm-dd','e'); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
				<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_patient_signature_date' border='0' alt='[?]' style='cursor:pointer;cursor:hand' title='<?php xl('Click here to choose a date','e'); ?>'>
			</div>
			<br>
			<br>

<!--END OF PATIENT SIGNATURE-->
<!-- GUARDIAN SIGNATURE-->
<canvas id="sigplus2" width="400" height="80">

</canvas>

<br>
<input id="SignBtn2" name="SignBtn2" type="button" value="Sign"  onclick="javascript:onSign2()"/>&nbsp;&nbsp;&nbsp;&nbsp;
<input id="clear2" name="ClearBtn2" type="button" value="Clear" onclick="javascript:onClear2()"/>&nbsp;&nbsp;&nbsp;&nbsp;
<input id="done2" name="doneBtn2" type="button" value="Done" onclick="javascript:onDone2()"/>&nbsp;&nbsp;&nbsp;&nbsp;
<label class="description" for="guardian_print_name"> </label>
			<div>
				Guardian Print Name:<input id="guardian_print_name" name="guardian_print_name"  class="element text medium" type="text" value="<?php echo stripslashes($obj{"guardian_print_name"});?>"   />
				<label>Signature Date:</label>
				<input type='text' size='10' name='guardian_signature_date' id='guardian_signature_date' value="<?echo stripslashes($obj{"guardian_signature_date"});?>" title='<?php xl('yyyy-mm-dd','e'); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
				<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_guardian_signature_date' border='0' alt='[?]' style='cursor:pointer;cursor:hand' title='<?php xl('Click here to choose a date','e'); ?>'>
			</div>
		
<!--END OF GUARDIAN SIGNATURE-->

	
	
	
	
  <tr>
    <td height="10" width="500">
<canvas id="sigplus3" width="400" height="80">

</canvas>
<br>
<input id="SignBtn3" name="SignBtn3" type="button" value="Sign"  onclick="javascript:onSign3()"/>&nbsp;&nbsp;&nbsp;&nbsp;
<input id="clear3" name="ClearBtn3" type="button" value="Clear" onclick="javascript:onClear3()"/>&nbsp;&nbsp;&nbsp;&nbsp;
<input id="done3" name="doneBtn3" type="button" value="Done" onclick="javascript:onDone3()"/>&nbsp;&nbsp;&nbsp;&nbsp;




<label class="description" for="provider_print_name"> </label>


			<div>
				Clinician Print Name: <input id="provider_print_name" name="provider_print_name"  class="element text medium" type="text" value="<?php echo stripslashes($obj{"provider_print_name"});?>"   />
				Credentials: 
				<input id="provider_credentials" name="provider_credentials"  class="element text medium" type="text" value="<?php echo stripslashes($obj{"provider_credentials"});?>" style="width: 75px"   />
				<label>Signature Date:</label>
				<input type='text' size='10' name='provider_signature_date' id='provider_signature_date' value="<?echo stripslashes($obj{"provider_signature_date"});?>" title='<?php xl('yyyy-mm-dd','e'); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
				<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_provider_signature_date' border='0' alt='[?]' style='cursor:pointer;cursor:hand' title='<?php xl('Click here to choose a date','e'); ?>'>
				<br>
	</div>
	
				PIN:<input id="clinician_pin" name="clinician_pin"  class="element text medium" type="password" value="" style="width: 35px"   />
				<input type="button" id="btnprovidersignature" value="Load Clinician's Signatures"  onclick="javascript:providersignature()" />
<br>
<label class="description" for="clinician_sig_lock">Lock Clinician's Signature</label>
			
				<input id="clinician_sig_lock" name="clinician_sig_lock" <?php if ($obj{"clinician_sig_lock"} == "on") {echo "checked";};?> class="element text medium" type="checkbox"     />
				<br>
			<hr style="
width: 610px; height: -12px">	
			
			
			<tr>
    <td height="10" width="500">
<!--<object id="sigplus1"  type="application/sigplus" width="500" height="100">
    <param name="onload" value="onReturnSampleSig1" />
</object>
-->
<canvas id="sigplus4" width="400" height="80">

</canvas>

<br>
<input id="SignBtn4" name="SignBtn4" type="button" value="Sign"  onclick="javascript:onSign4()"/>&nbsp;&nbsp;&nbsp;&nbsp;
<input id="clear4" name="ClearBtn4" type="button" value="Clear" onclick="javascript:onClear4()"/>&nbsp;&nbsp;&nbsp;&nbsp;
<input id="done4" name="DoneBtn4" type="button" value="Done" onclick="javascript:onDone4()"/>&nbsp;&nbsp;&nbsp;&nbsp;

<!--
<textarea id="rawdata" ></textarea>
-->
<label class="description" for="supervisor_print_name"> </label>
			<div>
				Supervisor Print Name: <input id="supervisor_print_name" name="supervisor_print_name"  class="element text medium" type="text" value="<?php echo stripslashes($obj{"supervisor_print_name"});?>"   />
				Credentials: 
				<input id="supervisor_credentials" name="supervisor_credentials"  class="element text medium" type="text" value="<?echo stripslashes($obj{"supervisor_credentials"});?>" style="width: 75px" >
				<label>Signature Date:</label>
				<input type='text' size='10' name='supervisor_signature_date' id='supervisor_signature_date' value="<?echo stripslashes($obj{"supervisor_signature_date"});?>" title='<?php xl('yyyy-mm-dd','e'); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
				<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_supervisor_signature_date' border='0' alt='[?]' style='cursor:pointer;cursor:hand' title='<?php xl('Click here to choose a date','e'); ?>'> 
				<br>
			</div>
				
			<div>
				
				<br>
				PIN:<input id="supervisor_pin" name="supervisor_pin"  class="element text medium" type="password" value="" style="width: 35px"   />
				<input type="button" id="btnsupervisorsignature" value="Load Supervisor's Signatures"  onclick="javascript:supervisorsignature()" />
				<br>
				<label class="description" for="supervisor_sig_lock">Lock Supervisor's Signature</label>
<input id="supervisor_sig_lock" name="supervisor_sig_lock" <?php if ($obj{"supervisor_sig_lock"} == "on") {echo "checked";};?> class="element text medium" type="checkbox"     />
<br>

<canvas id="sigplus5" width="400" height="80">

</canvas>
<br>
<!--
<img name="SigImg" id="SigImg" width='500' height='100'>
-->0


<input id="SignBtn5" name="SignBtn5" type="button" value="Sign"  onclick="javascript:onSign5()"/>&nbsp;&nbsp;&nbsp;&nbsp;
<input id="clear5" name="ClearBtn5" type="button" value="Clear" onclick="javascript:onClear5()"/>&nbsp;&nbsp;&nbsp;&nbsp;
<input id="done5" name="DoneBtn5" type="button" value="Done" onclick="javascript:onDone5()"/>&nbsp;&nbsp;&nbsp;&nbsp;
<label class="description" for="supervisor_print_name"> </label>
			<div>
				Physician Print Name: <input id="physician_print_name" name="physician_print_name"  class="element text medium" type="text" value="<?php echo stripslashes($obj{"physician_print_name"});?>"   />
				Credentials: 
				<input id="physician_credentials" name="physician_credentials"  class="element text medium" type="text" value="<?php echo stripslashes($obj{"physician_credentials"});?>" style="width: 79px"   />
				<label>Signature Date:</label>
				<input type='text' size='10' name='physician_signature_date' id='physician_signature_date' value="<?echo stripslashes($obj{"physician_signature_date"});?>" title='<?php xl('yyyy-mm-dd','e'); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
				<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_physician_signature_date' border='0' alt='[?]' style='cursor:pointer;cursor:hand' title='<?php xl('Click here to choose a date','e'); ?>'> 
				<br>
			</div>
				
				PIN:<input id="physician_pin" name="physician_pin"  class="element text medium" type="password" value="" style="width: 35px"   />
				<input type="button" id="btnphysiciansignature" value="Load Physician's Signatures"  onclick="javascript:physiciansignature()" />
				<br><div>
				
<label class="description" for="physician_sig_lock">Lock Physician's Signature</label>
			
				<input id="physician_sig_lock" name="physician_sig_lock" <?php if ($obj{"physician_sig_lock"} == "on") {echo "checked";};?> class="element text medium" type="checkbox"     />
<br>
						
				
<hr style="width: 659px">	
						
				
				<br>
				
				<br>

<INPUT TYPE=HIDDEN NAME="bioSigData">
<INPUT TYPE=HIDDEN NAME="bioSigData1">
<INPUT TYPE=HIDDEN NAME="bioSigData2">
<INPUT TYPE=HIDDEN NAME="sigStringData" id="sigStringData" value="">
<INPUT TYPE=HIDDEN NAME="sigStringData3" id="sigStringData1" value="<?php echo  stripslashes($obj{"supervisor_signature"});?>">
<INPUT TYPE=HIDDEN NAME="sigString">
<INPUT TYPE=HIDDEN NAME="sigImageData">

</form>

<!--LEGACY





	
	   	
	
	
	Signature Date:
   <input type='text' size='10' name='sig_date' id='sig_date'
    value="<?echo stripslashes($obj{"sig_date"});?>"
    title='<?php xl('yyyy-mm-dd','e'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_sig_date' border='0' alt='[?]' style='cursor:pointer;cursor:hand'
    title='<?php xl('Click here to choose a date','e'); ?>'>

<?php /* From New */ ?>

LEGACY-->	


		
	



<script language="javascript">
/* required for popup calendar */
Calendar.setup({inputField:"patient_signature_date", ifFormat:"%Y-%m-%d", button:"img_patient_signature_date"});
Calendar.setup({inputField:"guardian_signature_date", ifFormat:"%Y-%m-%d", button:"img_guardian_signature_date"});
Calendar.setup({inputField:"provider_signature_date", ifFormat:"%Y-%m-%d", button:"img_provider_signature_date"});
Calendar.setup({inputField:"supervisor_signature_date", ifFormat:"%Y-%m-%d", button:"img_supervisor_signature_date"});
Calendar.setup({inputField:"physician_signature_date", ifFormat:"%Y-%m-%d", button:"img_physician_signature_date"});
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
   $(".save").click(function() { top.restoreSession(); document.SigForm.submit(); });
    $(".dontsave").click(function() { location.href='<?php echo $GLOBALS['form_exit_url']; ?>'; });
    $(".printform").click(function() { PrintForm(); });
// disable the Print ability if the form has changed
    // this forces the user to save their changes prior to printing
    $("#img_date_of_signature").click(function() { $(".printform").attr("disabled","disabled"); });
    $("input").keydown(function() { $(".printform").attr("disabled","disabled"); });
    $("select").change(function() { $(".printform").attr("disabled","disabled"); });
    $("textarea").keydown(function() { $(".printform").attr("disabled","disabled"); });
});





</script>

<?php
formFooter();
?>