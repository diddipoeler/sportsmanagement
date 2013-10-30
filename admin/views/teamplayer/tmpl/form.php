<?php defined('_JEXEC') or die('Restricted access');

JToolBarHelper::title( JText::_( 'COM_JOOMLEAGUE_ADMIN_TEAMPLAYER_TITLE' ) );

// Set toolbar items for the page
$edit = JRequest::getVar( 'edit', true );
$text = !$edit ? JText::_( 'COM_JOOMLEAGUE_GLOBAL_NEW' ) : JText::_( 'COM_JOOMLEAGUE_GLOBAL_EDIT' );
JLToolBarHelper::save('teamplayer.save');

if ( !$edit )
{
	JLToolBarHelper::cancel('teamplayer.cancel');
}
else
{
	// for existing items the button is renamed `close` and the apply button is showed
	JLToolBarHelper::apply('teamplayer.apply');
	JLToolBarHelper::cancel( 'teamplayer.cancel', 'COM_JOOMLEAGUE_GLOBAL_CLOSE' );
}
JLToolBarHelper::onlinehelp();

?>
<!-- import the functions to move the events between selection lists	-->
<?php
$version = urlencode(JoomleagueHelper::getVersion());
echo JHTML::script( 'JL_eventsediting.js?v='.$version, 'administrator/components/com_joomleague/assets/js/' );
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

echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_EXTENDED'), 'panel3');
echo $this->loadTemplate('extended');

echo JHTML::_('tabs.end');
?>

		<input type="hidden" name="eventschanges_check"	id="eventschanges_check" value="0" />
		<input type="hidden" name="option"				value="com_joomleague" />
		<input type="hidden" name="team_id"				value="<?php echo $this->teamws->team_id; ?>" />
		<input type="hidden" name="cid[]"				value="<?php echo $this->project_player->id; ?>" />
		<input type="hidden" name="task"				value="" />
	</div>
	<?php echo JHTML::_( 'form.token' ); ?>
</form>