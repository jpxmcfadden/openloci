<?php

/****

Call Slip Status State Table

Status			  |			Options			| 	 Editable

Not Complete		Complete/Void					Y
Complete			Create Billing / Void			Y
Billing Pending		Recall Billing					N
Billed				Credit							N
Reversed/Credited	---								N
VOID				---								N

***/


class tables_call_slips {

	//Class Variables
	private $cs_modify_inventory = array(); //Create a class variable to store the values for modifying the inventory

	//Permissions
		function getPermissions(&$record){
			//Check if the user is logged in & what their permissions for this table are.
			if( isUser() ){
				$userperms = get_userPerms('call_slips');
				if($userperms == "view")
					return Dataface_PermissionsTool::getRolePermissions("READ ONLY"); //Assign Read Only Permissions
				elseif($userperms == "edit" || $userperms == "post"){
					$perms = Dataface_PermissionsTool::getRolePermissions(myRole()); //Assign Permissions based on user Role (typically USER)
						unset($perms['delete']);
					return $perms;
				}
			}

			//Default: No Access
			return Dataface_PermissionsTool::NO_ACCESS();
		}

		function __field__permissions($record){
		
			if ( isset($record) )
				switch($record->val('status')){
					case "BPD":
					case "BLD":
					case "CRD":
					case "VOID":
						return array('edit'=>0);
				}
		}

		//Remove the "edit" tab, if applicable. --- Field permissions are set to 'edit'=>0 anyway, but since changing "status" required general edit access via getPermissions(), which then automatically shows the tab - this needs to be visually disabled.
		function init(){
			$app =& Dataface_Application::getInstance();
			$query =& $app->getQuery();
			$record =& $app->getRecord();
			
			//Only on the 'view' page. Otherwise, causes issues with looking at the entire table (i.e. user sees a blank page).
			//If record exists & the status is set such that the record shouldn't be editable.
			//Make sure table is "call slips" otherwise screws up other tables that call call_slips.
			if($query['-action'] == 'view' && $query['-table'] == 'call_slips')
				switch($record->val('status')){
					case "BPD":
					case "BLD":
					case "CRD":
					case "VOID":
					echo "<style>#record-tabs-edit{display: none;}</style>";
				}
		}
		
		function status__permissions(&$record){
			//Check permissions & if allowed, set edit permissions for "account_status"
			if(get_userPerms('call_slips') == "edit" || get_userPerms('call_slips') == "post")
				return array("edit"=>1);
		}
		
		function ar_billing_id__permissions(&$record){
			//Check permissions & if allowed, set edit permissions for "account_status"
			if(get_userPerms('call_slips') == "edit" || get_userPerms('call_slips') == "post")
				return array("edit"=>1);
		}

		function credit__permissions(&$record){
			//Check permissions & if allowed, set edit permissions for "account_status"
			if(get_userPerms('call_slips') == "edit" || get_userPerms('call_slips') == "post")
				return array("edit"=>1);
		}


		function rel_call_slip_purchase_orders__permissions(&$record){
			return array(
				'add new related record'=>0,
				'add existing related record'=>0,
				'remove related record'=>0,
				'delete related record'=>0
			//	'reorder_related_records'=>1
			);
		}

		function rel_time_logs__permissions(&$record){
			//Set timelog edit permissions to use the timelog table's permission settings (unset related record permissions)
			$perms = &Dataface_PermissionsTool::getRolePermissions(myRole());
			unset($perms['edit related records']);
			unset($perms['delete related record']);
				
			//If call slip is no longer incomplete, don't allow entry modification
			if(isset($record))
			if($record->val('status') != 'NCO' && $record->val('status') != 'NCP' && $record->val('status') != 'CMP'){
				return array(
					'add new related record'=>0,
					'add existing related record'=>0,
					'remove related record'=>0,
					'delete related record'=>0
				);
			}
		}


	//Set the record title
		function getTitle(&$record){
			//Pull the site address
			$customer_record = df_get_record('customer_sites', array('site_id'=>$record->val('site_id')));

			return "Call Slip # " . $record->strval('call_id') . " - " . $customer_record->strval('site_address');
		}

		function titleColumn(){
			return 'CONCAT("Call Slip # ",call_id)';
		}


	//Default settings for fields
		function call_datetime__default() {
			return '<div class="formHelp">Call Date/Time will be assigned when the record is first saved.</div>';
		   //return date('Y-m-d g:i a');
		}

		function call_id__default() {
		   return "----<div class=\"formHelp\">A Call ID will be assigned after the first SAVE.</div>";		
		}

		function charge_consumables__default() {
			$admin_record = df_get_record('call_slip_admin',array('call_slip_admin_id'=>"=1")); //Get the admin record
			return $admin_record->val('charge_consumables_default');
		}

		function charge_fuel__default() {
			$admin_record = df_get_record('call_slip_admin',array('call_slip_admin_id'=>"=1")); //Get the admin record
			return $admin_record->val('charge_fuel_default');
		}

	//Visual Things
	
		function block__before_record_actions(){
			$app =& Dataface_Application::getInstance(); 
			$record =& $app->getRecord();

			//Add link to print invoice (if appropriate)
			/*if($record->val('status') == 'RDY' || $record->val('status') == 'SNT' || $record->val('status') == 'PPR'){
				echo '	<div class="dataface-view-record-actions">
							<ul>
								<li id="call_slip_invoice" class="plain">
									<a class="" id="call_slip_invoice-link" href="'.$app->url('-action=call_slip_print_invoice').' title="" data-xf-permission="view">
										<img id="call_slip_invoice-icon" src="images/report_icon.png" alt="Print Invoice">
										<span class="action-label">Print Invoice</span>
									</a>
								</li>
							</ul>
						</div>';
						
				//If printing invoice for the first time, give the user the confirm dialog.
				if($record->val('status') == 'RDY')
					echo '	<script>
								jQuery("#call_slip_invoice").click(function(){
									return confirm("NOTICE: You are about to print the invoice for this call slip. Once printed this call slip will no longer be editable & an accounts receivable entry will automatically be created. Do you wish to proceed?");
								});
							</script>';
				
			}
			*/
			
			//Add link to print call slip (if appropriate)
			if($record->val('status') == 'NCO' || $record->val('status') == 'NCP'){
				echo '	<div class="dataface-view-record-actions">
							<ul>
								<li id="print_call_slip" class="plain">
									<a class="" id="print_call_slip-link" href="'.$app->url('-action=call_slip_print_call_slip').' title="" data-xf-permission="view">
										<img id="call_slip_invoice-icon" src="images/report_icon.png" alt="Print Call Slip">                   <span class="action-label">Print Call Slip</span>
									</a>
								</li>
							</ul>
						</div>';
			}
		}
	
