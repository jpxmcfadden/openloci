<?php /* Smarty version 2.6.18, created on 2012-10-21 20:23:42
         compiled from copy_replace.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'use_macro', 'copy_replace.html', 1, false),array('block', 'fill_slot', 'copy_replace.html', 2, false),array('block', 'translate', 'copy_replace.html', 7, false),array('function', 'html_options', 'copy_replace.html', 55, false),)), $this); ?>
<?php $this->_tag_stack[] = array('use_macro', array('file' => "Dataface_Main_Template.html")); $_block_repeat=true;$this->_plugins['block']['use_macro'][0][0]->use_macro($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php $this->_tag_stack[] = array('fill_slot', array('name' => 'main_section')); $_block_repeat=true;$this->_plugins['block']['fill_slot'][0][0]->fill_slot($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>

	<h2><?php echo $this->_tpl_vars['title']; ?>
</h2>
	<p><?php echo $this->_tpl_vars['message']; ?>
</p>
	<div id="selected-records-list">
	<h3><?php $this->_tag_stack[] = array('translate', array('id' => "templates.copy_replace.HEADING_SELECTED_RECORDS")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		Selected records:
		<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	</h3>
	   <table class="listing scrollable" id="preview-table">
	   <thead>
	   	<tr>
	   		<th>Title</th>
	   		<?php $_from = $this->_tpl_vars['columns']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['column']):
?>
	   			<th><?php echo $this->_tpl_vars['column']['widget']['label']; ?>
</th>
	   		<?php endforeach; endif; unset($_from); ?>
	   	</tr>
	   </thead>
	   <tbody>
	  <?php $_from = $this->_tpl_vars['records']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['record']):
?>
	  	<tr><td><?php echo $this->_tpl_vars['record']->getTitle(); ?>
</td>
	  	<?php $_from = $this->_tpl_vars['columns']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['column']):
?>
	  		<td><?php echo $this->_tpl_vars['record']->preview($this->_tpl_vars['column']['name']); ?>
</td>
	  	<?php endforeach; endif; unset($_from); ?>
	  	</tr>
	  	
	  <?php endforeach; endif; unset($_from); ?>
	  </tbody>
	  </table>
	</div>
	<?php echo '
	<script language="javascript"><!--
		function updateForm(select){
			var selected = select.options[select.selectedIndex].value;
			var formRow = document.getElementById(\'copy-replace-field-\'+selected+\'-row\');
			formRow.style.display = \'\';
			select.options[select.selectedIndex] = null;
			var form = document.getElementById(\'copy_replace_form\');
			var fields= form.elements[\'-copy_replace:fields\'];
			if ( fields.value ) fields.value += \'-\'+selected;
			else fields.value = selected;
		}
	//--></script>
	'; ?>

	<!--
	<div class="floating-palette">
		<h3>Help</h3>
		<p>Click here for help</p>
	</div>
	-->
	<div id="copy_replace-select-field">
	<h3 style="display: inline">Add Field to update: </h3>
	<select id="xf-copy-replace-fields-select" onchange="updateForm(this);">
	<?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['field_options']), $this);?>

	</select>
	<button id="df-copy-replace-help-button"><span><?php $this->_tag_stack[] = array('translate', array('id' => 'help')); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Help<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></button>
	</div>
	
	<div id="copy_replace-form">
	
	<?php echo $this->_tpl_vars['form']; ?>

	<p><img src="<?php echo $this->_tpl_vars['ENV']['DATAFACE_URL']; ?>
/images/exclamation.gif"/><?php echo $this->_tpl_vars['warning']; ?>
</p>
	
	</div>
	
	
	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['fill_slot'][0][0]->fill_slot($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['use_macro'][0][0]->use_macro($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>