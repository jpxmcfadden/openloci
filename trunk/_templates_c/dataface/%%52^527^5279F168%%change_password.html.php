<?php /* Smarty version 2.6.18, created on 2012-09-14 14:32:32
         compiled from change_password.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'use_macro', 'change_password.html', 1, false),array('block', 'fill_slot', 'change_password.html', 2, false),)), $this); ?>
<?php $this->_tag_stack[] = array('use_macro', array('file' => "Dataface_Main_Template.html")); $_block_repeat=true;$this->_plugins['block']['use_macro'][0][0]->use_macro($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php $this->_tag_stack[] = array('fill_slot', array('name' => 'main_column')); $_block_repeat=true;$this->_plugins['block']['fill_slot'][0][0]->fill_slot($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		<div id="change-password-form" style="display:none">
			<h1>Change Password</h1>
			
			<form action="<?php echo $this->_tpl_vars['ENV']['DATAFACE_SITE_HREF']; ?>
" method="post">
				<input type="hidden" name="-action" value="change_password"/>
				
				<table>
					<tr>
						<th>
							<label>Old Password</label>
						</th>
						<td>
							<input type="password" name="--current-password" id="--current-password"/>
						</td>
					</tr>
					<tr>
						<th>
							<label>New Password<label>
						</th>
						<td>
							<input type="password" name="--password1" id="--password1"/>
						</td>
					<tr>
						<th>
							<label>Retype New Password</label>
						</th>
						<td>
							<input type="password" name="--password2" id="--password2"/>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="submit-cell">
							<input type="submit" value="Change Password" id="change-password-submit"/>
						</td>
					</tr>
				
				
				</table>
			
			</form>
		
		</div>
		
		<div id="change-password-complete" style="display:none">
		
			<h1>Password Successfully Changed</h1>
		
		</div>
	
	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['fill_slot'][0][0]->fill_slot($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['use_macro'][0][0]->use_macro($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>