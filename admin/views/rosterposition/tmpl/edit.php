<?php defined('_JEXEC') or die('Restricted access');

?>


<?PHP



JHTML::_('behavior.tooltip');JHTML::_('behavior.modal');
jimport('joomla.html.pane');

// Set toolbar items for the page
//$edit=JRequest::getVar('edit',true);
//$text=!$edit ? JText::_('COM_JOOMLEAGUE_GLOBAL_NEW') : JText::_('COM_JOOMLEAGUE_GLOBAL_EDIT');

/*
JToolBarHelper::save('rosterposition.save');

if (!$edit)
{
	JToolBarHelper::title(JText::_('COM_JOOMLEAGUE_ADMIN_ROSTERPOSITIONS_ADD_NEW'));
	JToolBarHelper::divider();
	JToolBarHelper::cancel('rosterposition.cancel');
}
else
{
	// for existing items the button is renamed `close` and the apply button is showed
	JToolBarHelper::title(JText::_('COM_JOOMLEAGUE_ADMIN_ROSTERPOSITIONS_EDIT'));
	JToolBarHelper::apply('rosterposition.apply');
	JToolBarHelper::divider();
	JToolBarHelper::cancel('rosterposition.cancel','COM_JOOMLEAGUE_GLOBAL_CLOSE');
}
JToolBarHelper::divider();
JLToolBarHelper::onlinehelp();
*/
?>

<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton)
	{
		var form=document.adminForm;
		if (pressbutton == 'cancel')
		{
			submitform(pressbutton);
			return;
		}

		// do field validation
		if (form.name.value == "")
		{
			alert("<?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROSTERPOSITIONS_NO_NAME',true); ?>");
		}
		else
		{
			submitform(pressbutton);
		}
		// do field validation
		if (form.short_name.value == "")
		{
			alert("<?php echo JText::_('COM_JOOMLEAGUE_ADMIN_ROSTERPOSITIONS_NO_SHORT_NAME',true); ?>");
		}
		else
		{
			submitform(pressbutton);
		}		
	}
</script>
<style type="text/css">
	table.paramlist td.paramlist_key {
		width: 92px;
		text-align: left;
		height: 30px;
	}
</style>

<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" >
	<div class="col50">
		<?php
		$pane = JPane::getInstance('tabs',array('startOffset'=>0));
		echo $pane->startPane('pane');
		
		echo $pane->startPanel(JText::_('COM_JOOMLEAGUE_TABS_ROSTERPOSITIONS_SYSTEM'),'panel1');
		echo $this->loadTemplate('details');
		echo $pane->endPanel();
		
		echo $pane->startPanel(JText::_('COM_JOOMLEAGUE_TABS_ROSTERPOSITIONS_PLAYGROUND'),'panel2');
    echo $this->loadTemplate('playground_jquery');
    echo $pane->endPanel();
    
    
    echo $pane->startPanel(JText::_('COM_JOOMLEAGUE_TABS_ROSTERPOSITIONS'),'panel3');
    echo $this->loadTemplate('extended');
    echo $pane->endPanel();
    
//     echo $pane->startPanel(JText::_('COM_JOOMLEAGUE_TABS_ROSTERPOSITIONS_PLAYGROUND'),'panel3');
//     echo $this->loadTemplate('playground');
//     echo $pane->endPanel();
    
    

		echo $pane->endPane();
		?>
	</div>
	<div class="clr"></div>
	
	
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="task" value="rosterposition.edit" />
	<?php echo JHTML::_('form.token')."\n"; ?>
</form>