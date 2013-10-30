<?php defined('_JEXEC') or die('Restricted access');?>
<!-- import the functions to move the events between selection lists	-->
<?php
$version = urlencode(JoomleagueHelper::getVersion());
echo JHtml::script( 'JL_eventsediting.js?v='.$version, 'administrator/components/com_joomleague/assets/js/' );
?>
<form action="index.php" method="post" id="adminForm">
	<div class="col50">
		<?php
		echo JHtml::_('tabs.start','tabs', array('useCookie'=>1));
		echo JHtml::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_DETAILS'), 'panel1');
		echo $this->loadTemplate('details');
		
		echo JHtml::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_PICTURE'), 'panel2');
		echo $this->loadTemplate('picture');
		
		echo JHtml::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_DESCRIPTION'), 'panel3');
		echo $this->loadTemplate('description');
		
		echo JHtml::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_EXTENDED'), 'panel3');
		echo $this->loadTemplate('extended');
		
		echo JHtml::_('tabs.end');
		?>	
		<input type="hidden" name="eventschanges_check"	value="0" id="eventschanges_check" />
		<input type="hidden" name="option"				value="com_joomleague" />
		<input type="hidden" name="team"				value="<?php echo $this->teamws->id; ?>" />
		<input type="hidden" name="cid[]"				value="<?php echo $this->project_teamstaff->id; ?>" />
		<input type="hidden" name="task"				value="" />
	</div>
	<?php echo JHtml::_( 'form.token' ); ?>
</form>