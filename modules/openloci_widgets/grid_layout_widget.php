<?php
$GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']['grid'] = array('HTML/QuickForm/grid.php', 'HTML_QuickForm_grid');

class Dataface_FormTool_grid_layout {

        // Builds a widget for the given record
        function &buildWidget(&$record, &$field, $form, $formFieldName, $new=false){
		
            // Obtain an HTML_QuickForm factory to creat the basic elements
		$table =& $record->_table;
		$formTool =& Dataface_FormTool::getInstance();
		$factory =& Dataface_FormTool::factory();
		$widget =& $field['widget'];
		$el =& $factory->addElement('grid',$formFieldName, $widget['label']);
		$el->setProperties($widget);


			//if ( isset($field['relationship']) ){
			$relationship =& $table->getRelationship($field['relationship']);
			
			$el->table = $relationship->getDomainTable();
			if ( isset( $widget['columns'] ) ){
				$columns = array_map('trim',explode(',',$widget['columns']));
			} else {
				$columns = $relationship->_schema['short_columns'];
			}

print_r ($columns);
			
			$subfactory = new HTML_QuickForm();
			$dummyRelatedRecord = new Dataface_RelatedRecord($record, $relationship->getName());
			foreach ($columns as $column){
				
				$colTable =& $relationship->getTable($column);
				if ( !$colTable ) echo "Could not find table for column $column";
				
				$colPerms = $dummyRelatedRecord->getPermissions(array('field'=>$column));
				
				//if ( !@$colPerms['view'] ){
				//	unset($colTable);
				//	unset($dummyRecord);
				//	continue;
				//}
				
				// We need to be a bit more refined on this one.  We need to take
				// into account the context being that we are in a relationship.
				$dummyRecord = new Dataface_Record($colTable->tablename, $record->vals());
				$colFieldDef =& $colTable->getField($column);
				
				$columnElement =& $formTool->buildWidget($dummyRecord, $colFieldDef, $subfactory, $column, false);
				$defaultValue = $colTable->getDefaultValue($column);
				$columnElement->setValue($defaultValue);
				$el->defaults[$column] = $defaultValue;
				$el->addField($colFieldDef, $columnElement, $colPerms );
				
				$orderCol = $relationship->getOrderColumn();
				if ( PEAR::isError($orderCol) ){ $el->reorder=false;}
				
				unset($columnElement);
				unset($colFieldDef);
				unset($dummyRecord);
				unset($colTable);
				unset($elementFilter);
			}
			
            return $el;
			
        }
}

?>