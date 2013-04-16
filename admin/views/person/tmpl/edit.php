<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');
?>
<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" >
 
	
 
<div class="col50">
		<?php
		echo JHTML::_('tabs.start','tabs', array('useCookie'=>1));
		
		echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'), 'panel1');
		echo $this->loadTemplate('details');
        
		echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_MEMBER_DATA'), 'panel7');
		echo $this->loadTemplate('member_data');

    echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS'), 'panel8');
		echo $this->loadTemplate('extra_fields');
    
		echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_PICTURE'), 'panel2');
		echo $this->loadTemplate('picture');
    
		echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_DESCRIPTION'), 'panel3');
		echo $this->loadTemplate('description');

		echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED'), 'panel4');
		echo $this->loadTemplate('extended');

		echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_FRONTEND'), 'panel5');
		echo $this->loadTemplate('frontend');

		echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_ASSIGN'), 'panel6');
		echo $this->loadTemplate('assign');

    /*
		if (!$this->edit):// add a selection to assign a person directly to a project and team
			echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_ASSIGN'), 'panel6');
			echo $this->loadTemplate('assign');
		endif;
		*/

		echo JHTML::_('tabs.end');
		?>
	</div>	
 
	<div>
		<input type="hidden" name="task" value="person.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
