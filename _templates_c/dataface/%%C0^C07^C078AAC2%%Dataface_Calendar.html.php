<?php /* Smarty version 2.6.18, created on 2012-09-17 22:59:56
         compiled from Dataface_Calendar.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'use_macro', 'Dataface_Calendar.html', 1, false),array('block', 'fill_slot', 'Dataface_Calendar.html', 3, false),)), $this); ?>
<?php $this->_tag_stack[] = array('use_macro', array('file' => "Dataface_Main_Template.html")); $_block_repeat=true;$this->_plugins['block']['use_macro'][0][0]->use_macro($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>

	<?php $this->_tag_stack[] = array('fill_slot', array('name' => 'main_section')); $_block_repeat=true;$this->_plugins['block']['fill_slot'][0][0]->fill_slot($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
		<?php echo $this->_tpl_vars['filters']; ?>

		<table class="calendar">
		<tr><td class="calendar-left">
		<div class="calendar-nav"><a href="<?php echo $this->_tpl_vars['nav']['prev']['url']; ?>
">&lt; <?php echo $this->_tpl_vars['nav']['prev']['label']; ?>
</a> 
		<b><?php echo $this->_tpl_vars['nav']['current']['label']; ?>
</b> <a href="<?php echo $this->_tpl_vars['nav']['next']['url']; ?>
"><?php echo $this->_tpl_vars['nav']['next']['label']; ?>
 &gt;</a>
		</div>
	
		<script language="javascript" type="text/javascript" src="<?php echo $this->_tpl_vars['ENV']['DATAFACE_URL']; ?>
/js/dfCalendar.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo $this->_tpl_vars['ENV']['DATAFACE_URL']; ?>
/js/ajaxgold.js"></script>
		<?php echo '
		<script language="javascript"><!--
		
		function handleGetEventDescription(text){
			//alert(text);
			eval(\'var data = \'+text);
			var div = document.getElementById(data[\'record_id\']+\'-description\');
			div.innerHTML = data[\'details\'];
			if ( df_add_editable_awareness ){
				var tables = div.getElementsByTagName(\'table\');
				df_add_editable_awareness(tables[0]);
			}
			
		}
		Dataface.Calendar.Event.prototype.getDescription = function(){
			
			getDataReturnText(DATAFACE_SITE_HREF+\'?-action=ajax_get_event_details&--record_id=\'+escape(this.record_id), handleGetEventDescription); 
			return \'<div id="\'+this.record_id+\'-description"><img src="\'+DATAFACE_URL+\'/images/progress.gif" alt="Please wait"></div>\';
			
		};
		var df_calendar = new Dataface.Calendar(null, new Date('; ?>
<?php echo $this->_tpl_vars['currentTime']; ?>
<?php echo '*1000+90000000));
		'; ?>
<?php echo $this->_tpl_vars['event_data']; ?>
<?php echo '
		for ( var i=0; i<events.length; i++){
			df_calendar.events.add(new Dataface.Calendar.Event(null, events[i]));
		}
		
		document.writeln(df_calendar.drawMonth());
		df_calendar.detailsPanel = \'detailsPanel\';
		df_calendar.dayPanel = \'detailsPanel\';
		
		//--></script>
		'; ?>

		<div id="dayPanel"></div>
		</td><td class="calendar-right">
		<div id="detailsPanel"></div>
		</td></tr>
		</table>
	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['fill_slot'][0][0]->fill_slot($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['use_macro'][0][0]->use_macro($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>