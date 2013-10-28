<?php

class actions_purchase_order_post_service {
	
	function handle(&$params){

		$poTable = "purchase_order_service";
		$poItemTable = "purchase_order_service_items";
		$po_type = "purchase_order_post_service";
		$id_prefix = "S";
	
		//Permission check
		if ( !isUser() )
			return Dataface_Error::permissionDenied("You are not logged in");

			
		//If the page has been submitted (Post button pressed), this will be "true", otherwise NULL.
		$confirm = isset($_GET['confirm_post']) ? $_GET['confirm_post'] : null;

		//Get the application instance - used for pulling the query data
		$app =& Dataface_Application::getInstance();

		//Extend the limit of df_get_records_array past the usual first 30 records, and query the records where [post_status == "Pending"]
		$query =& $app->getQuery();
		$query['-skip'] = 0;
		$query['-limit'] = ""; //******I think this will do *all*, but need to double check********
		$query['post_status'] = 'Pending';
			
		//Pull all records with Pending status
		$records = df_get_records_array($poTable, $query);

		//Unset the 'post_status' query
		unset($query['post_status']);
		
		//Set Headers as an empty array
		$headers = array();

		//Run through all pulled records
		foreach($records as $i=>$record){
			//Set the table - used for linking to the correct entry in the html.
			$headers[$i]['table'] = $poTable;

			//Get basic info
			$rdate = $record->val('purchase_date');
			$vendorRecord = df_get_record('vendors', array('vendor_id'=>$record->val('vendor_id')));
						
			$headers[$i]['id'] = $record->val('purchase_order_id');
			$headers[$i]['date'] = $rdate['month'].'-'.$rdate['day'].'-'.$rdate['year'];
			$headers[$i]['vendor'] = $vendorRecord->val('vendor');
			$headers[$i]['total'] = $record->val('total');

//**************************************//
			//If the Post button has been pressed, process the entries
			if($confirm == "true"){
				//Only modify selected records
				if(isset($_GET[$record->val('purchase_order_id')]) && $_GET[$record->val('purchase_order_id')]=="on"){
					$record->setValue('post_status',"Posted"); //Set status to Pending.
					$record->setValue('post_date',date('Y-m-d')); //Set post date.
					$res = $record->save(); //Save Record w/o permission check.
					//$res = $record->save(null, true); //Save Record w/ permission check. <-- this is error'ing. Find out why and fix.

					//Check for errors.
					if ( PEAR::isError($res) ){
						// An error occurred
						$save_error = 1;
						//throw new Exception($res->getMessage());
						break;
					}
					
					//Special tag to denote which entries have been posted.
					$headers[$i]['posted'] = "true";
					
					//Get associated items
					$query['purchase_order_id'] = $record->val('purchase_id');
					//$query['account_id'] = '>0'; //Check to make sure the account exists (i.e. if the journal line was removed)
					$item_records = df_get_records_array($poItemTable, $query);

					/*
					//Process all the Items in the Purchase Order
					foreach($item_records as $j=>$item_record){
						//Pull inventory record, Calculate & Set new inventory 'quantity' & 'last price'
						//	$inventory_record = df_get_record('inventory', array('inventory_id'=>$item_record->val('inventory_id')));
						//	$current_inventory_quantity = $inventory_record->val('quantity');
						//	$new_inventory_quantity = $current_inventory_quantity + $item_record->val('quantity');
						//	$inventory_record->setValue('quantity', $new_inventory_quantity);
						//	$inventory_record->setValue('last_purchase', $item_record->val('purchase_price'));
						
							//Save Record
						//		$res = $inventory_record->save(); //Save Record w/o permission check.
						
						//Create purchase history record
						//	$purchase_history_record = new Dataface_Record('inventory_purchase_history', array());
						//	$purchase_history_record->setValues(
						//				array(
						//					'inventory_id'=>$item_record->val('inventory_id'),
						//					'purchase_order_id'=>$record->val('purchase_id'),
						//					'purchase_date'=>$record->val('purchase_date'),
						//					'vendor'=>$record->val('vendor_id'),
						//					'purchase_price'=>$item_record->val('purchase_price'),
						//					'quantity'=>$item_record->val('quantity')
						//				)
						//			);
									
							//Save Record
						//		$res = $purchase_history_record->save(null, true);
						
						
						
						
						//CHECK FOR ERRORS*****

					}
					*/
					
				}
			}
//**************************************//			
			else {
				//Get associated item entries
				$query['purchase_order_id'] = $record->val('purchase_id');// print_r($query); echo "<br><br>";
				//$query['inventory_id'] = '>0'; //Check to make sure the account exists (i.e. if the journal line was removed)
				$item_records = df_get_records_array($poItemTable, $query);

				//Set the total purchase price of the PO to 0.
				$total_purchase = 0;
				
				//Pull all the item data
				foreach($item_records as $j=>$item_record){
						$inventory_record = df_get_record('inventory', array('inventory_id'=>$item_record->val('inventory_id')));
							
						$headers[$i]['entries'][$j]['item']=$inventory_record->val('item_name');
						$headers[$i]['entries'][$j]['quantity']=$item_record->val('quantity');
						$headers[$i]['entries'][$j]['purchase_price']=$item_record->val('purchase_price');
						$headers[$i]['entries'][$j]['total_per_item']=number_format(($item_record->val('purchase_price') * $item_record->val('quantity')),2);
						//$total_purchase += $item_record->val('purchase_price') * $item_record->val('quantity');
				}

				$total_purchase = $record->val('item_total');
				$headers[$i]['total_purchase'] = number_format($total_purchase,2);
				$headers[$i]['tax'] = $record->val('tax')*$total_purchase . ' (' . $record->val('tax')*100 . '%)';
				$headers[$i]['shipping'] = $record->val('shipping');
				//$headers[$i]['total'] = $record->val('total');

			}
		}
			
		//Display the page
		df_display(array("headers"=>$headers,"confirm"=>$confirm,"po_type"=>$po_type), 'purchase_order_post.html');

	}
}


?>
