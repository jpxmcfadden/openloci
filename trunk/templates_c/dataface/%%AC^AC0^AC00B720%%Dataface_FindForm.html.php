<?php /* Smarty version 2.6.18, created on 2012-10-23 12:02:32
         compiled from Dataface_FindForm.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'block', 'Dataface_FindForm.html', 115, false),array('block', 'define_slot', 'Dataface_FindForm.html', 116, false),array('block', 'collapsible_sidebar', 'Dataface_FindForm.html', 191, false),array('modifier', 'count', 'Dataface_FindForm.html', 141, false),)), $this); ?>

<!-- Display the fields -->
 
<?php echo '
<script language="javascript" type="text/javascript"><!--
	function Dataface_QuickForm(){
		
	}
	Dataface_QuickForm.prototype.setFocus = function(element_name){
		document.'; ?>
<?php echo $this->_tpl_vars['form_data']['name']; ?>
<?php echo '.elements[element_name].focus();
		document.'; ?>
<?php echo $this->_tpl_vars['form_data']['name']; ?>
<?php echo '.elements[element_name].select();
	}
	var quickForm = new Dataface_QuickForm();
//--></script>
'; ?>

		
<form<?php echo $this->_tpl_vars['form_data']['attributes']; ?>
>
<?php echo $this->_tpl_vars['form_data']['hidden']; ?>

<?php echo $this->_tpl_vars['form_data']['javascript']; ?>

 
<table width="100%" class="Dataface_QuickForm-table-wrapper Dataface_FindForm-table-wrapper">

<?php $_from = $this->_tpl_vars['form_data']['elements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['element']):
?>
		<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => "findform_before_".($this->_tpl_vars['element']['field']['name'])."_row"), $this);?>

	<?php $this->_tag_stack[] = array('define_slot', array('name' => "findform_".($this->_tpl_vars['element']['field']['name'])."_row")); $_block_repeat=true;$this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<tr id="<?php echo $this->_tpl_vars['element']['field']['name']; ?>
_findform_row">
		<td valign="top" align="right" class="Dataface_QuickForm-label-cell Dataface_FindForm-label-cell">
		<div class="field" id="findform_<?php echo $this->_tpl_vars['element']['field']['tablename']; ?>
-<?php echo $this->_tpl_vars['element']['field']['name']; ?>
-label-wrapper">
		
			<label><?php echo $this->_tpl_vars['element']['field']['widget']['label']; ?>
</label>
		
		</div>
		</td>
		<td class="Dataface_QuickForm-widget-cell Dataface_FindForm-widget-cell">
		<div class="field" id="<?php echo $this->_tpl_vars['element']['field']['tablename']; ?>
-<?php echo $this->_tpl_vars['element']['field']['name']; ?>
-wrapper">
		
		
		
		
			<div>
			
			<?php $this->_tag_stack[] = array('define_slot', array('name' => "findform_".($this->_tpl_vars['element']['field']['name'])."_widget")); $_block_repeat=true;$this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
			<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => "findform_before_".($this->_tpl_vars['element']['field']['name'])."_widget"), $this);?>

			<?php if ($this->_tpl_vars['element']['html']): ?>
				<?php echo $this->_tpl_vars['element']['html']; ?>

			<?php elseif ($this->_tpl_vars['element']['elements']): ?>
				<!--<fieldset><legend><?php echo $this->_tpl_vars['element']['field']['widget']['label']; ?>
</legend>-->
				<?php if ($this->_tpl_vars['element']['field']['widget']['columns']): ?><?php $this->assign('cols', $this->_tpl_vars['element']['field']['widget']['columns']); ?><?php else: ?><?php $this->assign('cols', 3); ?><?php endif; ?>
				<?php if ($this->_tpl_vars['cols'] > 1): ?>					<?php $this->assign('numelements', count($this->_tpl_vars['element']['elements'])); ?>
					<?php $this->assign('threshold', $this->_tpl_vars['numelements']/$this->_tpl_vars['cols']); ?>
					<table><tr><td>
				<?php endif; ?>
				<?php $this->assign('ctr', 0); ?>
				<?php $_from = $this->_tpl_vars['element']['elements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['grouploop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['grouploop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['groupel']):
        $this->_foreach['grouploop']['iteration']++;
?>
				
					<?php echo $this->_tpl_vars['groupel']['html']; ?>
<?php if ($this->_tpl_vars['element']['widget']['separator']): ?><?php echo $this->_tpl_vars['element']['widget']['separator']; ?>
<?php else: ?><?php echo $this->_tpl_vars['element']['separator']; ?>
<?php endif; ?>
					<?php $this->assign('ctr', $this->_tpl_vars['ctr']+1); ?>
					<?php if (( $this->_tpl_vars['cols'] > 1 ) && ( $this->_tpl_vars['ctr'] >= $this->_tpl_vars['threshold'] )): ?></td><td><?php $this->assign('ctr', 0); ?><?php endif; ?>
				<?php endforeach; endif; unset($_from); ?>
				<?php if ($this->_tpl_vars['cols'] > 1): ?>
					</td></tr></table>
				<?php endif; ?>
				<!--</fieldset>--> 
			<?php endif; ?>
			<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => "findform_after_".($this->_tpl_vars['element']['field']['name'])."_widget"), $this);?>

			<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
			
			
			
			</div>
			<div class="formHelp"><?php echo $this->_tpl_vars['element']['field']['widget']['description']; ?>
</div>
			
			<?php if ($this->_tpl_vars['element']['field']['widget']['focus']): ?>
			<script language="javascript" type="text/javascript"><!--
			try<?php echo '{'; ?>
quickForm.setFocus('<?php echo $this->_tpl_vars['element']['field']['name']; ?>
');<?php echo '} catch(err){}'; ?>

			//--></script>
			
			
			<?php endif; ?>
		</div>
		</td>
	</tr>
	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => "findform_after_".($this->_tpl_vars['element']['field']['name'])."_row"), $this);?>


<?php endforeach; endif; unset($_from); ?>
</table>
<?php $_from = $this->_tpl_vars['form_data']['sections']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['section']):
?>


		
	<?php $this->_tag_stack[] = array('collapsible_sidebar', array('heading' => $this->_tpl_vars['section']['header'])); $_block_repeat=true;smarty_block_collapsible_sidebar($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => 'before_findform_table'), $this);?>

		<table width="100%" class="Dataface_QuickForm-table-wrapper Dataface_FindForm-table-wrapper">
		
						<?php $_from = $this->_tpl_vars['section']['elements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['element']):
?>
			
								
				<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => "findform_before_".($this->_tpl_vars['element']['field']['name'])."_row"), $this);?>

				<?php $this->_tag_stack[] = array('define_slot', array('name' => "findform_".($this->_tpl_vars['element']['field']['name'])."_row")); $_block_repeat=true;$this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
				<tr id="<?php echo $this->_tpl_vars['element']['field']['name']; ?>
_findform_row">
					<td valign="top" align="right" class="Dataface_QuickForm-label-cell Dataface_FindForm-label-cell">
					<div class="field" id="findform_<?php echo $this->_tpl_vars['element']['field']['tablename']; ?>
-<?php echo $this->_tpl_vars['element']['field']['name']; ?>
-label-wrapper">
					
						<label><?php echo $this->_tpl_vars['element']['field']['widget']['label']; ?>
</label>
					
					</div>
					</td>
					<td class="Dataface_QuickForm-widget-cell Dataface_FindForm-widget-cell">
					<div class="field" id="<?php echo $this->_tpl_vars['element']['field']['tablename']; ?>
-<?php echo $this->_tpl_vars['element']['field']['name']; ?>
-wrapper">
					
					
					
					
						<div>
						
						<?php $this->_tag_stack[] = array('define_slot', array('name' => "findform_".($this->_tpl_vars['element']['field']['name'])."_widget")); $_block_repeat=true;$this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
						<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => "findform_before_".($this->_tpl_vars['element']['field']['name'])."_widget"), $this);?>

						<?php if ($this->_tpl_vars['element']['html']): ?>
							<?php echo $this->_tpl_vars['element']['html']; ?>

						<?php elseif ($this->_tpl_vars['element']['elements']): ?>
							<!--<fieldset><legend><?php echo $this->_tpl_vars['element']['field']['widget']['label']; ?>
</legend>-->
							<?php if ($this->_tpl_vars['element']['field']['widget']['columns']): ?><?php $this->assign('cols', $this->_tpl_vars['element']['field']['widget']['columns']); ?><?php else: ?><?php $this->assign('cols', 3); ?><?php endif; ?>
							<?php if ($this->_tpl_vars['cols'] > 1): ?>								<?php $this->assign('numelements', count($this->_tpl_vars['element']['elements'])); ?>
								<?php $this->assign('threshold', $this->_tpl_vars['numelements']/$this->_tpl_vars['cols']); ?>
								<table><tr><td>
							<?php endif; ?>
							<?php $this->assign('ctr', 0); ?>
							<?php $_from = $this->_tpl_vars['element']['elements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['grouploop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['grouploop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['groupel']):
        $this->_foreach['grouploop']['iteration']++;
?>
							
								<?php echo $this->_tpl_vars['groupel']['html']; ?>
<?php if ($this->_tpl_vars['element']['widget']['separator']): ?><?php echo $this->_tpl_vars['element']['widget']['separator']; ?>
<?php else: ?><?php echo $this->_tpl_vars['element']['separator']; ?>
<?php endif; ?>
								<?php $this->assign('ctr', $this->_tpl_vars['ctr']+1); ?>
								<?php if (( $this->_tpl_vars['cols'] > 1 ) && ( $this->_tpl_vars['ctr'] >= $this->_tpl_vars['threshold'] )): ?></td><td><?php $this->assign('ctr', 0); ?><?php endif; ?>
							<?php endforeach; endif; unset($_from); ?>
							<?php if ($this->_tpl_vars['cols'] > 1): ?>
								</td></tr></table>
							<?php endif; ?>
							<!--</fieldset>--> 
						<?php endif; ?>
						<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => "findform_after_".($this->_tpl_vars['element']['field']['name'])."_widget"), $this);?>

						<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
						
						
						
						</div>
						<div class="formHelp"><?php echo $this->_tpl_vars['element']['field']['widget']['description']; ?>
</div>
						
						<?php if ($this->_tpl_vars['element']['field']['widget']['focus']): ?>
						<script language="javascript" type="text/javascript"><!--
						try<?php echo '{'; ?>
quickForm.setFocus('<?php echo $this->_tpl_vars['element']['field']['name']; ?>
');<?php echo '} catch(err){}'; ?>

						//--></script>
						
						
						<?php endif; ?>
					</div>
					</td>
				</tr>
				<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
				<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => "findform_after_".($this->_tpl_vars['element']['field']['name'])."_row"), $this);?>

			<?php endforeach; endif; unset($_from); ?>
		</table>
		<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => 'after_findform_table'), $this);?>

	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_collapsible_sidebar($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php endforeach; endif; unset($_from); ?>

</form>

 