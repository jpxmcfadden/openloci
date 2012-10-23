<?php /* Smarty version 2.6.18, created on 2012-10-23 11:59:58
         compiled from Dataface_RecordHistory.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'use_macro', 'Dataface_RecordHistory.html', 1, false),array('block', 'fill_slot', 'Dataface_RecordHistory.html', 2, false),array('block', 'translate', 'Dataface_RecordHistory.html', 39, false),array('function', 'load_record', 'Dataface_RecordHistory.html', 180, false),array('function', 'actions', 'Dataface_RecordHistory.html', 206, false),array('modifier', 'escape', 'Dataface_RecordHistory.html', 182, false),)), $this); ?>
<?php $this->_tag_stack[] = array('use_macro', array('file' => "Dataface_Record_Template.html")); $_block_repeat=true;$this->_plugins['block']['use_macro'][0][0]->use_macro($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php $this->_tag_stack[] = array('fill_slot', array('name' => 'record_content')); $_block_repeat=true;$this->_plugins['block']['fill_slot'][0][0]->fill_slot($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		<?php if (! $this->_tpl_vars['error']): ?>
	    <script language="javascript" type="text/javascript" src="<?php echo $this->_tpl_vars['ENV']['DATAFACE_URL']; ?>
/js/ajax.js"></script>

		<script language="javascript"><!--
		
			var currentTable = '<?php echo $this->_tpl_vars['ENV']['table']; ?>
';
			//var DATAFACE_SITE_HREF = '<?php echo $this->_tpl_vars['ENV']['DATAFACE_SITE_HREF']; ?>
';
			<?php echo '
			function HistoryToolClient(){
				
			}
			
			function expandHistoryRecord(id, td){
				this.showHistoryRecord(id);
				var img = document.getElementById(\'history-\'+id+\'-collapse-image\');
				img.src = DATAFACE_URL+\'/images/treeExpanded.gif\';
				td.onclick = function(){ historyToolClient.collapseHistoryRecord(id, td); };
				//alert(td.className);
				
			}
			HistoryToolClient.prototype.expandHistoryRecord = expandHistoryRecord;
			
			function collapseHistoryRecord(id, td){
				var row = document.getElementById(\'history-\'+id+\'-row\');
				row.style.display = "none";
				var img = document.getElementById(\'history-\'+id+\'-collapse-image\');
				img.src = DATAFACE_URL+\'/images/treeCollapsed.gif\';
				td.onclick = function(){historyToolClient.expandHistoryRecord(id, td);};
			}
			HistoryToolClient.prototype.collapseHistoryRecord = collapseHistoryRecord;
			
			function showHistoryRecord(id){
				var row = document.getElementById(\'history-\'+id+\'-row\');
				var cell = document.getElementById(\'history-\'+id+\'-details-content\');
				
				if ( !cell.record_content ){
					'; ?>
cell.innerHTML = "<?php $this->_tag_stack[] = array('translate', array('id' => "templates.Dataface_RecordHistory.MESSAGE_LOADING")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Loading ... Please wait ...<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>";<?php echo '
					this.loadHistoryRecord(id,false);
				} else {
					cell.innerHTML = cell.record_content;
					var menu  = document.getElementById(\'history-\'+id+\'-viewtabs\');
					var detailsTab = document.getElementById(\'history-\'+id+\'-viewtabs-details\');
					var changesTab = document.getElementById(\'history-\'+id+\'-viewtabs-changes\');
					var fromcurrentTab = document.getElementById(\'history-\'+id+\'-viewtabs-fromcurrent\');
					//alert(changesTab);
					changesTab.className = "unselectedTab";
					detailsTab.className = "selectedTab";
					fromcurrentTab.className = "unselectedTab";
					
					fromcurrentTab.onclick = function(){ historyToolClient.showFromCurrent(id);};
					changesTab.onclick = function(){ historyToolClient.showChanges(id);};
					detailsTab.onclick = null;
				}
				//row.style.display = \'table-row\';
				row.style.display = \'\';
				
				
				
				
			}
			HistoryToolClient.prototype.showHistoryRecord = showHistoryRecord;
			
			function loadHistoryRecord(id, changes, fromcurrent){
				var row = document.getElementById(\'history-\'+id+\'-row\');
				var cell = document.getElementById(\'history-\'+id+\'-details-content\');
				
				var url = \'?'; ?>
<?php echo $this->_tpl_vars['current_record_qstr']; ?>
<?php echo '&-action=view_history_record_details&-table=\'+currentTable+\'&history__id=\'+id;
				if ( changes ) url += \'&-show_changes=1\';
				if ( fromcurrent ) url += \'&-fromcurrent=1\';
				//alert(url);
				this.http = getHTTPObject();
				this.http.open(\'GET\', url);
				this.cell = cell;
				this.row = row;
				this.id = id;
				this.changes = changes;
				this.fromcurrent = fromcurrent;
				this.http.onreadystatechange = this.handleLoadHistoryRecord;
				HistoryToolClient.prototype.currentRequest = this;
				this.http.send(null);
			}
			HistoryToolClient.prototype.loadHistoryRecord = loadHistoryRecord;
			
			function handleLoadHistoryRecord(){
				//var client = historyToolClient;
				//alert(client);
				if ( historyToolClient.http.readyState == 4 ){
					//alert(historyToolClient.changes);
					if ( historyToolClient.changes ){
						historyToolClient.cell.record_content_changes = historyToolClient.http.responseText;
						historyToolClient.showChanges(historyToolClient.id);
					} else if ( historyToolClient.fromcurrent ){
						historyToolClient.cell.record_content_fromcurrent = historyToolClient.http.responseText;
						historyToolClient.showFromCurrent(historyToolClient.id);
					} else {
						historyToolClient.cell.record_content = historyToolClient.http.responseText;
						historyToolClient.showHistoryRecord(historyToolClient.id);
					}
					//alert(historyToolClient.http.responseText);
					
				}
			}
			HistoryToolClient.prototype.handleLoadHistoryRecord = handleLoadHistoryRecord;
			
			function showChanges(id){
				var cell = document.getElementById(\'history-\'+id+\'-details-content\');
				if ( !cell.record_content_changes ){
					cell.innerHTML = "Loading ... Please wait ...";
					this.loadHistoryRecord(id, true);
				} else {
				
					cell.innerHTML = cell.record_content_changes;
					var menu  = document.getElementById(\'history-\'+id+\'-viewtabs\');
					var detailsTab = document.getElementById(\'history-\'+id+\'-viewtabs-details\');
					var changesTab = document.getElementById(\'history-\'+id+\'-viewtabs-changes\');
					var fromcurrentTab = document.getElementById(\'history-\'+id+\'-viewtabs-fromcurrent\');
					//alert(changesTab);
					changesTab.className = "selectedTab";
					detailsTab.className = "unselectedTab";
					fromcurrentTab.className = \'unselectedTab\';
					fromcurrentTab.onclick = function(){ historyToolClient.showFromCurrent(id);};
					changesTab.onclick = null;
					detailsTab.onclick = function(){ historyToolClient.showHistoryRecord(id);};
					
					
				}
				
			}
			
			HistoryToolClient.prototype.showChanges = showChanges;
			
			
			function showFromCurrent(id){
				var cell = document.getElementById(\'history-\'+id+\'-details-content\');
				if ( !cell.record_content_fromcurrent ){
					cell.innerHTML = "Loading ... Please wait ...";
					this.loadHistoryRecord(id, false, true);
				} else {
				
					cell.innerHTML = cell.record_content_fromcurrent;
					var menu  = document.getElementById(\'history-\'+id+\'-viewtabs\');
					var detailsTab = document.getElementById(\'history-\'+id+\'-viewtabs-details\');
					var changesTab = document.getElementById(\'history-\'+id+\'-viewtabs-changes\');
					var fromcurrentTab = document.getElementById(\'history-\'+id+\'-viewtabs-fromcurrent\');
					
					//alert(changesTab);
					changesTab.className = "unselectedTab";
					detailsTab.className = "unselectedTab";
					fromcurrentTab.className = \'selectedTab\';
					fromcurrentTab.onclick = null;
					changesTab.onclick = function(){ historyToolClient.showChanges(id);};
					detailsTab.onclick = function(){ historyToolClient.showHistoryRecord(id);};
					
					
				}
				
			}
			HistoryToolClient.prototype.showFromCurrent = showFromCurrent;
			
			
			function restoreRecord(id){
				'; ?>
var res = confirm(<?php $this->_tag_stack[] = array('translate', array('id' => "templates.Dataface_RecordHistory.JS_STRING_CONFIRM_RESTORE")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>'Are you sure you want to restore the current record to the history snap shot with id '+id+'?'<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>);<?php echo '
				if ( !res ) return false;
				var form = document.getElementById(\'history-restore-form\');
				form.history__id.value = id;
				form.submit();
				
			}
			HistoryToolClient.prototype.restoreRecord = restoreRecord;
			
			
			
			
			var historyToolClient = new HistoryToolClient();
		
		//--></script>
		'; ?>

		<?php echo $this->_plugins['function']['load_record'][0][0]->load_record(array('var' => 'source_record'), $this);?>

		<form id="history-restore-form" action="<?php echo $this->_tpl_vars['ENV']['DATAFACE_SITE_HREF']; ?>
" method="POST">
			<input type="hidden" name="-table" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ENV']['table'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"/>
			<input type="hidden" name="-action" value="history_restore_record"/>
			<input type="hidden" name="history__id"/>
			<input type="hidden" name="-fieldname"/>
			<?php $_from = $this->_tpl_vars['source_record']->_table->keys(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['keyfield']):
?>
				<input type="hidden" name="--__keys__[<?php echo ((is_array($_tmp=$this->_tpl_vars['keyfield']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['source_record']->strval($this->_tpl_vars['keyfield']['name']))) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"/>
			<?php endforeach; endif; unset($_from); ?>
			<input type="hidden" name="-locationid" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['ENV']['APPLICATION_OBJECT']->encodeLocation($_SERVER['QUERY_STRING']))) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"/>
		</form>
		<table class="listing">
			<thead>
			   <tr>
				<th><!-- Actions --></th>
				<th><?php $this->_tag_stack[] = array('translate', array('id' => "templates.Dataface_RecordHistory.LABEL_ID")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>ID<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
				<th><?php $this->_tag_stack[] = array('translate', array('id' => "templates.Dataface_RecordHistory.LABEL_DATE")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Date<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
				<th><?php $this->_tag_stack[] = array('translate', array('id' => "templates.Dataface_RecordHistory.LABEL_LANGUAGE")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Language<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
				<th><?php $this->_tag_stack[] = array('translate', array('id' => "templates.Dataface_RecordHistory.LABEL_USER")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>User<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
				<th><?php $this->_tag_stack[] = array('translate', array('id' => "templates.Dataface_RecordHistory.LABEL_COMMENTS")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Comments<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
			    </tr>
			</thead>
			<tbody>
			<?php $_from = $this->_tpl_vars['log']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['logitem']):
?>
				<tr>
					<td><!-- Actions -->
					<?php echo $this->_plugins['function']['actions'][0][0]->actions(array('var' => 'actions','category' => 'history_record_actions','history__id' => $this->_tpl_vars['logitem']['history__id']), $this);?>

					<ul class="row-item-actions-menu">
						<?php $_from = $this->_tpl_vars['actions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['action']):
?>
						<li><a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['action']['url'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['action']['description'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" <?php if ($this->_tpl_vars['action']['onmouseover']): ?>onmouseover="<?php echo ((is_array($_tmp=$this->_tpl_vars['action']['onmouseover'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"<?php endif; ?>><img src="<?php echo $this->_tpl_vars['ENV']['DATAFACE_URL']; ?>
/images/undo_icon.gif" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['action']['label'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"/></a></li>
						<?php endforeach; endif; unset($_from); ?>
					</ul>
					
					</td>
					<td onclick="historyToolClient.expandHistoryRecord('<?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
', this)"><img src="<?php echo $this->_tpl_vars['ENV']['DATAFACE_URL']; ?>
/images/treeCollapsed.gif" alt="Show details" id="history-<?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
-collapse-image" />&nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</td>
					<td><?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__modified'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</td>
					<td><?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__language'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</td>
					<td><?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__user'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</td>
					<td><?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__comments'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</td>
				</tr>
				<tr style="display: none" id="history-<?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
-row">
					<td></td>
					<td></td>
					<td colspan="4" class="history_details_cell" id="history-<?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
-cell">
					
					<!--This cell is to display the contents of this history record.  It will be loaded with AJAX -->
						<ul id="history-<?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
-viewtabs" class="history-tabs">
							<li id="history-<?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
-viewtabs-details" class="selectedTab"><?php $this->_tag_stack[] = array('translate', array('id' => "templates.Dataface_RecordHistory.LABEL_DETAILS")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Details<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></li>
							<li id="history-<?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
-viewtabs-changes" class="unselectedTab" onclick="historyToolClient.showChanges('<?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
')"><?php $this->_tag_stack[] = array('translate', array('id' => "templates.Dataface_RecordHistory.LABEL_CHANGES")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Changes<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></li>
							<li id="history-<?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
-viewtabs-fromcurrent" class="unselectedTab" onclick="historyToolClient.showFromCurrent('<?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
')"><?php $this->_tag_stack[] = array('translate', array('id' => "templates.Dataface_RecordHistory.LABEL_FROM_CURRENT")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Vs. Current<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></li>
						</ul>
						<div id="history-<?php echo ((is_array($_tmp=$this->_tpl_vars['logitem']['history__id'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
-details-content" class="history-details-pane">
						
						</div>
					
					</td>
				</tr>
			<?php endforeach; endif; unset($_from); ?>
			
			
			</tbody>
		</table>
		<?php else: ?>
			<div class="portalMessage"><?php echo ((is_array($_tmp=$this->_tpl_vars['error']->getMessage())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</div>
		<?php endif; ?>
	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['fill_slot'][0][0]->fill_slot($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['use_macro'][0][0]->use_macro($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>