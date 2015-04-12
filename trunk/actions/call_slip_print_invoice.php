<?php

class actions_call_slip_print_invoice {


	function handle(&$params){

		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");		

		$app =& Dataface_Application::getInstance();
		$query =& $app->getQuery();

		//Load CSS
		echo '<link rel="stylesheet" href="style.css" type="text/css"/>';
		echo '<link rel="stylesheet" href="print.css" type="text/css" media="print" />';
		
		//********************************************
		//*****If one record is directly selected*****
		//********************************************
		if(isset($query['-recordid'])){
			$record =& $app->getRecord();

			//Cost = T), Quoted = QU / SW / PM
			if($record->val('type') == 'TM'){
				echo '<div class="print">' . getHTMLReport($record,'call_slip_invoice_cost') . '</div>';
				$amount = $record->val('invoice_total');
			}
			else{
				echo '<div class="print">' . getHTMLReport($record,'call_slip_invoice_quote') . '</div>';
				$amount = $record->val('quoted_cost');
			}
			
			//Auto Print
			print '<script type="text/javascript">window.print();</script>';

			//Redirect back to the previous page
			$url = $app->url('-action=browse') . '&--msg='.urlencode('Invoice(s) Printed.');

			//Redirect back to previous
			//print '<script type="text/javascript">window.location.replace("'.$url.'");</script>';
			print '<script type="text/javascript">function redirect(){return function(){window.location.replace("'.$url.'");}}setTimeout(redirect(), 10);</script>'; //Include 10us delay to insure that the print dialog pops up.
		}
		
		//******************************************
		//*****If multiple records are selected*****
		//******************************************
		else{
			//Check to make sure user has at least view permissions
			if(get_userPerms('call_slips') == null){
				//Display the error page
				df_display(array("error"), 'call_slip_print_invoice.html');
			}
			
			//Has the print button been pressed? No - display records to print, Yes - print & assign status SNT
			elseif(!isset($query['-status'])){
				//Get All Records with Status = Ready
				$query['status'] = "RDY";
				$records = df_get_records_array("call_slips",$query);

				//Check to see if any records were found
				if(!empty($records)){

					$i = 0;
					foreach ($records as $record){
						$i++;
						$headers[$i]['id'] = $record->val('call_id');
						$headers[$i]['date'] = $record->strval('completion_date');
						$headers[$i]['type'] = $record->display('type');
						$headers[$i]['customer'] = $record->display('customer_id');
						$headers[$i]['site'] = $record->display('site_id');
						$headers[$i]['status'] = $record->display('status');
					}
										
					//Display the page
					df_display(array("headers"=>$headers), 'call_slip_print_invoice.html');
				}
				else{
					//Redirect back to the dashboard
					$msg = 'Nothing to print.';
					$url = 'index.php?--msg='.urlencode($msg);
					
					//Redirect
					echo '<meta http-equiv="refresh" content="0; url='.$url.'" />';
				}

			}
			elseif($query['-status'] == 'selected'){ //Invoices have been selected and the Print button has been pressed.
				//Get All Records with Status = Ready
				$query['status'] = "RDY";
				$records = df_get_records_array("call_slips",$query);
	
				//After printing - use this URL to redirect to the print summary.
				$url = 'index.php?-action=call_slip_print_invoice&-status=printed&printed=';

				foreach ($records as $record){
					//Parse through records to see which were selected
					if(isset($_GET[$record->val('call_id')]) && $_GET[$record->val('call_id')]=="on"){
						//Cost = TM, Quoted = QU / SW / PM
						if($record->val('type') == 'TM'){
							echo '<div class="print">' . getHTMLReport($record,'call_slip_invoice_cost') . '</div>';
							$amount = $record->val('invoice_total');
						}
						else{
							echo '<div class="print">' . getHTMLReport($record,'call_slip_invoice_quote') . '</div>';
							$amount = $record->val('quoted_cost');
						}

						$url .= $record->val('call_id').',';
					}
					
				}
				
//				$msg = 'Invoice(s) Printed.';
//				$url .= '&--msg='.urlencode($msg);
				
				//Auto Print
				print '<script type="text/javascript">window.print();</script>';
				print '<script type="text/javascript">function redirect(){return function(){window.location.replace("'.$url.'");}}setTimeout(redirect(), 10);</script>'; //Include 10us delay to insure that the print dialog pops up before redirecting.
				
				//Redirect back to previous - javascript like above is causing the print window to not appear for some reason
				//echo '<meta http-equiv="refresh" content="0; url='.$url.'" />';
			}
			elseif($query['-status'] == 'printed'){ //Invoice have been selected and the Print button has been pressed.
				$printed_records = explode(',',substr($query['printed'],0,-1)); //convert passed string into an array (exclude last comma, which will always be there)

				$i = 0;
				foreach($printed_records as $printed_record){
					$record = df_get_record('call_slips',array('call_id'=>$printed_record));
					$i++;
					$headers[$i]['id'] = $record->val('call_id');
					$headers[$i]['date'] = $record->strval('completion_date');
					$headers[$i]['type'] = $record->display('type');
					$headers[$i]['customer'] = $record->display('customer_id');
					$headers[$i]['site'] = $record->display('site_id');
					$headers[$i]['status'] = $record->display('status');
				}
				
				//Display the page
				df_display(array("headers"=>$headers,"status"=>"printed"), 'call_slip_print_invoice.html');
			}
			else{
				//Something went wrong
			}
		}
		
	}
}

?>
