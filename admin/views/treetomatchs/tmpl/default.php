<?php 

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
jimport('joomla.html.pane');

//JToolbarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_TITLE'));
//
////JLToolBarHelper::save();
//JToolbarHelper::custom('treetomatch.editlist','upload.png','upload_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_BUTTON_ASSIGN'),false);
//JToolbarHelper::back('Back','index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode.display&tid='.$this->jinput->get('tid').'&pid='.$this->jinput->get('pid'));

//JToolbarHelper::help('screen.joomleague',true);
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>



<!-- Start games list -->
<form action="<?php echo $this->request_url; ?>" method="post" id='adminForm' name='adminForm'>
<?php

if(version_compare(JVERSION,'3.0.0','ge')) 
{
echo $this->loadTemplate('joomla3');
}
else
{
echo $this->loadTemplate('joomla2');    
}


echo $this->loadTemplate('data');

?>
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="act" value="" />
<input type="hidden" name="task" value="treetomatchs.display" id="task" />
<?php echo JHtml::_('form.token')."\n"; ?>
</form>
