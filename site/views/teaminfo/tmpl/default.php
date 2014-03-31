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



if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo 'this->teaminfo config<br /><pre>~' . print_r($this->config,true) . '~</pre><br />';
echo 'this->teaminfo team<br /><pre>~' . print_r($this->team,true) . '~</pre><br />';
echo 'this->teaminfo merge_clubs<br /><pre>~' . print_r($this->merge_clubs,true) . '~</pre><br />';
echo 'this->teaminfo seasons<br /><pre>~' . print_r($this->seasons,true) . '~</pre><br />';
}


// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<div class="joomleague">
	<?php
	if ($this->config['show_projectheader']==1)
	{	
		echo $this->loadTemplate('projectheading');
	}
		
	if ($this->config['show_sectionheader']==1)
	{
		echo $this->loadTemplate('sectionheader');
	}
		
// ################################################################
  // diddipoeler
  // aufbau der templates
  $output = array();
  /*
  if ($this->config['show_teaminfo']==1)
	{
        $output['COM_SPORTSMANAGEMENT_TEAMINFO_PAGE_TITLE'] = 'teaminfo';
	}
  */
	if ($this->config['show_description']==1)
	{
        $output['COM_SPORTSMANAGEMENT_TEAMINFO_TEAMINFORMATION'] = 'description';
	}
	
    if ($this->config['show_extra_fields']==1)
	{
        $output['COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS'] = 'extra_fields';
	}
    //fix me css	
	if ($this->config['show_extended']==1)
	{
        $output['COM_SPORTSMANAGEMENT_TABS_EXTENDED'] = 'extended';
	}	
		
	if ($this->config['show_history']==1)
	{
        $output['COM_SPORTSMANAGEMENT_TEAMINFO_HISTORY'] = 'history';
	}
    
    if ($this->config['show_history_leagues']==1)
	{
        $output['COM_SPORTSMANAGEMENT_TEAMINFO_HISTORY_PER_LEAGUE_SUMMARY'] = 'history_leagues';
	}
    
    if ($this->config['show_training']==1)
	{
        $output['COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING'] = 'training';
	}
    
  
  // ################################################################
  
    if ( $this->use_joomlaworks == 0 || $this->config['show_teaminfo_tabs'] == 'no_tabs' )
    {
        
    if ($this->config['show_teaminfo']==1)
	{
		echo $this->loadTemplate('teaminfo');
	}

	if ($this->config['show_description']==1)
	{
		echo $this->loadTemplate('description');
	}
	if ($this->config['show_extra_fields']==1)
	{
		echo $this->loadTemplate('extra_fields');
	}	
    //fix me css	
	if ($this->config['show_extended']==1)
	{
		echo $this->loadTemplate('extended');
	}
    if ($this->config['show_training']==1)
	{
		echo $this->loadTemplate('training');
	}	
		
	if ($this->config['show_history']==1)
	{
		echo $this->loadTemplate('history');
	}
    
    if ($this->config['show_history_leagues']==1)
	{
		echo $this->loadTemplate('history_leagues');
	}
    
    }
    else
    {
    // diddipoeler
    // anzeige als tabs oder slider von joomlaworks
    $startoutput = '';
    $params = '';
    echo $this->loadTemplate('teaminfo');
    
    if($this->config['show_teaminfo_tabs'] == "show_tabs") 
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
    else if($this->config['show_teaminfo_tabs'] == "show_slider") 
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

    echo JHtml::_('content.prepare', $params);     
        
    }
		
	
    
    echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
