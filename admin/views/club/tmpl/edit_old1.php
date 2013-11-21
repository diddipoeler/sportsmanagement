<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
//jimport( 'joomla.html.html.tabs' );
jimport('joomla.html.pane');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');

$options = array(
    'onActive' => 'function(title, description){
        description.setStyle("display", "block");
        title.addClass("open").removeClass("closed");
    }',
    'onBackground' => 'function(title, description){
        description.setStyle("display", "none");
        title.addClass("closed").removeClass("open");
    }',
    'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
    'useCookie' => true, // this must not be a string. Don't use quotes.
);

?>
<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
 
<div  class="col50">
<?php

$pane = JPane::getInstance('tabs',array('startOffset'=>0));
echo $pane->startPane('pane');

echo $pane->startPanel(JText::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'),'panel1');
echo $this->loadTemplate('details');

        
//echo JHtml::_('tabs.start','tabs',$options);
//echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'), 'panel1');
?>

<?PHP
//echo $this->loadTemplate('details');
?>
<?PHP
if ( $this->map )
{
echo $this->loadTemplate('maps');
}
echo $pane->endPanel();
?>

<?PHP
echo $pane->startPanel(JText::_('COM_SPORTSMANAGEMENT_TABS_PICTURE'),'panel2');
//echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_PICTURE'), 'panel2');
echo $this->loadTemplate('picture');
echo $pane->endPanel();

echo $pane->startPanel(JText::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED'),'panel3');
//echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED'), 'panel3');
echo $this->loadTemplate('extended');
echo $pane->endPanel();

if ( $this->checkextrafields )
{
    echo $pane->startPanel(JText::_('COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS'),'panel4');
    //echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS'), 'panel4');
    echo $this->loadTemplate('extra_fields');
    echo $pane->endPanel();
}

//echo JHtml::_('tabs.end');
echo $pane->endPane();
?>	
 
	
	</div>
 <div class="clr"></div>
	<div>
		<input type="hidden" name="task" value="club.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