		//Hide the "type" field if the record type is for 'Preventative Maintenance' or 'Credit'
		function block__before_type_widget(){
			$app =& Dataface_Application::getInstance(); 
			$record =& $app->getRecord();
			$query =& $app->getQuery();
		
			if($query['-action'] != 'new'){ //We do this b/c when getRecord() is used on a "new record" it returns the data from the last saved record.
				if($record->val('type') == "PM"){ //Check if type == "PM" and if so replace the dropdown menu with static text.
					echo 'Preventative Maintenance<style>#type {display:none;}</style>';
				}
				elseif($record->val('type') == "CR"){ //Check if type == "CR" and if so replace the dropdown menu with static text.
					echo 'Credit<style>#type {display:none;}</style>';
				}
			}
		}
		
		function valuelist__type_list(){
			return array(
				'TM'=>'Time & Material',
				'QU'=>'Quoted Repairs',
				'SW'=>'Service Warranty',
				'NC'=>'No Charge',
			);
		}

		//Display PM as "Preventative Maintenance"
		function type__display(&$record){
			//Pull the "type" valuelist
//			$list = $record->_table->_valuelistsConfig['type_list'];

			//Add PM and CR to the list
			$list["PM"]="Preventative Maintenance";
			$list["CR"] = "Credit";

			$list["TM"] = "Time & Material";
			$list["QU"] = "Quoted Repairs";
			$list["SW"] = "Service Warranty";
			$list["NC"] = "No Charge";

			
			//Return the type as per the list.
			return $list[$record->val('type')];
		}

		//Display datetime format as: "Month Day, Year - Hour(12):Minutes AM/PM" or "Month Year" for PMs
		function call_datetime__display($record) {
			if($record->val('type') == "PM")
				return date('F Y', strtotime($record->strval('call_datetime')));
			return date('F d, Y - g:i A', strtotime($record->strval('call_datetime')));
			//return date('Y-m-d g:i A', strtotime($record->strval('call_datetime')));
	   }
	   
		//Pretty things up a bit.
		function block__before_inventory_widget() { echo '<div style="width: 600px">'; }
		function block__after_inventory_widget() { echo "</div>"; }	

		function block__before_purchase_orders_widget() { echo '<div style="width: 600px">'; }
		function block__after_purchase_orders_widget() { echo "</div>"; }

		function block__before_additional_materials_widget() { echo '<div style="width: 600px">'; }
		function block__after_additional_materials_widget() { echo "</div>"; }
		
		


