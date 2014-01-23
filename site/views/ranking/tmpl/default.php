<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.switcher');



// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$this->kmlpath = JURI::root().'tmp'.DS.$this->project->id.'-ranking.kml';

?>
<div class="joomleague">
	<?php
	echo $this->loadTemplate('projectheading');

	if ($this->config['show_sectionheader'])
	{
		echo $this->loadTemplate('sectionheader');
	}

	if ($this->config['show_rankingnav']==1)
	{
		echo $this->loadTemplate('rankingnav');
	}

	if ($this->config['show_ranking']==1)
	{
	   // tabs anzeigen
       //echo 'currentRanking<pre>',print_r($this->currentRanking ,true),'</pre>';
       //echo 'homeRank<pre>',print_r($this->homeRank ,true),'</pre>';
       //echo 'awayRank<pre>',print_r($this->awayRank ,true),'</pre>';
    
   
    if ( JPluginHelper::isEnabled('content', 'jw_ts') )
    {
    $params = '';
    $startoutput = '{tab=';
    $endoutput = '{/tabs}';
    $params .= $startoutput.JText::_($this->config['table_text_1']).'}';
    $params .= $this->loadTemplate('ranking');
    $params .= $startoutput.JText::_($this->config['table_text_2']).'}';
    $params .= $this->loadTemplate('ranking_home'); 
    $params .= $startoutput.JText::_($this->config['table_text_3']).'}';
    $params .= $this->loadTemplate('ranking_away');  
    
    if ($this->config['show_half_of_season']==1)
	{
	$params .= $startoutput.JText::_($this->config['table_text_4']).'}';
    $params .= $this->loadTemplate('ranking_first'); 
    $params .= $startoutput.JText::_($this->config['table_text_5']).'}';
    $params .= $this->loadTemplate('ranking_second'); 
    }
    
    $params .= $endoutput;
    echo JHTML::_('content.prepare', $params);
    }
    else
    {
    $idxTab = 1;
  echo JHTML::_('tabs.start','tabs_ranking', array('useCookie'=>1));
  echo JHTML::_('tabs.panel', JText::_($this->config['table_text_1']), 'panel'.($idxTab++));
		echo $this->loadTemplate('ranking');
        echo JHTML::_('tabs.panel', JText::_($this->config['table_text_2']), 'panel'.($idxTab++));
		echo $this->loadTemplate('ranking_home');
        echo JHTML::_('tabs.panel', JText::_($this->config['table_text_3']), 'panel'.($idxTab++));
		echo $this->loadTemplate('ranking_away');
    
    if ($this->config['show_half_of_season']==1)
	{
	echo JHTML::_('tabs.panel', JText::_($this->config['table_text_4']), 'panel'.($idxTab++));
	echo $this->loadTemplate('ranking_first');
    echo JHTML::_('tabs.panel', JText::_($this->config['table_text_5']), 'panel'.($idxTab++));
	echo $this->loadTemplate('ranking_second');
    }   
        
echo JHTML::_('tabs.end');    
    }
    
    }

	if ($this->config['show_colorlegend']==1)
	{
		echo $this->loadTemplate('colorlegend');
	}
	
	if ($this->config['show_explanation']==1)
	{
		echo $this->loadTemplate('explanation');
	}
	
	if ($this->config['show_pagnav']==1)
	{
		echo $this->loadTemplate('pagnav');
	}
	
	if ($this->config['show_notes'] == 1)
	{
		echo $this->loadTemplate('notes');
	}
	
	if (($this->config['show_ranking_maps'])==1)
	{ 
		echo $this->loadTemplate('googlemap');
	}
	
	if ($this->config['show_help'] == "1")
	{
		echo $this->loadTemplate('hint');
	}
    
    if (($this->overallconfig['show_project_rss_feed']) == 1   )
	{
		//if ( !empty($this->rssfeedoutput) )
//       {
//       echo $this->loadTemplate('rssfeed-table'); 
//       }
		if ( $this->rssfeeditems )
        {
        echo $this->loadTemplate('rssfeed');    
        }
	}

	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
