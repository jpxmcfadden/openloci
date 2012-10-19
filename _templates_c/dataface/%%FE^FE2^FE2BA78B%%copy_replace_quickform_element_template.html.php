<?php /* Smarty version 2.6.18, created on 2012-09-14 12:10:32
         compiled from copy_replace_quickform_element_template.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'translate', 'copy_replace_quickform_element_template.html', 9, false),)), $this); ?>
<tr id="copy-replace-field-<?php echo $this->_tpl_vars['fieldname']; ?>
-row" style="display: none">
	<td id="copy-replace-field-<?php echo $this->_tpl_vars['fieldname']; ?>
-label-cell" class="copy-replace-field-label-cell" valign="top" align="right">
		<label><?php echo $this->_tpl_vars['field']['widget']['label']; ?>
</label>
	</td>
	<td id="copy-replace-field-<?php echo $this->_tpl_vars['fieldname']; ?>
-widget-cell" class="copy-replace-field-widget-cell" valign="top" align="left">
		<!-- BEGIN required --><span style="color: #ff0000" class="fieldRequired" title="required">&nbsp;</span><!-- END required -->
		<!-- BEGIN error --><div class="fieldError" style="color: #ff0000"><?php echo '{error}'; ?>
</div><!-- END error -->
		<?php echo '{element}'; ?>

		(<input type="checkbox" name="-copy_replace:blank_flag[<?php echo $this->_tpl_vars['fieldname']; ?>
]" label="Leave Blank"><label for="-copy_replace:blank_flag[<?php echo $this->_tpl_vars['fieldname']; ?>
]"><?php $this->_tag_stack[] = array('translate', array('id' => "templates.copy_replace_quickform_element_template.LABEL_MAKE_BLANK")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Make Blank<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label>)
	</td>
</tr>