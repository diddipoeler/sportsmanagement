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

<div class="row">

<?php

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo $this->loadTemplate('debug');
}
	echo $this->loadTemplate('projectheading');

	if ( $this->config['show_sectionheader'] )
	{
		echo $this->loadTemplate('sectionheader');
	}

	if ( $this->config['show_result'] )
	{
		echo $this->loadTemplate('result');
	}
    
    if ( !empty( $this->matchevents ) )
	{
    if ( $this->config['show_timeline_under_results'] )
	{
		echo $this->loadTemplate('timeline');
	}
    }
    
  // ################################################################
  // diddipoeler
  // aufbau der templates
  $output = array();
  if ( $this->config['show_details'] )
	{
		$output['COM_SPORTSMANAGEMENT_MATCHREPORT_DETAILS'] = 'details';
	}
  if (($this->config['show_extended'])==1 && $this->extended )
	{
        $output['COM_SPORTSMANAGEMENT_TABS_EXTENDED'] = 'extended';
	}
	if ( $this->config['show_roster'] )
	{
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE_UP_PLAYER'] = 'roster';
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE_UP_STAFF'] = 'staff';
        $output['COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTES'] = 'subst';
	}
    if ( $this->config['show_roster_playground'] )
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

if($this->config['show_result_tabs'] == "show_slider") 
{
?>    
<div class="panel-group" id="accordion-matchreport">
<?PHP    
foreach ( $output as $key => $templ ) 
    {
?>    
<div class="panel panel-default">
<div class="panel-heading">
<h4 class="panel-title">
<a data-toggle="collapse" data-parent="#accordion-matchreport" href="#<?php echo $key; ?>"><?php echo JText::_($key); ?></a>
</h4>
</div>

<div id="<?php echo $key; ?>" class="panel-collapse collapse">
<div class="panel-body">
<?PHP    
echo $this->loadTemplate($templ); 
?>
</div>
</div>
</div>

<?PHP	    
    }         
        
    ?>
    </div>
    <?PHP       
}
    
if( $this->config['show_result_tabs'] == "show_tabs" ) 
{
?>    
    
<div role="tabpanel">

<!-- Tabs-Navs -->
<ul class="nav nav-tabs" role="tablist">
<?PHP
$count = 0;
$active = 'active';
foreach ($output as $key => $templ)
{
if ( $count )
{
$active = '';
}
?>  
<li role="presentation" class="<?PHP echo $active; ?>"><a href="#<?PHP echo $templ; ?>" role="tab" data-toggle="tab"><?PHP echo JText::_($key); ?></a></li>
<?PHP
$count++;
}
?>
</ul>
<!-- Tab-Inhalte -->
<div class="tab-content">
<?PHP
$count = 0;
$active = 'in active';
foreach ($output as $key => $templ)
{
if ( $count )
{
$active = '';
}
?>
<div role="tabpanel" class="tab-pane fade <?PHP echo $active; ?>" id="<?PHP echo $templ; ?>">
<?PHP   
echo $this->loadTemplate($templ);
?>
</div>
<?PHP
}
?>
</div>

</div>
    
<?PHP            
    
    }
        

  
	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
