<?php 
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');

//        L O A D   M O O T O O L S

JHtml::_('behavior.mootools');

//        L O A D   C A L E N D A R

JHtml::_('behavior.calendar');

//        I M P O R T   T A B S   C L A S S

jimport('joomla.html.pane');


?>



<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
	
		<?php
        
//        I N I T I A L I Z E   T H E   T A B - C L A S S

    $this->_main_tab = JPane::getInstance( );

    // output the custom fields

    echo $this->_main_tab->startPane( 'rosterposition_tabs' );

    //echo $this->loadTemplate('details');        
        
        
  echo $this->_main_tab->startPanel(JText::_('Assignments'),'assignments-page');

    echo $this->loadTemplate('details');

    echo $this->_main_tab->endPanel();       
        
 echo $this->_main_tab->endPane();        
        
        
        
        
        
        
        
        
        
//		$pane = JPane::getInstance('tabs',array('startOffset'=>0));
//		echo $pane->startPane('pane');
		
//		echo $pane->startPanel(JText::_('COM_SPORTSMANAGEMENT_TABS_ROSTERPOSITIONS_SYSTEM'),'panel1');
//		echo $this->loadTemplate('details');
//		echo $pane->endPanel();
		
//		echo $pane->startPanel(JText::_('COM_SPORTSMANAGEMENT_TABS_ROSTERPOSITIONS_PLAYGROUND'),'panel2');
//    echo $this->loadTemplate('playground_jquery');
//    echo $pane->endPanel();
    
    
//    echo $pane->startPanel(JText::_('COM_SPORTSMANAGEMENT_TABS_ROSTERPOSITIONS'),'panel3');
//    echo $this->loadTemplate('extended');
//    echo $pane->endPanel();
    
//     echo $pane->startPanel(JText::_('COM_SPORTSMANAGEMENT_TABS_ROSTERPOSITIONS_PLAYGROUND'),'panel3');
//     echo $this->loadTemplate('playground');
//     echo $pane->endPanel();

//		echo $pane->endPane();

/*
echo JHtml::_('tabs.start', 'tab_group_id', $options);
 
echo JHtml::_('tabs.panel', JText::_('PANEL_1_TITLE'), 'panel_1_id');
echo $this->loadTemplate('details');
 
echo JHtml::_('tabs.panel', JText::_('PANEL_2_TITLE'), 'panel_2_id');
echo $this->loadTemplate('playground_jquery');

echo JHtml::_('tabs.panel', JText::_('PANEL_3_TITLE'), 'panel_3_id');
echo $this->loadTemplate('extended');
 
echo JHtml::_('tabs.end');
*/

		?>
	
	
	
	
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="task" value="rosterposition.edit" />
	<?php echo JHtml::_('form.token')."\n"; ?>
</form>