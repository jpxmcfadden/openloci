<?php /* Smarty version 2.6.18, created on 2012-09-14 11:34:43
         compiled from Dataface_Details_Controller.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'translate', 'Dataface_Details_Controller.html', 21, false),array('function', 'prev_link', 'Dataface_Details_Controller.html', 31, false),array('function', 'jump_menu', 'Dataface_Details_Controller.html', 36, false),array('function', 'next_link', 'Dataface_Details_Controller.html', 41, false),array('function', 'actions_menu', 'Dataface_Details_Controller.html', 45, false),)), $this); ?>
<div class="result-stats">
		<?php $this->_tag_stack[] = array('translate', array('id' => 'found x records in table y','found' => $this->_tpl_vars['ENV']['resultSet']->found(),'table' => $this->_tpl_vars['ENV']['table_object']->getLabel(),'cursor' => $this->_tpl_vars['ENV']['resultSet']->cursor()+1,'total' => $this->_tpl_vars['ENV']['resultSet']->cardinality())); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><b>Found</b> <?php echo $this->_tpl_vars['ENV']['resultSet']->found(); ?>
 of <?php echo $this->_tpl_vars['ENV']['resultSet']->cardinality(); ?>
 records in table <b><i><?php echo $this->_tpl_vars['ENV']['table_object']->getLabel(); ?>
</i></b>
			<br/><b>Now Showing</b> <?php echo $this->_tpl_vars['ENV']['resultSet']->cursor()+1; ?>
 of <?php echo $this->_tpl_vars['ENV']['resultSet']->found(); ?>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
			
	</div>

	<div class="prev-link">
		<?php echo $this->_plugins['function']['prev_link'][0][0]->prev_link(array(), $this);?>

	</div>
	<?php if ($this->_tpl_vars['ENV']['prefs']['show_jump_menu']): ?>
	<div class="jump-menu">
	
		<?php echo $this->_plugins['function']['jump_menu'][0][0]->jump_menu(array(), $this);?>

	
	</div>
	<?php endif; ?>
	<div class="next-link">
		<?php echo $this->_plugins['function']['next_link'][0][0]->next_link(array(), $this);?>

	</div>
	
	<div class="record-details-actions">
		<?php echo $this->_plugins['function']['actions_menu'][0][0]->actions_menu(array('id' => "record-details-actions",'id_prefix' => "record-details-actions-",'class' => "icon-only",'category' => 'record_details_actions'), $this);?>

	</div>