	//Add attitional details to the view tab
	function section__billing(&$record){

	$childString = "";

	
		//Hours Worked
			$childString .= '<b><u>Hours</u></b><br><br>';
			$childString .= '<table class="view_add"><tr>
								<th>Employee</th>
								<th>Arrive Time</th>
								<th>Depart Time</th>
								<th>Hours</th>
								<th>Rate</th>
								<th>Total</th>
							</tr>';

			$total_hours_sale = 0;

			$timelog_records = $record->getRelatedRecords('time_logs');
			foreach ($timelog_records as $cs_tl){
				//Pull the employee name out of the 'employees' table
				$employee_record = df_get_record('employees', array('employee_id'=>$cs_tl['employee_id']));

				//Convert arrive / depart times, & calculate 'hours'
				$arrive = Dataface_converters_date::datetime_to_string($cs_tl['start_time']);
				$depart = Dataface_converters_date::datetime_to_string($cs_tl['end_time']);
				$hours = number_format(((strtotime($depart) - strtotime($arrive)) / 3600),1);

				//Get "rate" details
				$rate_record = df_get_record('rates', array('rate_id'=>$cs_tl['rate_id']));
				$rate = $rate_record->val($cs_tl['rate_type']);
				
				$childString .= '<tr><td>' . $employee_record->display('first_name') . ' ' . $employee_record->display('last_name') .
								'</td><td>' . $arrive .
								"</td><td>" . $depart .
								"</td><td>" . $hours .
								"</td><td>" . $rate .
								"</td><td>$" . number_format($rate * $hours,2) .
								"</td></tr>";
				$total_hours_sale += $rate * $hours;
			}
			
			$childString .= '<tr><td></td><td></td><td></td><td></td><td></td><td>$<b>' . number_format($total_hours_sale,2) . '</b></td></tr>';
			$childString .= '</table><br>';
			
		//Materials
			$childString .= '<b><u>Materials</u></b><br><br>';
			$childString .= '<table class="view_add"><tr>
								<th>PO# / Inventory</th>
								<th>Item</th>
								<th>Quantity</th>
								<th>Purchase Price</th>
								<th>Markup</th>
								<th>Sale Price</th>
								<th>Total Sale</th>
							</tr>';

			$total_materials_sale = 0;

			$customerRecord = df_get_record('customers', array('customer_id'=>$record->val('customer_id')));
			$markupRecords = df_get_records_array('customer_markup_rates', array('markup_id'=>$record->val('markup')));
			
			
			//Purchase Orders
			$purchaseorderRecords = $record->getRelatedRecords('call_slip_purchase_orders');
			foreach ($purchaseorderRecords as $cs_pr){
				$purchase_price = $cs_pr['purchase_price'];
				
				//Calculate sale price based on customer markup - Normal
				foreach ($markupRecords as $mr) {
					if($mr->val('to') == null)
						$no_limit = true;
						
					if( ($purchase_price >= $mr->val('from')) && ($purchase_price <= $mr->val('to') || $no_limit == true) ){
						$markup = $mr->val('markup_percent');
						break;
					}
				}
					
				$sale_price = number_format($purchase_price * (1+$markup),2,".","");
				$markup = $markup*100 . "%";
					
				$sale_price_original = "";
				$sale_color = "";
				
				//If however, the sale price is set in the PO record, re-calculate based on the overide
				$sale_price_overide = $cs_pr['sale_price'];
				if($sale_price_overide){
					//$sale_price_original = '<span style="color: black;">[$' . $sale_price . ']</span>'; //To show auto-calculated sale price inline.
					$sale_price = $sale_price_overide;
					$sale_color = "color: royalblue;";

					if($purchase_price > $sale_price){
						$markup = '<span style="color: crimson;">' . number_format(-100 * ($purchase_price / $sale_price - 1), 0) . "%</span>";
					}
					elseif($purchase_price != 0)
						$markup = number_format(100 * ($sale_price / $purchase_price - 1), 0) . "%";
					else
						$markup = "---";
				}

				//If quantity_used is set in the PO record (overide), use this value for the quantity, otherwise use the purchased quantity.
				if(isset($cs_pr['quantity_used'])){
					$quantity = $cs_pr['quantity_used'];
					$quantity_original = '<span style="color: black;">[' . $cs_pr['quantity'] . ']</span>';
					$quantity_color = "color: royalblue;";
				}
				else{
					$quantity = $cs_pr['quantity'];
					$quantity_original = "";
					$quantity_color = "";
				}
			
				$subtotal_sale = number_format($sale_price * $quantity,2);
				$total_materials_sale += number_format($sale_price * $quantity,2,".","");

				$childString .= '<tr><td style="text-align: right">' . (($cs_pr['post_status'] != "Posted") ? '[unposted] ' : "") . 'PO# - S' . $cs_pr['purchase_id'] .
								'</td><td>' . $cs_pr['item_name'] .
								'</td><td style="text-align: right; ' . $quantity_color . '">' . $quantity_original . " " . $quantity .
								'</td><td style="text-align: right">$' . $cs_pr['purchase_price'] .
								'</td><td style="text-align: right; ' . $markup_color. ' ">' . $markup . 
								'</td><td style="text-align: right; ' . $sale_color. '">' . $sale_price_original . ' $' . number_format($sale_price,2) .
								'</td><td style="text-align: right;">$' . $subtotal_sale .
								'</td></tr>';

			}
		
			//Inventory
			$inventoryRecords = $record->getRelatedRecords('call_slip_inventory');
			foreach ($inventoryRecords as $cs_ir){
				//Pull the item name / cost out of the 'inventory' table
				$inventory_record = df_get_record('inventory', array('inventory_id'=>$cs_ir['inventory_id']));
				
				$purchase_price = $cs_ir['purchase_price'];
				$sale_price = $cs_ir['sale_price']; //Pull sale price from record (will likely be null)

				//If the sale price is set in the callslip inventory record, calculate based on the overide
				if(isset($sale_price)){
					if($purchase_price < $sale_price)
						$markup = number_format(100 * ($sale_price / $purchase_price - 1), 0) . "%";
					elseif($purchase_price > $sale_price)
						$markup = '<span style="color: crimson;">' . number_format(-100 * ($purchase_price / $sale_price - 1), 0) . "%</span>";
					else
						$markup = "---";

					$sale_color = "color: royalblue;";
				}
				//Otherwise, check if the inventory sale overide has been set, if so, calculate based on it
				elseif($inventory_record->val('sale_method')=="overide"){
					$sale_price = $inventory_record->val('sale_overide');
					if($purchase_price < $sale_price)
						$markup = number_format(100 * ($sale_price / $purchase_price - 1), 0) . "%";
					elseif($purchase_price > $sale_price)
						$markup = '<span style="color: crimson;">' . number_format(-100 * ($purchase_price / $sale_price - 1), 0) . "%</span>";
					else
						$markup = "---";

					$sale_color = "color: seagreen;";
				}
				//Otherwise (most likely), calculate based on set customer markup rate
				else{ 
					foreach ($markupRecords as $mr) {
						if($mr->val('to') == null)
							$no_limit = true;
						
						if( ($purchase_price >= $mr->val('from')) && ($purchase_price <= $mr->val('to') || $no_limit == true) ){
							$markup = $mr->val('markup_percent');
							break;
						}
					}
					
					$sale_price = number_format($purchase_price * (1+$markup),2,".","");
					$markup = $markup*100 . "%";
					
					$sale_color = "";
				}				
				

				$subtotal_sale = number_format($sale_price * $cs_ir['quantity'],2);
				$total_materials_sale += number_format($sale_price * $cs_ir['quantity'],2,".","");

				$childString .= '<tr><td style="text-align: right">Inventory' .
							//	'</td><td>' . $inventory_record->display('item_name') .
								'</td><td> ' . $inventory_record->val("item_name") .
								'</td><td style="text-align: right">' . $cs_ir['quantity'] .
								'</td><td style="text-align: right">$' . $purchase_price .
								'</td><td style="text-align: right; ' . $markup_color. '">' . $markup .
								'</td><td style="text-align: right; ' . $sale_color. '">$' . $sale_price .
								'</td><td style="text-align: right;">$' . $subtotal_sale .
								'</td></tr>';

			}
			
			//Additional Materials
			$additional_materialsRecords = $record->getRelatedRecords('call_slip_additional_materials');
			foreach ($additional_materialsRecords as $cs_amr){
				
				$subtotal_sale = number_format($cs_amr['quantity'] * $cs_amr['sale_price'],2);
				$total_materials_sale += number_format($cs_amr['quantity'] * $cs_amr['sale_price'],2,".","");
				
				$childString .= '<tr><td style="text-align: right">Additional Items' .
								'</td><td>' . $cs_amr['item_name'] .
								'</td><td style="text-align: right">' . $cs_amr['quantity'] .
								'</td><td style="text-align: right">---' . //$purchase_price .
								'</td><td style="text-align: right;">---' . //$markup_color. '">' . $markup .
								'</td><td style="text-align: right;">$' . $cs_amr['sale_price'] .
								'</td><td style="text-align: right;">$' . $subtotal_sale .
								'</td></tr>';

			}			
			
			$childString .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td>' .
							'<td style="text-align: right">$<b>' . number_format($total_materials_sale,2) . '</b></td>' .
							'</td></tr>';
			$childString .= '</table><br>';
			
			
			//KEY
			//$childString .= '<b>Key</b>';
			//$childString .= '<table class="view_add">
			//					<tr>
			//						<td style="text-align: right; background-color: lightpink;">Overide (From Call Slip)</td>
			//						<td style="text-align: right; background-color: lightsteelblue;">Overide (From Inventory)</td>
			//					</tr>
			//				</table>';


			//Additional Charges (consumables / fuel)
			if($record->val('charge_consumables') || $record->val('charge_fuel')){
				$childString .= "<br><b><u>Additional Charges</u></b><br><br>";
				$childString .= '<table class="view_add">';
				$childString .= "<tr><th>Charge Type</th><th>Amount</th></tr>";

				if($record->val('charge_consumables'))
					$childString .= "<tr><td>Consumables</td><td>$" . $record->val('charge_consumables') . "</td></tr>";
				
				if($record->val('charge_fuel') )
					$childString .= "<tr><td>Fuel</td><td>$" . $record->val('charge_fuel') . "</td></tr>";
					
				$childString .= '</table>';
			}

			//Total
			if($record->val('type') == "NC")
				$total_charge_to_customer = "0.00 (No Charge)";
			elseif($record->val('type') == "SW")
				$total_charge_to_customer = "0.00 (Service Warranty)";
			else
				$total_charge_to_customer = number_format($total_hours_sale+$total_materials_sale+$record->val('charge_consumables')+$record->val('charge_fuel'),2);
				
			$childString .= '<br><br><u><b>Total:</b> $<b>' . $total_charge_to_customer . '</b></u>';


		return array(
			'content' => "$childString",
			'class' => 'main',
			'label' => 'Billing Details',
			'order' => 10
		);
	}

	
	function section__status(&$record){
		//Check permissions & if allowed, show Change Status button
		if(get_userPerms('call_slips') == "edit" || get_userPerms('call_slips') == "post"){

			$app =& Dataface_Application::getInstance(); 
			$query =& $app->getQuery();
			$childString = '';

			//If the button in the 'Change Status' section has been pressed.
			//Because both the $_GET and $query will be "" on a new record, check to insure that they are not empty.
			if(($_GET['-status_change'] == $query['-recordid']) && ($query['-recordid'] != "")){
				$status_button = $_GET['status_button'];

				switch($status_button){
					case("Void Call Slip"): //Pressed: Void Call Slip
						$record->setValue('status',"VOID"); //Set status to Complete.
						$status_msg = "Call Slip has been Voided";
						break;
					case("Complete Job"): //Pressed: Complete Job
						$record->setValue('status',"CMP"); //Set status to Complete.
						if($record->val('completion_date') == "")
							$record->setValue('completion_date',date("Y-m-d")); //Set Job Completion Date.
						$status_msg = "Call Slip has been set to Job Complete";
						break;
					case("Create Billing"): //Pressed: Create Billing
						$billing_id = $this->create_billing($record); //Create Billing.
						if($billing_id != -1){ //Check for errors with saving AR entry
							$record->setValue('ar_billing_id',$billing_id);  //Set Status to Billing Pending & the associated AR entry.
							$record->setValue('status',"BPD");
							if($billing_id < 0) //Check for other errors
								$status_msg .= 'WARNING: Billing Entry has been Created, However, the Customer Balance could not be Updated. Manual Correction may be needed. (ERROR ' . $billing_id . ')';
							else
								$status_msg = 'Billing Entry has been Created.';
						}
						else
							$status_msg = 'An error occured while trying to Create a Billing Entry. You may not have the proper permissions. (ERROR '. $billing_id .')';
						break;
					case("Unbill"): //Pressed: Unbill
						$unbill = $this->unbill_cs($record); //Create a Credit Call Slip, and Reverse the billing entry on this one in Accounts Receivable.
						if($unbill == 1){
							$record->setValue('status',"CMP");
							$record->setValue('ar_billing_id',null);
							$status_msg = "Billing Entry has been removed.";
						}
						else
							$status_msg = 'An error occured while trying to Unbill the Call Slip. You may not have the proper Accounts Receivable permissions. (ERROR '. $unbill .')';
						break;
					case("Credit Invoice"): //Pressed: Credit Invoice
						$credit_call_id = $this->create_credit_cs($record); //Create a Credit Call Slip, and Reverse the billing entry on this one in Accounts Receivable.
						$record->setValue('status',"CRD");
						$record->setValue('credit',"Credited (Call ID " . $credit_call_id . ")");
						$status_msg = "Call Slip has been Reversed, and a Credit Call Slip has been created.";
						break;
				}
				
				//Save record
				$res = $record->save(null, true); //Save Record w/ permission check.
				//$res = $record->save(); //Save Record w/o permission check. - Temporary quick fix, should modify permissions instead
				
				//Check for errors.
				if ( PEAR::isError($res) ){
					// An error occurred
					//throw new Exception($res->getMessage());
					$msg = '<input type="hidden" name="--error" value="Unable to change status. This is most likely because you do not have the required permissions.'.$status_msg.'">';
				}
				else {
						$msg = '<input type="hidden" name="--msg" value="'.$status_msg.'">';
					//else 
					//	$msg = '<input type="hidden" name="--error" value="Something Broke: Status='.$record->val('status').'">';
				}

				
				$childString .= '<form name="status_change">';
				$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
				$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
				$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';

				$childString .= $msg;

				$childString .= '</form>';
				$childString .= '<script language="Javascript">document.status_change.submit();</script>';
			}
			//Show the appropriate buttons
			else{
				//$childString .= '<form onsubmit="return confirm(\'Are you sure you want to submit this form?\');">'; //This works for confirm popup, but works awkwardly with submithandler.js (i.e. if you hit cancel, it still counts as a "press")
				$childString .= '<form>';
				$childString .= '<input type="hidden" name="-table" value="'.$query['-table'].'">';
				$childString .= '<input type="hidden" name="-action" value="'.$query['-action'].'">';
				$childString .= '<input type="hidden" name="-recordid" value="'.$record->getID().'">';
				$childString .= '<input type="hidden" name="-status_change" value="'.$record->getID().'">';

				//Status: Not Complete
				if($record->val('status') == "NCO" || $record->val('status') == "NCP"){
					$childString .= '<input type="submit" name="status_button" value="Complete Job">'; //Complete Job Button
					$childString .= '&nbsp;&nbsp;<input type="submit" name="status_button" value="Void Call Slip">'; //Void Button
				}

				//Status: Job Complete
				elseif($record->val('status') == "CMP"){
					//Check if all purchase orders associated with the Call Slip have been posted.
					$purchaseorderRecords = $record->getRelatedRecords('call_slip_purchase_orders');
					$po_not_posted = false;
					foreach ($purchaseorderRecords as $cs_pr){
						if($cs_pr['post_status'] != "Posted"){
							$po_not_posted = true;
							break;
						}
							
					}
					//If not, show, but disable 'Create Billing' Button.
					if($po_not_posted == true)
						$childString .= '[Some Purchase Orders associated with this record have not yet been posted.]<br>
										<input type="submit" name="status_button" value="Create Billing" disabled="disabled" style="color: grey;">'; //Disabled Create Billing Button
					else
						$childString .= '<input type="submit" name="status_button" value="Create Billing">'; //Create Billing Button

					$childString .= '&nbsp;&nbsp;<input type="submit" name="status_button" value="Void Call Slip">'; //Void Button
				}
				
				//Status: Billing Pending
				elseif($record->val('status') == "BPD"){
					$childString .= '<input type="submit" name="status_button" value="Unbill"> (This will remove the associated Accounts Payable Entry)';
				}
				
				//Status: Billed
				elseif($record->val('status') == "BLD")
					$childString .= '<input type="submit" name="status_button" value="Credit Invoice">';

				else {
					$childString .= "No further options available";
				}

				$childString .= '</form>';
			}
			
			return array(
				'content' => "$childString",
				'class' => 'main',
				'label' => 'Change Status',
				'order' => 999
			);
		}
	}


