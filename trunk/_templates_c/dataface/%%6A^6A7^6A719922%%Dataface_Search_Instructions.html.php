<?php /* Smarty version 2.6.18, created on 2012-09-14 15:06:29
         compiled from Dataface_Search_Instructions.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'block', 'Dataface_Search_Instructions.html', 1, false),array('block', 'define_slot', 'Dataface_Search_Instructions.html', 2, false),array('block', 'translate', 'Dataface_Search_Instructions.html', 5, false),)), $this); ?>
<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => 'before_search_form_header'), $this);?>

<?php $this->_tag_stack[] = array('define_slot', array('name' => 'search_form_header')); $_block_repeat=true;$this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
<a class="search-instructions-link">
	<span>
		<?php $this->_tag_stack[] = array('translate', array('id' => "actions.find.show_search_instructions_link")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Show Search Instructions<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	</span>
</a>
<h2><?php echo $this->_tpl_vars['tableLabel']; ?>
 &raquo; <?php $this->_tag_stack[] = array('translate', array('id' => "actions.find.advanced_search_heading")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Advanced Search<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h2>
			
<div class="formHelp">
	<?php $this->_tag_stack[] = array('define_slot', array('name' => 'find_form_help')); $_block_repeat=true;$this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php $this->_tag_stack[] = array('translate', array('id' => "actions.find.short_help_blurb")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Enter your search terms in the fields below and click "Find"<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
</div>
<div id="search-instructions" style="display:none">
<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => 'before_search_instructions'), $this);?>

<?php $this->_tag_stack[] = array('define_slot', array('name' => 'search_instructions')); $_block_repeat=true;$this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>

	<div class="accordion search-instruction-items">
		<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => 'before_seach_instruction_items'), $this);?>

		<?php $this->_tag_stack[] = array('define_slot', array('name' => 'search_instruction_items')); $_block_repeat=true;$this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
			<h6>
				<a href="#">
					<?php $this->_tag_stack[] = array('translate', array('id' => "actions.find.instructions.simple_searches.heading")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Simple Searches<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
				</a>
			</h6>
				
			<div>
				<?php $this->_tag_stack[] = array('translate', array('id' => "actions.find.instructions.simple_searches_body")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Enter the values into the appropriate fields and click the "Find" button to find records the contain the specified patterns.
				<br/> e.g., If you enter "Dog" in one of the fields, it will match records that contain the phrase &quot;Dog&quot;.  This includes &quot;madog&quot; &quot;doggy;&quot; etc...<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
			</div>
			<h6>
				<a href="#">
					<?php $this->_tag_stack[] = array('translate', array('id' => "actions.find.instructions.boolean_searches.heading")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Boolean Searches<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
				</a>
			</h6>
			<div>
				<?php $this->_tag_stack[] = array('translate', array('id' => "actions.find.instructions.boolean_searches.body")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>If you enter criteria into more than one field, they will match only those records that match BOTH criteria.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
			</div>
			<h6>
				<a href="#">
					<?php $this->_tag_stack[] = array('translate', array('id' => "actions.find.instructions.exact_matches.heading")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Exact Matches<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
				</a>
			</h6>
			
			<div>
				<?php $this->_tag_stack[] = array('translate', array('id' => "actions.find.instructions.exact_matches.body")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Prepend an equals sign ("=") to any criteria to force exact matching.<br/>
			e.g., Searching for "=dog" in a field  will match records where that field contains &quot;dog&quot; (and not "doggy";)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
			</div>
			
			<h6>
				<a href="#">
					<?php $this->_tag_stack[] = array('translate', array('id' => "actions.find.instructions.less_than_greater_than.heading")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Less Than / Greater Than Searches<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
				</a>
			</h6>
			<div>
				<?php $this->_tag_stack[] = array('translate', array('id' => "actions.find.instructions.less_than_greater_than.body")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Prepending a less than (&quot;&lt;&quot;) or greater than (&quot;&gt;&quot;) sign to a  will match records where the field has a value LESS THAN (respectively GREATER THAN) the specified value.
				<br/>e.g., Entering &quot;&gt;200&quot; in the Price field will match records with price greater than 200.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> 
			</div>
			
			<h6>
				<a href="#">
					<?php $this->_tag_stack[] = array('translate', array('id' => "actions.find.instructions.range_searches.heading")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Range Searches<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
				</a>
			</h6>
			
			<div>
				<?php $this->_tag_stack[] = array('translate', array('id' => "actions.find.instructions.range_searches.body")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>To match records containing values in a range, use &quot;&lt;LowerRange&gt; .. &lt;UpperRange&gt;&quot; where &lt;LowerRange&gt; is the lower bound on matches and &lt;UpperRange&gt; is the upper bound on matches.
				<br/>e.g., To find records where Price is between 200 and 500 enter &quot;200..500&quot; in the Price field.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
			</div>
			
			
			<h6>
				<a href="#">
					<?php $this->_tag_stack[] = array('translate', array('id' => "actions.find.instructions.wildcard_searches.heading")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Wildcard Searches<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
				</a>
			</h6>
			
			<div>
				<?php $this->_tag_stack[] = array('translate', array('id' => "actions.find.instructions.wildcard_searches.body")); $_block_repeat=true;$this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
				<p>
					To perform wildcard searches, prefix your query with a tilde ("~"), then include one of the supported wildcard characters in your query.
				</p>
				<p><b>Supported Wildcards</b>:</p>
				<ul>
					<li><b>%</b> : Matches any string of length 0 or more characters.</li>
					<li><b>_</b> : Matches a single character.</li>
				</ul>
				
				<p>Examples:</p>
				<ul>
					<li>"~J%y" will match only if the field value begins with "J" and ends with "y".  E.g. It will match both "January" and "Joy", but not "Joyous" or "AJoy"</li>
					<li>"~B%" will match any rows where the field value begins with "B"</li>
					<li>"~B_b" will match any rows beginning and ending with "b" and is 3 characters long.  This would match "bib" and "bob" but not "boob"</li>
				</ul>
				<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['translate'][0][0]->translate($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
			</div>
		<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
		
		<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => 'after_search_instruction_items'), $this);?>

	
	</div>

<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => 'after_search_instructions'), $this);?>

</div>

<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo $this->_plugins['block']['define_slot'][0][0]->define_slot($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php echo $this->_plugins['function']['block'][0][0]->block(array('name' => 'after_search_form_header'), $this);?>