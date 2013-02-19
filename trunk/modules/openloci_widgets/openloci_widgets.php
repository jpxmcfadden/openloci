<?php


class modules_openloci_widgets {

	//Test to make sure module is getting loaded.
	//function block__before_header(){
	//	echo "<h1>OpenLoci Widgets Enabled</h1>";
	//	echo dirname(__FILE__).'\grid_layout_widget.php';
	//}

	// The constructor for the module class.  Executed at the beginning
	// of each request
	public function __construct(){
		$app = Dataface_Application::getInstance();
		if ( !class_exists('Dataface_FormTool') ){
				// If the formtool is not loaded then we don't 
				// want to load it here... we'll just register
				// the _registerWidget() method to run  as soon
				// as the FormTool is loaded.
						$app->registerEventListener(
								'Dataface_FormTool::init', 
								array($this, '_registerWidget')
						);
				} else {
						// If the formTool is already loaded, then we'll
						// register the widget directly
						$this->_registerWidget(Dataface_FormTool::getInstance());
				}
	}
    
	// Function to register our widget with the form tool.
	public function _registerWidget(Dataface_FormTool $formTool){
		$formTool->registerWidgetHandler(
			'grid_layout', 
			dirname(__FILE__).'\grid_layout_widget.php',
			'Dataface_FormTool_grid_layout'
		);
	}
}

?>