<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 *
 * @version    1.0.05
 * @file       deafult_tabs.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage ranking
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

echo HTMLHelper::_('bootstrap.startTabSet', 'defaulttabs', array('active' => 'rankingall')); //start tab set
echo HTMLHelper::_('bootstrap.addTab', 'defaulttabs', 'rankingall', Text::_('ranking'));

if ($this->config['show_table_1']
    || $this->config['show_table_2']
    || $this->config['show_table_3']
    || $this->config['show_table_4']
    || $this->config['show_table_5']
) {

    echo HTMLHelper::_('bootstrap.startTabSet', 'defaulttabsranking', array('active' => 'show_table_1')); //start tab set
    if ($this->config['show_table_1']) {
        echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_1', Text::_($this->config['table_text_1']));
        echo $this->loadTemplate('ranking');
        echo HTMLHelper::_('bootstrap.endTab');
    }
    if ($this->config['show_table_2']) {
        echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_2', Text::_($this->config['table_text_2']));
        echo $this->loadTemplate('ranking_home');
        echo HTMLHelper::_('bootstrap.endTab'); 
    }
    if ($this->config['show_table_3']) {
        echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_3', Text::_($this->config['table_text_3']));
        echo $this->loadTemplate('ranking_away');
        echo HTMLHelper::_('bootstrap.endTab'); 
    }
    if ($this->config['show_table_4']) {
        echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_4', Text::_($this->config['table_text_4']));
        echo $this->loadTemplate('ranking_first');
        echo HTMLHelper::_('bootstrap.endTab'); 
    }
    if ($this->config['show_table_5']) {
        echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_5', Text::_($this->config['table_text_5']));
        echo $this->loadTemplate('ranking_second');
        echo HTMLHelper::_('bootstrap.endTab'); 
    } 
    echo HTMLHelper::_('bootstrap.endTabSet'); //end tab set  
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
echo HTMLHelper::_('bootstrap.endTab');

              

if ($this->config['show_projectinfo']) {
    echo HTMLHelper::_('bootstrap.addTab', 'defaulttabs', 'projectinfo', Text::_('projectinfo'));
    echo $this->loadTemplate('projectinfo');
    echo HTMLHelper::_('bootstrap.endTab');
}
if ($this->config['show_notes']) {
    echo HTMLHelper::_('bootstrap.addTab', 'defaulttabs', 'notes', Text::_('notes'));
    echo $this->loadTemplate('notes');
    echo HTMLHelper::_('bootstrap.endTab');
}
if ($this->config['show_ranking_maps']) {
    echo HTMLHelper::_('bootstrap.addTab', 'defaulttabs', 'googlemap', Text::_('googlemap'));
    echo $this->loadTemplate('googlemap');
    echo HTMLHelper::_('bootstrap.endTab');
}
              
if ($this->overallconfig['show_project_rss_feed']) {
    if ($this->rssfeeditems) {
        echo HTMLHelper::_('bootstrap.addTab', 'defaulttabs', 'ranking', Text::_('COM_EXAMPLE_NAME'));
        echo $this->loadTemplate('rssfeed');
        echo HTMLHelper::_('bootstrap.endTab');
    }
}

echo HTMLHelper::_('bootstrap.endTabSet'); //end tab set
?>
