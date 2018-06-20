<?php
/** SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 * @version   1.0.05
 * @file      deafult_tabs.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage ranking
 */

defined('_JEXEC') or die('Restricted access');
  
echo JHtml::_('bootstrap.startTabSet', 'defaulttabs', array('active' => 'rankingall')); //start tab set
echo JHtml::_('bootstrap.addTab', 'defaulttabs', 'rankingall', JText::_('ranking'));

if ($this->config['show_table_1'] ||
        $this->config['show_table_2'] ||
        $this->config['show_table_3'] ||
        $this->config['show_table_4'] ||
        $this->config['show_table_5']) {

echo JHtml::_('bootstrap.startTabSet', 'defaulttabsranking', array('active' => 'show_table_1')); //start tab set  
if ($this->config['show_table_1']) {
echo JHtml::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_1', JText::_($this->config['table_text_1']));
echo $this->loadTemplate('ranking');  
echo JHtml::_('bootstrap.endTab');  
}  
if ($this->config['show_table_2']) {
echo JHtml::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_2', JText::_($this->config['table_text_2']));
echo $this->loadTemplate('ranking_home');  
echo JHtml::_('bootstrap.endTab');   
} 
if ($this->config['show_table_3']) {
echo JHtml::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_3', JText::_($this->config['table_text_3']));
echo $this->loadTemplate('ranking_away');  
echo JHtml::_('bootstrap.endTab');   
} 
if ($this->config['show_table_4']) {
echo JHtml::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_4', JText::_($this->config['table_text_4']));
echo $this->loadTemplate('ranking_first');  
echo JHtml::_('bootstrap.endTab');   
} 
if ($this->config['show_table_5']) {
echo JHtml::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_5', JText::_($this->config['table_text_5']));
echo $this->loadTemplate('ranking_second');  
echo JHtml::_('bootstrap.endTab');   
}   
echo JHtml::_('bootstrap.endTabSet'); //end tab set    
}




if ($this->config['show_colorlegend']) {
echo $this->loadTemplate('colorlegend');
}
if ($this->config['show_explanation']) {
echo $this->loadTemplate('explanation');
}
if ($this->config['show_help']) {
echo $this->loadTemplate('hint');
}
echo JHtml::_('bootstrap.endTab');

                

                if ($this->config['show_projectinfo']) {
                  echo JHtml::_('bootstrap.addTab', 'defaulttabs', 'projectinfo', JText::_('projectinfo'));
                    echo $this->loadTemplate('projectinfo');
                  echo JHtml::_('bootstrap.endTab');
                }
                if ($this->config['show_notes']) {
                  echo JHtml::_('bootstrap.addTab', 'defaulttabs', 'notes', JText::_('notes'));
                    echo $this->loadTemplate('notes');
                  echo JHtml::_('bootstrap.endTab');
                }
                if ($this->config['show_ranking_maps']) {
                  echo JHtml::_('bootstrap.addTab', 'defaulttabs', 'googlemap', JText::_('googlemap'));
                    echo $this->loadTemplate('googlemap');
                  echo JHtml::_('bootstrap.endTab');
                }
                
                if ($this->overallconfig['show_project_rss_feed']) {
                    if ($this->rssfeeditems) {
                      echo JHtml::_('bootstrap.addTab', 'defaulttabs', 'ranking', JText::_('COM_EXAMPLE_NAME'));
                        echo $this->loadTemplate('rssfeed');
                      echo JHtml::_('bootstrap.endTab');
                    }
                }

echo JHtml::_('bootstrap.endTabSet'); //end tab set  
?>