	//Function to create a billing entry based on the call slip - returns the id of the billing record
	//On error: (-1) Error with creating AR entry, (-2) Error saving the customer balance, (-3) Error loading customer record.
	function create_billing($record){
		
		if($record->val('type') == "TM"){
		
			$cs_total = 0;
			
			//Collect Billing Details for Time Logs
			$timelog_records = $record->getRelatedRecords('time_logs');
			foreach ($timelog_records as $cs_tl){
				//Convert arrive / depart times, & calculate 'hours'
				$arrive = Dataface_converters_date::datetime_to_string($cs_tl['start_time']);
				$depart = Dataface_converters_date::datetime_to_string($cs_tl['end_time']);
				$hours = number_format(((strtotime($depart) - strtotime($arrive)) / 3600),1);

				//Get "rate" details
				$rate_record = df_get_record('rates', array('rate_id'=>$cs_tl['rate_id']));
				$rate = $rate_record->val($cs_tl['rate_type']);
				
				$cs_total += $rate * $hours;
			}
				
			//Collect Billing Details for Inventory Items
			$inventoryRecords = df_get_records_array('call_slip_inventory', array('call_id'=>$record->val('call_id')));
			foreach ($inventoryRecords as $cs_ir){
				//Pull the item / cost out of the 'inventory' table
				$inventory_record = df_get_record('inventory', array('inventory_id'=>$cs_ir->val('inventory_id')));
							
				$purchase_price = $cs_ir->val('purchase_price');
				$sale_price = $cs_ir->val('sale_price'); //Pull sale price from record (will likely be null)
				
				//If the sale price has not already been set in the callslip inventory record
				if(!isset($sale_price)){
							
					//Check if the Inventory sale overide has been set, if so, use it
					if($inventory_record->val('sale_method')=="overide"){
						$sale_price = $inventory_record->val('sale_overide');
					}
					//Otherwise (most likely), calculate based on the purchase price and set customer markup rate
					else{ 
						//Get the custmer markup record
						$customerRecord = df_get_record('customers', array('customer_id'=>$record->val('customer_id')));
						$markupRecords = df_get_records_array('customer_markup_rates', array('markup_id'=>$record->val('markup')));

						foreach ($markupRecords as $mr) {
							if($mr->val('to') == null)
								$no_limit = true;
								
							if( ($purchase_price >= $mr->val('from')) && ($purchase_price <= $mr->val('to') || $no_limit == true) ){
								$markup = $mr->val('markup_percent');
								break;
							}
						}
									
						$sale_price = round($purchase_price * (1+$markup),2);
					}				

					$cs_ir->setValue('sale_price',$sale_price); //Set "Sale Price" to CS record - this is to ensure that the price doesn't change once the billing has been created.
					$res = $cs_ir->save(null,true); //Save Record w/ permission check.
				}
				
				//Save inventory sale price to total
				$cs_total += $sale_price * $cs_ir->val('quantity');
			}

			//Collect Billing Details for Purchase Order Items
			$purchaseRecords = df_get_records_array('purchase_order_service', array('callslip_id'=>$record->val('call_id')));
			foreach ($purchaseRecords as $cs_pr){
				//Pull the items from the purchase order record
				$itemRecords = df_get_records_array('purchase_order_service_items', array('purchase_order_id'=>$cs_pr->val('purchase_id')));
				foreach ($itemRecords as $item){
					$purchase_price = $item->val('purchase_price');
					$sale_price = $item->val('sale_price'); //Pull sale price from record (will likely be null)

					//If the sale price has not already been set in the po item record
					if(!isset($sale_price)){
					//Get the custmer markup record
						$customerRecord = df_get_record('customers', array('customer_id'=>$record->val('customer_id')));
						$markupRecords = df_get_records_array('customer_markup_rates', array('markup_id'=>$record->val('markup')));

						foreach ($markupRecords as $mr) {
							if($mr->val('to') == null)
								$no_limit = true;
										
							if( ($purchase_price >= $mr->val('from')) && ($purchase_price <= $mr->val('to') || $no_limit == true) ){
								$markup = $mr->val('markup_percent');
								break;
							}
						}
									
						$sale_price = round($purchase_price * (1+$markup),2); 

						$item->setValue('sale_price',$sale_price); //Set "Sale Price" to the item record - this is to ensure that the price doesn't change once the billing has been created.
					}
								
					//If the "quantity used" field is empty, assign it to be the total quantity from purchase
					if($item->val('quantity_used') == "")
						$item->setValue('quantity_used',$item->val('quantity'));

					$res = $item->save(null,true); //Save Record w/ permission check.

					//Save PO item sale price to total
					$cs_total += $sale_price * $item->val('quantity_used');
				}
			}

			//Collect Billing Details for Additional Material Items
			$materialRecords = df_get_records_array('call_slip_additional_materials', array('call_id'=>$record->val('call_id')));
			foreach ($materialRecords as $cs_mr){
				$sale_price = $cs_mr->val('sale_price'); //Pull sale price from record
				$quantity = $cs_mr->val('quantity'); //Pull quantity price from record
				
				$cs_total += $sale_price * $quantity;
			}
			
			$cs_total += $record->val('charge_consumables');
			$cs_total += $record->val('charge_fuel');
		}
		else{
			$cs_total = $record->val("quoted_cost");
		}
		
		//Create Accounts Receivable Entry
		require_once('tables/accounts_receivable/accounts_receivable.php');
		$new_ar_id = tables_accounts_receivable::create_accounts_receivable_entry($record->val('call_id'), $record->val('customer_id'), $cs_total, $record->val('customer_po'));

		//If no errors with creating AR entry, add total to customer balance.
		if($new_ar_id > 0){
			$customer_record = df_get_record('customers',array('customer_id'=>$record->val('customer_id'))); //Get Customer Record
			if(isset($customer_record)){
				$customer_balance = $customer_record->val('balance'); //Get current customer balance
				$customer_record->setValue('balance',$customer_balance+$cs_total); //Add call slip total to balance
				$res = $customer_record->save(null, true); //Save balance, with permission check
				if ( PEAR::isError($res) )
					return -2;
			}
			else
				return -3;
		}
		
		return $new_ar_id;
	
	}

