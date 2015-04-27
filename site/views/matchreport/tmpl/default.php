<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );
JHtml::_('behavior.tooltip');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

switch ( $this->project->sport_type_name )
{
    case 'COM_SPORTSMANAGEMENT_ST_TENNIS';
    echo $this->loadTemplate('projectheading');
    echo $this->loadTemplate('sectionheader');
    
    echo $this->loadTemplate('result');
    echo $this->loadTemplate('details');
    
    echo $this->loadTemplate('sporttype_tennis');
    break;
    default:

$hasMatchPlayerStats = false;
$hasMatchStaffStats = false;

if (!empty($this->matchplayerpositions ))
{
	$hasMatchPlayerStats = false;
	$hasMatchStaffStats = false;
	foreach ( $this->matchplayerpositions as $pos )
	{
		if(isset($this->stats[$pos->position_id]) && count($this->stats[$pos->position_id])>0) {
			foreach ($this->stats[$pos->position_id] as $stat) {
				if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()) {
					$hasMatchPlayerStats = true;
					break;
				}
			}
		}
	}	
	foreach ( $this->matchstaffpositions as $pos )
	{
		if(isset($this->stats[$pos->position_id]) && count($this->stats[$pos->position_id])>0) {
			foreach ($this->stats[$pos->position_id] as $stat) {
				if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()) {
					$hasMatchStaffStats = true;
				}
			}
		}
	}
}    


?>

<div class="">

