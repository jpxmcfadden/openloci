<?php /* Smarty version 2.6.18, created on 2012-09-18 00:36:58
         compiled from Dataface_AjaxEventDetails.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'block', 'Dataface_AjaxEventDetails.html', 1, false),array('function', 'actions_menu', 'Dataface_AjaxEventDetails.html', 10, false),array('block', 'define_slot', 'Dataface_AjaxEventDetails.html', 2, false),array('modifier', 'escape', 'Dataface_AjaxEventDetails.html', 6, false),)), $this); ?>
<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => 'before_event_details'), $this);?>

<?php $this->_tag_stack[] = array('define_slot', array('name' => 'event_details')); $_block_repeat=true;$this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
<table class="record-view-table"><tbody>
<?php $_from = $this->_tpl_vars['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field']):
?>

	<tr><th><?php echo ((is_array($_tmp=$this->_tpl_vars['field']['fielddef']['widget']['label'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</th><td id="<?php echo $this->_tpl_vars['field']['tdid']; ?>
" class="<?php echo $this->_tpl_vars['field']['tdclass']; ?>
"><?php echo $this->_tpl_vars['field']['value']; ?>
</td></tr>

<?php endforeach; endif; unset($_from); ?>
</tbody></table>
<?php echo $this->_plugins['function']['actions_menu'][0][0]->actions_menu(array('category' => 'event_actions','record' => $this->_tpl_vars['event']), $this);?>

<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => 'after_event_details'), $this);?>

		