	//Function to unbill a call slip - returns 1 on success, -1 on error.
	function unbill_cs($record){
		$ar_record = df_get_record('accounts_receivable',array('voucher_id'=>$record->val('ar_billing_id')));
		
		//Check for batch
		if($ar_record->val('batch_id') != null){
			//Delete record in accounts_receivable_batch_vouchers
			$ar_batch_record = df_get_record('accounts_receivable_batch_vouchers',array('voucher_id'=>$record->val('ar_billing_id')));
			$res = $ar_batch_record->delete(true);
		}
		if ( PEAR::isError($res) ){
			// An error occurred
			return -1;
		}
			
		$res = $ar_record->delete(true);

		if ( PEAR::isError($res) ){
			// An error occurred
			return -2;
		}

		
		return 1;
	}
	
	//Function to create a credit call slip - returns call_id of new credit call_slip, -1 on error.
	function create_credit_cs($record){
		$new_cs_record = new Dataface_Record('call_slips', array());

		//Create a array from the current record values.
		$record_values = $record->vals();
		
		//Unset the values that we don't want to transfer to the new record.
		unset($record_values['search_field']);
		unset($record_values['call_id']);
		unset($record_values['status']);

		//Copy everything from the current record to the new
		foreach($record_values as $name=>$value){
			$new_cs_record->setValue($name,$value);
			//$ret .= $name . "=" . $value . " - ";
		}
		$new_cs_record->setValue('type','CR');
		$new_cs_record->setValue('status','CMP');
		$new_cs_record->setValue('credit','Credit for Call ID '.$record->val('call_id'));
		if($record->val('type') == "TM"){ //If type if 'Time & Materials', calculate total
			$cs_inv_total = (float) str_replace(',', '', $this->field__invoice_total($record)); //Remove comma, and cast as float. (Output from function is in number_format form)
			$new_cs_record->setValue('quoted_cost',$cs_inv_total);
		}
		
		//$res = $record->save();   // Doesn't check permissions
		$res = $new_cs_record->save(null, true);  // checks permissions

		if ( PEAR::isError($res) ){
			// An error occurred
			return -1;
			throw new Exception($res->getMessage());
		}
		

		//Get & parse through call slip inventory records
	/*	$csi_records = df_get_records_array('call_slip_inventory',array('call_id'=>$record->val('call_id')));
		foreach($csi_records as $csi_record){
			//Create new call slip inventory entries
			$new_csi_record = new Dataface_Record('call_slip_inventory', array());

			//Create a array from the current record values.
			$record_values = $csi_record->vals();

			//Unset the values that we don't want to transfer to the new record.
			unset($record_values['csi_id']);
			unset($record_values['call_id']);

			//Copy everything from the current record to the new
			foreach($record_values as $name=>$value){
				$new_csi_record->setValue($name,$value);
			}
			$new_csi_record->setValue('call_id',$new_cs_record->val('call_id'));

			$res = $new_csi_record->save(null, true);  // checks permissions

			if ( PEAR::isError($res) ){
				// An error occurred
				return -2;
				throw new Exception($res->getMessage());
			}
		}
		
		//Create new call slip additional materials entries
		$csa_records = df_get_records_array('call_slip_additional_materials',array('call_id'=>$record->val('call_id')));
		foreach($csa_records as $csa_record){
			//Create new call slip inventory entries
			$new_csa_record = new Dataface_Record('call_slip_additional_materials', array());

			//Create a array from the current record values.
			$record_values = $csa_record->vals();

			//Unset the values that we don't want to transfer to the new record.
			unset($record_values['list_id']);
			unset($record_values['call_id']);

			//Copy everything from the current record to the new
			foreach($record_values as $name=>$value){
				$new_csa_record->setValue($name,$value);
			}
			$new_csa_record->setValue('call_id',$new_cs_record->val('call_id'));

			$res = $new_csa_record->save(null, true);  // checks permissions

			if ( PEAR::isError($res) ){
				// An error occurred
				return -3;
				throw new Exception($res->getMessage());
			}
		}
	*/
		return $new_cs_record->val('call_id');
	}
	
	
	
