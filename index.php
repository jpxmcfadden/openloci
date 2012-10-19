<?php //Main Application access point

//Default Sort Orders
	if ( !isset($_REQUEST['-sort']) and @$_REQUEST['-table'] == 'customers' ){
		$_REQUEST['-sort'] = $_GET['-sort'] = 'customer';
	}

	
require_once "C:\\Work\\xampp\\htdocs\\xataface/dataface-public-api.php";
df_init(__FILE__, "/xataface");
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


//$app->addHeadContent('<link rel="stylesheet" type="text/css" href="styles.css"/>');
//$app->addHeadContent('<style type="text/css">p{visibility:hidden;display:none;}</style>');