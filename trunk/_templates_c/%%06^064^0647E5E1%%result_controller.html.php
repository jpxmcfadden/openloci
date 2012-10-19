<?php /* Smarty version 2.6.18, created on 2012-09-18 00:19:47
         compiled from xataface/RelatedList/result_controller.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'translate', 'xataface/RelatedList/result_controller.html', 3, false),array('modifier', 'escape', 'xataface/RelatedList/result_controller.html', 4, false),)), $this); ?>
<div class="result-stats">
	
	<?php $this->_tag_stack[] = array('translate', array('id' => "scripts.Dataface.RelatedList.toHtml.MESSAGE_FOUND",'num' => $this->_tpl_vars['num_related_records'],'relationship' => $this->_tpl_vars['relationship_name'])); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		<b>Found</b> <?php echo ((is_array($_tmp=$this->_tpl_vars['num_related_records'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
 Records in relationship <i><?php echo ((is_array($_tmp=$this->_tpl_vars['relationship_name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</i>
	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	<br/>
	<?php $this->_tag_stack[] = array('translate', array('id' => "scripts.Dataface.RelatedList.toHtml.MESSAGE_NOW_SHOWING",'start' => $this->_tpl_vars['now_showing_start'],'finish' => $this->_tpl_vars['now_showing_finish'])); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		<b>Now Showing</b> <?php echo ((is_array($_tmp=$this->_tpl_vars['now_showing_start'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
 to <?php echo ((is_array($_tmp=$this->_tpl_vars['now_showing_finish'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>

	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
</div>
<div class=\"limit-field\">
	<?php echo $this->_tpl_vars['limit_field']; ?>

</div>
<div class="prev-link"><?php echo $this->_tpl_vars['back_link']; ?>
</div>
<div class="next-link"><?php echo $this->_tpl_vars['next_link']; ?>
</div>