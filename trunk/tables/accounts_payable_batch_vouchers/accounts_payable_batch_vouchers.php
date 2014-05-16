<?php

class tables_accounts_payable_batch_vouchers {

	function afterSave(&$record){
		$voucher_record = df_get_record('accounts_payable', array('voucher_id'=>$record->val('voucher_id')));
		$voucher_record->setValue('batch_id',$record->val('batch_id'));
		$voucher_record->save();

	}
}
?>
