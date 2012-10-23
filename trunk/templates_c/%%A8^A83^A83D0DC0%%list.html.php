<?php /* Smarty version 2.6.18, created on 2012-10-23 11:59:57
         compiled from xataface/RelatedList/list.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'xataface/RelatedList/list.html', 19, false),array('modifier', 'count', 'xataface/RelatedList/list.html', 35, false),array('block', 'translate', 'xataface/RelatedList/list.html', 20, false),)), $this); ?>
 		
<?php if (! $this->_tpl_vars['relatedList']->hideActions && ( $this->_tpl_vars['record_editable'] || $this->_tpl_vars['can_add_new_related_record'] || $this->_tpl_vars['can_add_existing_related_record'] )): ?>
	
	<div id="relatedActionsWrapper" class="contentActions">
		<ul id="relatedActions">
			<?php if ($this->_tpl_vars['relatedList']->_relationship->supportsAddNew() && $this->_tpl_vars['can_add_new_related_record']): ?>
				<li id="addNew">
					<a id="add_new_related_record" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['new_related_record_link'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
">
						<?php $this->_tag_stack[] = array('translate', array('id' => "scripts.Dataface.RelatedList.toHtml.LABEL_ADD_NEW_RELATED_RECORD",'relationship' => ((is_array($_tmp=$this->_tpl_vars['relationship_label'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)))); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
							Add New <?php echo ((is_array($_tmp=$this->_tpl_vars['relationship_label'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
 Record
						<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
					</a>
				</li>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['relatedList']->_relationship->supportsAddExisting() && $this->_tpl_vars['can_add_existing_related_record']): ?>
				  <li id="addExisting">
					  <a id="add_existing_related_record" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['existing_related_record_link'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
">
						  <?php $this->_tag_stack[] = array('translate', array('id' => "scripts.Dataface.RelatedList.toHtml.LABEL_ADD_EXISTING_RELATED_RECORD",'relationship' => ((is_array($_tmp=$this->_tpl_vars['relationship_label'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)))); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
							Add Existing <?php echo ((is_array($_tmp=$this->_tpl_vars['relationship_label'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
 Record
						  <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
					  </a>
				   </li>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['import_related_records_query'] && count($this->_tpl_vars['importTable']->getImportFilters()) > 0): ?>
				<li id="import">
					<a id="import_related_records" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['link3'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
">
						<?php $this->_tag_stack[] = array('translate', array('id' => "scripts.Dataface.RelatedList.toHtml.LABEL_IMPORT_RELATED_RECORDS",'relationship' => ((is_array($_tmp=$this->_tpl_vars['relationship_label'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)))); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
							Import <?php echo ((is_array($_tmp=$this->_tpl_vars['relationship_label'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
 Records
						<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
					</a>
				</li>
			<?php endif; ?>
		</ul>
	</div>
	
<?php endif; ?>
<?php if (! $this->_tpl_vars['relatedList']->hideActions): ?>

	<div class="result-tools" style="float:left">
		<script language="javascript" type="text/javascript" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['searchSrc'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"></script>
		<a href="#" onclick="Dataface.RelatedList.showSearch('<?php echo ((is_array($_tmp=$this->_tpl_vars['relname'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
', document.getElementById('related_find_wrapper')); return false;" title="Filter these results"><img src="<?php echo ((is_array($_tmp=$this->_tpl_vars['imgIcon'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" alt="Filter" /></a>
		
	</div>


<?php endif; ?>
<?php if (! $this->_tpl_vars['relatedList']->hideActions): ?>
	<div class="resultlist-controller related-list-controller">
		<?php ob_start(); ?>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "xataface/RelatedList/result_controller.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			
			<div class="result-list-actions">
				<ul class="icon-only" id="result-list-actions">
	
					<?php $_from = $this->_tpl_vars['related_list_actions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['action']):
?>
						
						
						<li id="result-list-actions-<?php echo ((is_array($_tmp=$this->_tpl_vars['action']['id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" class="plain">
						
							<a id="result-list-actions-<?php echo ((is_array($_tmp=$this->_tpl_vars['action']['id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
-link" href="<?php echo ((is_array($_tmp=$this->_tpl_vars['action']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"
								<?php if ($this->_tpl_vars['action']['onclick']): ?>
									onclick="<?php echo ((is_array($_tmp=$this->_tpl_vars['action']['onclick'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"
								<?php endif; ?>
							   accesskey="e" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['action']['description'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
">
							   <img id="result-list-actions-<?php echo ((is_array($_tmp=$this->_tpl_vars['action']['id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
-icon" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['action']['icon'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['action']['label'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"/>                   
								<span class="action-label"><?php echo ((is_array($_tmp=$this->_tpl_vars['action']['label'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</span>
							</a>
						</li>
		
					
					<?php endforeach; endif; unset($_from); ?>
				
				
				
				</ul>
			
			</div>
		<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('relatedResultController', ob_get_contents());ob_end_clean(); ?>
		<?php echo $this->_tpl_vars['relatedResultController']; ?>


	</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['treetable']): ?>
	<?php echo $this->_tpl_vars['treetable']; ?>

<?php else: ?>
	<?php echo $this->_tpl_vars['moveUpForm']; ?>

	<?php if (! $this->_tpl_vars['relatedList']->hideActions && $this->_tpl_vars['relatedList']->_where): ?>
		<?php $this->assign('relatedSearchKey', "-related:search"); ?>
		<div>Showing matches for query <em>&quot;<?php echo ((is_array($_tmp=$this->_tpl_vars['filterQuery'][$this->_tpl_vars['relatedSearchKey']])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
&quot;</em>
		<a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['app']->url('-related:search='))) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" title="Remove this filter to show all records in this relationship">
			<img src="<?php echo ((is_array($_tmp=@DATAFACE_URL)) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
/images/delete.gif" alt="Remove filter" />
		</a>
		</div>
	<?php endif; ?>
	<div style="display:none" id="related_find_wrapper"></div>
	<?php if (count($this->_tpl_vars['records']) > 0): ?>
		<?php echo $this->_tpl_vars['related_table_html']; ?>

		
		
		<?php if (! $this->_tpl_vars['relatedList']->hideActions): ?>
			<?php echo $this->_tpl_vars['selected_actions_form']; ?>

			
			<?php if (count($this->_tpl_vars['selected_actions']) > 0): ?>
				<div id="selected-actions">
					<?php $this->_tag_stack[] = array('translate', array('id' => "scripts.GLOBAL.LABEL_WITH_SELECTED")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>With Selected<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>: 
					<ul class="selectedActionsMenu" id="result_list-selectedActionsMenu">
						<?php $_from = $this->_tpl_vars['selected_actions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['action']):
?>
					
							<li id="action-<?php echo ((is_array($_tmp=$this->_tpl_vars['action']['id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"><a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['action']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['action']['description'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['action']['label'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</a></li>

						<?php endforeach; endif; unset($_from); ?>				
			
			
					</ul>
				</div>
			<?php endif; ?>

			<div class="resultlist-controller">
				<?php echo $this->_tpl_vars['relatedResultController']; ?>

			</div>
			
			<script language="javascript" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['prototype_url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"></script>
			<script language="javascript" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['scriptaculous_url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"></script>
			<script language="javascript" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['effects_url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"></script>
			<script language="javascript" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['dragdrop_url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"></script>
			<script><?php echo '
				function removeUnauthorizedColumns(){
					var relatedList = document.getElementById(\'relatedList\');
					var trs = relatedList.getElementsByTagName(\'tr\');
					var viewableColumns = [];
					var numCols = 0;
					for (var i=0; i<trs.length; i++){
						var tr = trs[i];
						var tds = tr.getElementsByTagName(\'td\');
						for (var j=0; j<tds.length; j++){
							var td = tds[j];
							if ( td.className.indexOf(\'viewableColumn\') >= 0 ){
								viewableColumns[j] = true;
							}
							numCols = j;
						}
					}
					for (var j=viewableColumns.length; j<=numCols; j++){
						viewableColumns[j] = false;
					}
					
					
					for (var i=0; i<trs.length; i++){
						var tds = trs[i].getElementsByTagName(\'td\');
						if ( tds.length <= 0 ){
							var tds = trs[i].getElementsByTagName(\'th\');
						}
						
						for (var j=0; j<viewableColumns.length; j++){
							if ( !viewableColumns[j] ){
								tds[j].style.display = \'none\';
							}
						}
						
					}
				}
				removeUnauthorizedColumns();
				
				
				if ( '; ?>
<?php echo $this->_tpl_vars['sortable_js']; ?>
<?php echo ' ){
					Sortable.create("relatedList-body",
							{
								dropOnEmpty:true,
								constraint:false, 
								//handle:\'move-handle\',
								tag:\'tr\',
								onUpdate: function(container){
									
									var params = Sortable.serialize(\'relatedList-body\');
									params += \'&\'+window.location.search.substring(1);
									
									params += \'&-action=reorder_related_records\';
									
									new Ajax.Request(
										DATAFACE_SITE_HREF, {
											method: \'post\', 
											parameters: params, 
											onSuccess: function(transport){
												
												//document.getElementById(\'details-controller\').innerHTML = transport.responseText;
											},
											onFailure:function(){
												alert(\'Failed to sort records.\');
											}
										}
									);
									
								}
								//only:\'movable\'
							});
						//Sortable.create("dataface-sections-main",
						//{dropOnEmpty:true,constraint:false, handle:\'movable-handle\',tag:\'div\',only:\'movable\', onUpdate:updateSections});
				}	
			
			'; ?>
</script>
			

		<?php endif; ?>
	
	<?php else: ?>
	
		<p><?php $this->_tag_stack[] = array('translate', array('id' => "scripts.GLOBAL.NO_RECORDS_MATCHED_REQUEST")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>No records matched your request.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></p>
	<?php endif; ?>

	
		
<?php endif; ?>