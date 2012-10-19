<?php /* Smarty version 2.6.18, created on 2012-09-14 12:10:22
         compiled from Dataface_DeleteForm.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'translate', 'Dataface_DeleteForm.html', 20, false),)), $this); ?>
<h2><?php $this->_tag_stack[] = array('translate', array('id' => "templates.Dataface_DeleteForm.DELETE_RECORDS")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete Records<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h2>
<?php echo $this->_tpl_vars['msg']; ?>

<?php echo $this->_tpl_vars['form']; ?>