	//Calendar Module Functions
		function getBgColor($record){
			if ( $record->val('technician') == 1) return 'blue';
			if ( $record->val('technician') == 2) return 'blueviolet';
			if ( $record->val('technician') == 3) return 'brown';
			if ( $record->val('technician') == 4) return 'cadetblue';
			if ( $record->val('technician') == 5) return 'chocolate';
			if ( $record->val('technician') == 6) return 'cornflowerblue';
			if ( $record->val('technician') == 7) return 'crimson';
			if ( $record->val('technician') == 8) return 'darkcyan';
			if ( $record->val('technician') == 9) return 'darkred';
			if ( $record->val('technician') == 10) return 'green';
			if ( $record->val('technician') == 11) return 'goldenrod';
			else return 'rgb(0,0,0)';
		}
		
		function calendar__decorateEvent(Dataface_Record $record, &$event){
			$rec_site = df_get_record('customer_sites', array('site_id'=>$record->val('site_id')));
			$rec_empl = df_get_record('employees', array('employee_id'=>$record->val('technician')));
			$event['title'] = "\nTech: " . $rec_empl->val('first_name') . " " . $rec_empl->val('last_name') . "\nSite: " . $rec_site->val('address');
		}



	//PROCESSING

	function inventory__validate(&$record, $value, &$params){
		//Empty the error message
		$params['message'] = '';

		//Get the Call Slip ID
		$csid = $record->val('call_id');
		
		//Get rid of the last set in the array - it isn't needed for our use and causes issues
		unset($value['__loaded__']);

		//Determine what to add/subtract from the general inventory.
		//  First - Run through the data in the form list
		foreach ($value as $x){

			//Skip empty lines - do nothing (unless a quantity has been assigned, and then return an error)
			if($x['inventory_id'] == ''){
				if($x['quantity']){ //Case where the 'item_name' field has been left empty, but a quantity has been given
					$params['message'] .= 'CANNOT PROCESS INVENTORY: A quantity has been given, but an "Item Name" has not been assigned.';
					return false;
				}
			}

			//Process non-empty lines
			else{
				//$params['message'] .= $x['inventory_id'] . ' -> ' . $x['quantity'] . '<br>';

				//Pull data from the "call_slip_inventory" and "inventory" tables.
				$cs_inv = df_get_record('call_slip_inventory', array('call_id'=>$csid, 'inventory_id'=>$x['inventory_id']));
				$gen_inv = df_get_record('inventory', array('inventory_id'=>$x['inventory_id']));

				//Create some variables for simplicity.
				$item_name = $gen_inv->display('item_name');
				$gen_inv_q = $gen_inv->display('quantity');
				$new_cs_i_q = $x['quantity'];

				
				//Check insure that each item in only entered in once.
				//We do this to simplify things significantly, and allow us to safely ignore some things that would otherwise have to be handled.
				foreach ($value as $y)
				{
					//Check if different lines have the same 'inventory_id'
					if( ($x['__order__'] != $y['__order__']) && ($x['inventory_id'] == $y['inventory_id'])){
						$params['message'] .= 'CANNOT PROCESS INVENTORY: The item: "'. $item_name .'" has been added multiple times. Please add each item only once.<br>';
						return 0;
					}
				}

				//If this is a new entry, $cur_cs_i_q = 0
				if(!$cs_inv)
					$cur_cs_i_q = 0;

				//Otherwise, pull from the 'call_slip_inventory' record
				else
					$cur_cs_i_q = $cs_inv->display('quantity');

				//Modify inventory variable.
				$mod_inv = $new_cs_i_q - $cur_cs_i_q;

				//Check if the quantity has changed. If so... do some things.
				if($new_cs_i_q != $cur_cs_i_q){
					//First we check to make sure that the quantity of the item being added is not greater than what's in the inventory.
					//If it is, cause an error and go no further.
					if($mod_inv > $gen_inv_q){
						$params['message'] .=	'CANNOT PROCESS INVENTORY: The current stock in inventory for "'. $item_name .'" is ' . $gen_inv_q . '.<br>' .
												'You are trying to add ' . $mod_inv . ' which exceeds the amount in inventory.<br>';
						return 0;
					}
					
					//Next check to make sure the quantity entered is not negative
					//If it is, cause an error and go no further.
					if($new_cs_i_q < 0){
						$params['message'] .=	'CANNOT PROCESS INVENTORY: Negative inventory for "'. $item_name .'" cannot be added.<br>';
						return 0;
					}
					

					//Now, save the inventory modification to the class variable cs_modify_inventory. The actual inventory will be modified/saved in the beforeSave() function.
					//We don't save here because 1) this function is actually run twice, and thus the inventory would be modified x2, and 2) other potential validation checks failing.
					$this->cs_modify_inventory[$x['inventory_id']] = (- $mod_inv);

					//*****Output for testing purposes
					//$params['message'] .= 'Value for "'. $item_name.'" has been modified. Changing from ' . $cur_cs_i_q . ' to ' . $new_cs_i_q . '.<br>';
					//$params['message'] .=	'Modify inventory by: ' . $mod_inv . '.<br>' .
					//						'Inventory was: ' . $gen_inv_q . ', Will now be: ' . ($gen_inv_q-$mod_inv) . '<br>';
					//*****
				}
			}
		}

		//  Now check to see if any lines have been removed
		$cs_inv = df_get_records_array('call_slip_inventory', array('call_id'=>$csid));
		foreach($cs_inv as $x)
		{
			//Clear the "found" key
			$found = 0;
			
			//Compare against the lines in $value to see if any are missing.
			foreach ($value as $y){
				//Check if the 'inventory_id' is in the list and set key
				if( $x->val('inventory_id') == $y['inventory_id'])
					$found = 1;
			}

			//If the item was not found, it has been removed, and we add the quantity back to inventory.
			if($found == 0){
				//Get matching inventory record
				$gen_inv = df_get_record('inventory', array('inventory_id'=>$x->val('inventory_id')));
				$gen_inv_q = $gen_inv->display('quantity');

				//Modify the inventory.
				$this->cs_modify_inventory[$x->val('inventory_id')] = $x->val('quantity');


				//*****Output for testing purposes
				//$params['message'] .=	'Item '. $gen_inv->val('item_name') .' Removed!<br>'.
				//						'Modify inventory by: ' . $x->val('quantity') . '.<br>' .
				//						'Inventory was: ' . $gen_inv_q . ', Will now be: ' . ($gen_inv_q+$x->val('quantity')) . '<br>';
				//*****

			}
		}
		
		//If no errors have occured, move along.
		//print_r($this->cs_modify_inventory);
		return 1;
	}

