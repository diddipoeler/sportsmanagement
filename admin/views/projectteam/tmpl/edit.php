<?php defined('_JEXEC') or die('Restricted access');

?>
<!-- import the functions to move the events between selection lists	-->
<?php
$version = urlencode(JoomleagueHelper::getVersion());
echo JHTML::script('JL_eventsediting.js?v='.$version,'administrator/components/com_joomleague/assets/js/');
?>
<form action="index.php" method="post" id="adminForm">

	<div class="col50">
<?php
echo JHTML::_('tabs.start','tabs', array('useCookie'=>1));
echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_DETAILS'), 'panel1');
echo $this->loadTemplate('details');

echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_PICTURE'), 'panel2');
echo $this->loadTemplate('picture');

echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_DESCRIPTION'), 'panel3');
echo $this->loadTemplate('description');

echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_TRAINING'), 'panel4');
echo $this->loadTemplate('training');

echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_EXTENDED'), 'panel5');
echo $this->loadTemplate('extended');

echo JHTML::_('tabs.end');
?>
		<div class="clr"></div>
		<input type="hidden" name="eventschanges_check"	value="0"	id="eventschanges_check" />
		<input type="hidden" name="option"				value="com_joomleague" />
		<input type="hidden" name="cid[]"				value="<?php echo $this->project_team->id; ?>" />
		<input type="hidden" name="project_id"			value="<?php echo $this->projectws->id; ?>" />
		<input type="hidden" name="task"				value="" id='task'/>
	</div>
	<?php echo JHTML::_('form.token'); ?>

</form>