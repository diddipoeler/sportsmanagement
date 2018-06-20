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
  
echo JHtml::_('bootstrap.startTabSet', 'defaulttabs', array('active' => 'ranking')); //start tab set
echo JHtml::_('bootstrap.addTab', 'defaulttabs', 'ranking', JText::_('ranking'));
echo $this->loadTemplate('ranking');
if ($this->config['show_colorlegend']) {
                    echo $this->loadTemplate('colorlegend');
                }
echo JHtml::_('bootstrap.endTab');

                if ($this->config['show_explanation']) {
                  echo JHtml::_('bootstrap.addTab', 'defaulttabs', 'explanation', JText::_('explanation'));
                    echo $this->loadTemplate('explanation');
                  echo JHtml::_('bootstrap.endTab');
                }

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
                if ($this->config['show_help']) {
                  echo JHtml::_('bootstrap.addTab', 'defaulttabs', 'hint', JText::_('hint'));
                    echo $this->loadTemplate('hint');
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
