<?php //Main Application access point

//Default Sort Orders
	if ( !isset($_REQUEST['-sort']) and @$_REQUEST['-table'] == 'employees' ){ $_REQUEST['-sort'] = $_GET['-sort'] = 'last_name, first_name'; }
	if ( !isset($_REQUEST['-sort']) and @$_REQUEST['-table'] == 'customers' ){ $_REQUEST['-sort'] = $_GET['-sort'] = 'customer'; }
	if ( !isset($_REQUEST['-sort']) and @$_REQUEST['-table'] == 'customer_sites' ){ $_REQUEST['-sort'] = $_GET['-sort'] = 'customer_id, site_address'; }
	if ( !isset($_REQUEST['-sort']) and @$_REQUEST['-table'] == 'call_slips' ){ $_REQUEST['-sort'] = $_GET['-sort'] = 'call_id desc'; }
	if ( !isset($_REQUEST['-sort']) and @$_REQUEST['-table'] == 'purchase_orders' ){ $_REQUEST['-sort'] = $_GET['-sort'] = 'purchase_id desc'; }
	if ( !isset($_REQUEST['-sort']) and @$_REQUEST['-table'] == 'chart_of_accounts' ){ $_REQUEST['-sort'] = $_GET['-sort'] = 'account_number'; }
	if ( !isset($_REQUEST['-sort']) and @$_REQUEST['-table'] == 'general_ledger' ){ $_REQUEST['-sort'] = $_GET['-sort'] = 'ledger_id desc'; }
	if ( !isset($_REQUEST['-sort']) and @$_REQUEST['-table'] == 'payroll_period' ){ $_REQUEST['-sort'] = $_GET['-sort'] = 'payroll_period_id desc'; }


		
require_once "../xataface/dataface-public-api.php";
df_init(__FILE__, "../xataface");
$app =& Dataface_Application::getInstance();
$app->display();

function isUser(){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( $user )  return true;
    return false;
}

function isAdmin(){
    $auth =& Dataface_AuthenticationTool::getInstance();
    $user =& $auth->getLoggedInUser();
    if ( $user and ($user->val('role') == 'ADMIN' or $user->val('role') == 'MASTER') )  return true;
    return false;
}

function myRole(){
	$auth =& Dataface_AuthenticationTool::getInstance();
	$user =& $auth->getLoggedInUser();
	return $user->val('role'); 
}

function default_location_state(){
	$app =& Dataface_Application::getInstance();
	$record = df_get_record('_company_info', array('id'=>1));
	return $record->val('state');
}

function company_name(){
	$app =& Dataface_Application::getInstance();
	$record = df_get_record('_company_info', array('id'=>1));
	return $record->val('name');
}

function company_address(){
	$app =& Dataface_Application::getInstance();
	$record = df_get_record('_company_info', array('id'=>1));
	$c_address['address'] = $record->val('address');
	$c_address['city'] = $record->val('city');
	$c_address['state'] = $record->val('state');
	$c_address['zip'] = $record->val('zip');
	return $c_address;
}

function company_phone(){
	$app =& Dataface_Application::getInstance();
	$record = df_get_record('_company_info', array('id'=>1));
	return $record->val('phone');
}

function company_fax(){
	$app =& Dataface_Application::getInstance();
	$record = df_get_record('_company_info', array('id'=>1));
	return $record->val('fax');
}

function company_county(){
	$app =& Dataface_Application::getInstance();
	$record = df_get_record('_company_info', array('id'=>1));
	$record_county = df_get_record('admin_county_tax', array('county_id'=>$record->val('county')));
	return $record_county->val('county');
}

function company_webaddress(){
	$app =& Dataface_Application::getInstance();
	$record = df_get_record('_company_info', array('id'=>1));
	return $record->val('web_address');
}
