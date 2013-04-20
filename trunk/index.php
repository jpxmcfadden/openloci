<?php //Main Application access point

//Default Sort Orders
	if ( !isset($_REQUEST['-sort']) and @$_REQUEST['-table'] == 'employees' ){ $_REQUEST['-sort'] = $_GET['-sort'] = 'last_name, first_name'; }
	if ( !isset($_REQUEST['-sort']) and @$_REQUEST['-table'] == 'customers' ){ $_REQUEST['-sort'] = $_GET['-sort'] = 'customer'; }
	if ( !isset($_REQUEST['-sort']) and @$_REQUEST['-table'] == 'customer_sites' ){ $_REQUEST['-sort'] = $_GET['-sort'] = 'customer_id, site_address'; }
	//if ( !isset($_REQUEST['-sort']) and @$_REQUEST['-table'] == 'call_slips' ){ $_REQUEST['-sort'] = $_GET['-sort'] = 'call_datetime desc'; }
	if ( !isset($_REQUEST['-sort']) and @$_REQUEST['-table'] == 'call_slips' ){ $_REQUEST['-sort'] = $_GET['-sort'] = 'call_id desc'; }
	if ( !isset($_REQUEST['-sort']) and @$_REQUEST['-table'] == 'purchase_orders' ){ $_REQUEST['-sort'] = $_GET['-sort'] = 'purchase_id desc'; }


require_once "xataface/dataface-public-api.php";
df_init(__FILE__, "xataface");
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
	return $app->_conf['_default_location'];
}

function company_name(){
	$app =& Dataface_Application::getInstance(); 
	return $app->_conf['_company_info']['name'];
}

function company_address(){
	$app =& Dataface_Application::getInstance();
	$c_address['address'] = $app->_conf['_company_info']['address'];
	$c_address['city'] = $app->_conf['_company_info']['city'];
	$c_address['state'] = $app->_conf['_company_info']['state'];
	$c_address['zip'] = $app->_conf['_company_info']['zip'];
	return $c_address;
}

function company_phone(){
	$app =& Dataface_Application::getInstance();
	return $app->_conf['_company_info']['phone'];
}

function company_fax(){
	$app =& Dataface_Application::getInstance();
	return $app->_conf['_company_info']['fax'];
}