	function beforeSave(&$record){
		//$response =& Dataface_Application::getResponse();
		//$rlist = 'a';
		/*
		if($record->val('status') == '')
			$record->setValue('status','NCO');
		
		if($record->val('call_datetime') == '')
			$record->setValue('call_datetime',date('Y-m-d g:i a'));
		*/
		//*****************************************************************
		//********************Inventory Management Code********************
		//*****************************************************************

		//Get inventory modification values from the class variable cs_modify_inventory
		foreach($this->cs_modify_inventory as $iid=>$modify){
			$gen_inv = df_get_record('inventory', array('inventory_id'=>$iid));
			$gen_inv->setValue('quantity',($gen_inv->val('quantity') + $modify));
			//$gen_inv->save(null, true);
			$gen_inv->save();
			//return PEAR::raiseError('END',DATAFACE_E_NOTICE);
		}

		//*********************************************************************
		//********************END Inventory Management Code********************
		//*********************************************************************

		//if($rlist){
		//	$response['--msg'] = "Data: ".$rlist;
		//	return PEAR::raiseError("FIN",DATAFACE_E_NOTICE);
		//}
	}

	function beforeInsert(&$record){
		//Copy "Site Instructions" from the Customer Site file.
		$site_record = df_get_record('customer_sites', array('site_id'=>$record->val('site_id')));
		$record->setValue('site_instructions', $site_record->val('site_instructions'));

		//Set initial 'Status' and 'Call Date/Time'
		$record->setValue('status','NCO');
		$record->setValue('call_datetime',date('Y-m-d g:i a'));
	}