<?php

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo $this->loadTemplate('debug');
}
	echo $this->loadTemplate('projectheading');

	if (($this->config['show_sectionheader'])==1)
	{
		echo $this->loadTemplate('sectionheader');
	}

	if (($this->config['show_result'])==1)
	{
		echo $this->loadTemplate('result');
	}
    
    if ( !empty( $this->matchevents ) )
	{
    if (($this->config['show_timeline_under_results'])==1)
	{
		echo $this->loadTemplate('timeline');
	}
    }
    
  // ################################################################
  // diddipoeler
  // aufbau der templates
  $output = array();
  if (($this->config['show_details'])==1)
	{
		$output['COM_SPORTSMANAGEMENT_MATCHREPORT_DETAILS'] = 'details';
	}
  if (($this->config['show_extended'])==1 && $this->extended )
	{
        $output['COM_SPORTSMANAGEMENT_TABS_EXTENDED'] = 'extended';
	}
	if (($this->config['show_roster'])==1)
	{
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE_UP_PLAYER'] = 'roster';
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE_UP_STAFF'] = 'staff';
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTES'] = 'subst';
	}
    if (($this->config['show_roster_playground'])==1)
	{
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_PLAYGROUND'] = 'rosterplayground';
	}
    if ( !empty( $this->matchevents ) )
	{
		if (($this->config['show_timeline'])==1 && $this->config['show_timeline_under_results']==0 )
		{
            $output['COM_SPORTSMANAGEMENT_MATCHREPORT_TIMELINE'] = 'timeline';
		}
        if (($this->config['show_events'])==1)
		{
			switch ($this->config['use_tabs_events'])
			{
				case 0:
					/** No tabs */
					if ( !empty( $this->eventtypes ) ) 
                    {
						$output['COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'] = 'events';
					}
					break;
				case 1:
					/** Tabs */
					if ( !empty( $this->eventtypes ) ) 
                    {
						//echo $this->loadTemplate('events_tabs');
                        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'] = 'events_tabs';
					}
					break;
				case 2:
					/** Table/Ticker layout */
					//echo $this->loadTemplate('events_ticker');
                    $output['COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'] = 'events_ticker';
					break;
			}
                    
                    
                    
                    
        }
        
    }    
    if (($this->config['show_stats'])==1 && ( $hasMatchPlayerStats || $hasMatchStaffStats ) )
	{
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_STATISTICS'] = 'stats';
	}

	if (($this->config['show_summary'])==1 && $this->match->summary )
	{
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_SUMMARY'] = 'summary';
	}
    
    if (($this->config['show_article'])==1 )
	{
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_ARTICLE'] = 'article';
	}
    
    if (($this->config['show_commentary'])==1 && $this->matchcommentary )
	{
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_COMMENTARY'] = 'commentary';
	}
	
	if (($this->config['show_pictures'])==1 && isset($this->matchimages) )
	{
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_PICTURES'] = 'pictures';
	} 
    break;
    
}       


    if(version_compare(JVERSION,'3.0.0','ge')) 
    //if(version_compare(JVERSION,'2.5.0','ge'))
        {
        $count = 0;
    foreach ($output as $templ)
    {
    
    if ( !$count )
    {
    // Define slides options
        $slidesOptions = array(
            "active" => "slide".$count."_id" // It is the ID of the active tab.
        );    
    // Define tabs options for version of Joomla! 3.0
        $tabsOptions = array(
            "active" => "tab".$count."_id" // It is the ID of the active tab.
        );      
    }    
    $count++;	   
    }
    
    if( $this->config['show_result_tabs'] == "show_tabs" ) 
    {
    $count = 0;    
    ?>
        <!-- This is a list with tabs names. -->
    	<ul class="nav nav-tabs" id="ID-Tabs-Group">
        <?PHP
        foreach ($output as $key => $templ)
        {
        $active = '';    
        if ( $count == 0 )
        {
            $active = 'active';
        }    
        ?>
        <li class="<?php echo $active; ?>">
        <a data-toggle="tab" href="#tab<?php echo $count; ?>_id"><?php echo JText::_($key); ?>
        </a>
       	</li>
        <?PHP
        $count++;
        }
        ?>
        </ul>
            
    <?PHP    
    echo JHtml::_('bootstrap.startPane', 'ID-Tabs-Group', $tabsOptions);
    $count = 0;  
    foreach ($output as $key => $templ)
    {
    echo JHtml::_('bootstrap.addPanel', 'ID-Tabs-Group', 'tab'.$count.'_id');
    echo $this->loadTemplate($templ);
    echo JHtml::_('bootstrap.endPanel'); 
    $count++;
    }
    echo JHtml::_('bootstrap.endPane', 'ID-Tabs-Group');    
    }
    else if($this->config['show_result_tabs'] == "show_slider" ) 
    {
    // This renders the beginning of the slides code.
    echo JHtml::_('bootstrap.startAccordion', 'slide-group-id', $slidesOptions);  
    $count = 0;  
    foreach ($output as $key => $templ)
    {
        // Open the first slide
        echo JHtml::_('bootstrap.addSlide', 'slide-group-id', JText::_($key), 'slide'.$count.'_id');
        echo $this->loadTemplate($templ);
        // This is the closing tag of the first slide
        echo JHtml::_('bootstrap.endSlide');  
        $count++;
    } 
    // This renders the end part of the slides code.	
    echo JHtml::_('bootstrap.endAccordion');

    }
    else 
    {

	foreach ($output as $templ)
	{
	echo $this->loadTemplate($templ);
	}
	
    }
        
            }
            else
            {
  // diddipoeler
  // anzeige als tabs oder slider von joomlaworks
  
  //echo 'output<pre>',print_r($output,true),'</pre>';
  
  $startoutput = '';
    $params = '';
    if($this->config['show_result_tabs'] == "show_tabs") 
    {
    $startoutput = '{tab=';
    $endoutput = '{/tabs}';
        
    foreach ( $output as $key => $templ ) 
    {
    $params .= $startoutput.JText::_($key).'}';
    $params .= $this->loadTemplate($templ);    
    }    
    $params .= $endoutput;   
       
    }    
    else if($this->config['show_result_tabs'] == "show_slider") 
    {
    $startoutput = '{slider=';
    $endoutput = '{/slider}';
    foreach ( $output as $key => $templ ) 
    {
    $params .= $startoutput.JText::_($key).'}';
    $params .= $this->loadTemplate($templ);    
    $params .= $endoutput;
    }    
        
    }
    else 
    {

	foreach ($output as $templ)
	{
	echo $this->loadTemplate($templ);
	}
	
    }    

    echo JHtml::_('content.prepare', $params); 
    }
  //}
  
	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
