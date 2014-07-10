<?php 
/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.26
* @file		components/sportsmanagement/views/ranking/tmpl/default.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license This file is part of Joomla Sports Management.
*
* Joomla Sports Management is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Joomla Sports Management is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Joomla Sports Management. If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von Joomla Sports Management.
*
* Joomla Sports Management ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* Joomla Sports Management wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
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
JHtml::_( 'behavior.modal' );
//echo ' config<br><pre>'.print_r($this->config,true).'</pre>';   

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$this->kmlpath = JUri::root().'tmp'.DS.$this->project->id.'-ranking.kml';
$this->kmlfile = $this->project->id.'-ranking.kml';

?>
<script>

jQuery(document).ready(function() {
    // document is loaded and DOM is ready
    //alert("document is ready");
var width = get_windowPopUpWidth();
var heigth = get_windowPopUpHeight();
var linkbugtracker = "<?php echo COM_SPORTSMANAGEMENT_SHOW_BUGTRACKER_SERVER ?>";
var linkonlinehelp = "<?php echo COM_SPORTSMANAGEMENT_SHOW_HELP_SERVER ?>";
var view = "<?php echo COM_SPORTSMANAGEMENT_SHOW_VIEW ?>";

document.getElementById("bugtracker-link").innerHTML='Bug-Tracker <a class="modal" rel="{handler: \'iframe\', size: {x: ' + width + ', y: ' + heigth + '}}" href="' + linkbugtracker + '">Bug-Tracker</a>';
document.getElementById("onlinehelp-link").innerHTML='Onlinehelp <a class="modal" rel="{handler: \'iframe\', size: {x: ' + width + ', y: ' + heigth + '}}" href="' + linkonlinehelp + 'SM-Frontend:' + view + '">Onlinehelp</a>';    
});

jQuery(window).load(function() {
    // page is fully loaded, including all frames, objects and images
    //alert("window is loaded");
});

</script>

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
    if ($this->config['show_table_1']==1)
	{
    $params .= $startoutput.JText::_($this->config['table_text_1']).'}';
    $params .= $this->loadTemplate('ranking');
    }
    if ($this->config['show_table_2']==1)
	{
    $params .= $startoutput.JText::_($this->config['table_text_2']).'}';
    $params .= $this->loadTemplate('ranking_home');
    }
    if ($this->config['show_table_3']==1)
	{ 
    $params .= $startoutput.JText::_($this->config['table_text_3']).'}';
    $params .= $this->loadTemplate('ranking_away');  
    }
    if ($this->config['show_half_of_season']==1)
	{
	if ($this->config['show_table_4']==1)
	{   
	$params .= $startoutput.JText::_($this->config['table_text_4']).'}';
    $params .= $this->loadTemplate('ranking_first');
    }
    if ($this->config['show_table_5']==1)
	{ 
    $params .= $startoutput.JText::_($this->config['table_text_5']).'}';
    $params .= $this->loadTemplate('ranking_second');
    } 
    }
    
    $params .= $endoutput;
    echo JHtml::_('content.prepare', $params);
    }
    else
    {
    $idxTab = 1;
  echo JHtml::_('tabs.start','tabs_ranking', array('useCookie'=>1));
  if ($this->config['show_table_1']==1)
	{
  echo JHtml::_('tabs.panel', JText::_($this->config['table_text_1']), 'panel'.($idxTab++));
		echo $this->loadTemplate('ranking');
        }
        if ($this->config['show_table_2']==1)
	{
        echo JHtml::_('tabs.panel', JText::_($this->config['table_text_2']), 'panel'.($idxTab++));
		echo $this->loadTemplate('ranking_home');
        }
        if ($this->config['show_table_3']==1)
	{
        echo JHtml::_('tabs.panel', JText::_($this->config['table_text_3']), 'panel'.($idxTab++));
		echo $this->loadTemplate('ranking_away');
    }
    if ($this->config['show_half_of_season']==1)
	{
	if ($this->config['show_table_4']==1)
	{   
	echo JHtml::_('tabs.panel', JText::_($this->config['table_text_4']), 'panel'.($idxTab++));
	echo $this->loadTemplate('ranking_first');
    }
    if ($this->config['show_table_5']==1)
	{
    echo JHtml::_('tabs.panel', JText::_($this->config['table_text_5']), 'panel'.($idxTab++));
	echo $this->loadTemplate('ranking_second');
    }
    }   
        
echo JHtml::_('tabs.end');    
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
	
    if ($this->config['show_projectinfo'] == 1)
	{
		echo $this->loadTemplate('projectinfo');
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
