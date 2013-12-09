<?php 
defined('_JEXEC') or die('Restricted access');


//JHtml::_('behavior.tooltip');
//JHtml::_('behavior.modal');
//jimport('joomla.html.pane');
jimport( 'joomla.html.html.tabs' );
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
	<div class="col50">
		<?php
//		$pane = JPane::getInstance('tabs',array('startOffset'=>0));
//		echo $pane->startPane('pane');
		
//		echo $pane->startPanel(JText::_('COM_JOOMLEAGUE_TABS_ROSTERPOSITIONS_SYSTEM'),'panel1');
//		echo $this->loadTemplate('details');
//		echo $pane->endPanel();
		
//		echo $pane->startPanel(JText::_('COM_JOOMLEAGUE_TABS_ROSTERPOSITIONS_PLAYGROUND'),'panel2');
//    echo $this->loadTemplate('playground_jquery');
//    echo $pane->endPanel();
    
    
//    echo $pane->startPanel(JText::_('COM_JOOMLEAGUE_TABS_ROSTERPOSITIONS'),'panel3');
//    echo $this->loadTemplate('extended');
//    echo $pane->endPanel();
    
//     echo $pane->startPanel(JText::_('COM_JOOMLEAGUE_TABS_ROSTERPOSITIONS_PLAYGROUND'),'panel3');
//     echo $this->loadTemplate('playground');
//     echo $pane->endPanel();

//		echo $pane->endPane();

echo JHtml::_('tabs.start', 'tab_group_id', $options);
 
echo JHtml::_('tabs.panel', JText::_('PANEL_1_TITLE'), 'panel_1_id');
echo $this->loadTemplate('details');
 
echo JHtml::_('tabs.panel', JText::_('PANEL_2_TITLE'), 'panel_2_id');
echo $this->loadTemplate('playground_jquery');

echo JHtml::_('tabs.panel', JText::_('PANEL_3_TITLE'), 'panel_3_id');
echo $this->loadTemplate('extended');
 
echo JHtml::_('tabs.end');


		?>
	</div>
	<div class="clr"></div>
	
	
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="task" value="rosterposition.edit" />
	<?php echo JHtml::_('form.token')."\n"; ?>
</form>