	//These are for HTML Reports
		function field__company($record){
			return company_name();
		}

		function field__company_address_1($record){
			$address = company_address();
			return $address['address'];
		}
		
		function field__company_address_2($record){
			$address = company_address();
			return $address['city'] . ', ' . $address['state'] . ' ' . $address['zip'];
		}

		function field__company_phone($record){
			return company_phone();
		}
		
		function field__company_fax($record){
			return company_fax();
		}

		function field__date_today($record){
			return date('m/d/Y');
		}
		
		function field__billing_address_1($record){
			$rec = df_get_record('customers', array('customer_id'=>$record->val('customer_id')));
			$billing_address = $rec->val('billing_address');
			return $billing_address;
		}
		
		function field__billing_address_2($record){
			$rec = df_get_record('customers', array('customer_id'=>$record->val('customer_id')));
			$billing_address = $rec->val('billing_city') . ' ' . $rec->val('billing_state') . ' ' . $rec->val('billing_zip');;
			return $billing_address;
		}

		function field__site_address($record){
			$rec = df_get_record('customer_sites', array('site_id'=>$record->val('site_id')));
			$billing_address = $rec->val('site_city') . ' ' . $rec->val('site_state') . ' ' . $rec->val('site_zip');
			return $billing_address;
		}
		
		function field__time_log_total($record){
			$total = 0;
			$employeeRecords = $record->getRelatedRecords('time_logs');
			foreach ($employeeRecords as $cs_er){
				$arrive = Dataface_converters_date::datetime_to_string($cs_er['start_time']);
				$depart = Dataface_converters_date::datetime_to_string($cs_er['end_time']);
				$hours = number_format(((strtotime($depart) - strtotime($arrive)) / 3600),1);
				$total += ($hours * $cs_er['rate_per_hour']);
			}
			return number_format($total,2);
		}
		
		function field__materials_total($record){
			$total = 0;
			
			$purchaseorderRecords = df_get_records_array('purchase_order_service', array('callslip_id'=>$record->val('call_id')));
			foreach ($purchaseorderRecords as $cs_pr){
				//Pull the items from the purchase order record
				$itemRecords = df_get_records_array('purchase_order_service_items', array('purchase_order_id'=>$cs_pr->val('purchase_id')));
				foreach ($itemRecords as $item){
					$subtotal_sale = $item->val('sale_price') * $item->val('quantity_used');
					$total += $subtotal_sale;
				}
			}
			
			$inventoryRecords = $record->getRelatedRecords('call_slip_inventory');
			foreach ($inventoryRecords as $cs_ir){
				$subtotal_sale = $cs_ir['sale_price'] * $cs_ir['quantity'];
				$total += $subtotal_sale;
			}

			$additionalRecords = $record->getRelatedRecords('call_slip_additional_materials');
			foreach ($additionalRecords as $cs_ar){
				$subtotal_sale = $cs_ar['sale_price'] * $cs_ar['quantity'];
				$total += $subtotal_sale;
			}

			$total += $record->val('charge_consumables');
				
			return number_format($total,2);
		}
	
		function field__consumables_text($record){
			if($record->val('charge_consumables') != "")
				return "Misc Consumables";
		}
		function field__consumables_quantity($record){
			if($record->val('charge_consumables') != "")
				return "1";
		}
		function field__consumables_charge($record){
			if($record->val('charge_consumables') != "")
				return $record->val('charge_consumables');
		}

		function field__fuel_text($record){
			$text = "";
			if($record->val('charge_fuel') != "")
				$text .= "Fuel Surcharge: $" . $record->val('charge_fuel');
				
			return $text;
		}
		
		function field__invoice_total($record){
			$total = 0;
			
			//Materials
			$purchaseorderRecords = df_get_records_array('purchase_order_service', array('callslip_id'=>$record->val('call_id')));
			foreach ($purchaseorderRecords as $cs_pr){
				//Pull the items from the purchase order record
				$itemRecords = df_get_records_array('purchase_order_service_items', array('purchase_order_id'=>$cs_pr->val('purchase_id')));
				foreach ($itemRecords as $item){
					$subtotal_sale = $item->val('sale_price') * $item->val('quantity_used');
					$total += $subtotal_sale;
				}
			}
			
			$inventoryRecords = $record->getRelatedRecords('call_slip_inventory');
			foreach ($inventoryRecords as $cs_ir){
				$subtotal_sale = $cs_ir['sale_price'] * $cs_ir['quantity'];
				$total += $subtotal_sale;
			}

			$additionalRecords = $record->getRelatedRecords('call_slip_additional_materials');
			foreach ($additionalRecords as $cs_ar){
				$subtotal_sale = $cs_ar['sale_price'] * $cs_ar['quantity'];
				$total += $subtotal_sale;
			}

			//Misc
			$total += $record->val('charge_consumables');
			$total += $record->val('charge_fuel');

			//Hours
			$employeeRecords = $record->getRelatedRecords('time_logs');
			foreach ($employeeRecords as $cs_er){
				$arrive = Dataface_converters_date::datetime_to_string($cs_er['start_time']);
				$depart = Dataface_converters_date::datetime_to_string($cs_er['end_time']);
				$hours = number_format(((strtotime($depart) - strtotime($arrive)) / 3600),1);
				$total += ($hours * $cs_er['rate_per_hour']);
			}

			return number_format($total,2);
		}


}
?>
