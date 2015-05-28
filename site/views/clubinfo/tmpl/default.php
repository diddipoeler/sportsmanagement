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

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$this->kmlpath = JURI::root().'tmp'.DS.$this->club->id.'-club.kml';
$this->kmlfile = $this->club->id.'-club.kml';

?>
<div class="row" id="clubinfo">

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

	echo $this->loadTemplate('clubinfo');
	
/**
 * diddipoeler
 * aufbau der templates als array
 */
  $this->output = array();
  
  if ( $this->config['show_extra_fields'] )
	{
	$this->output['COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS'] = 'extrafields';
	}
    
    if ( $this->config['show_extended'] )
	{
	$this->output['COM_SPORTSMANAGEMENT_TABS_EXTENDED'] = 'extended';
	}
    
    if ( $this->config['show_maps'] && (JPluginHelper::isEnabled('system', 'plugin_googlemap2') || JPluginHelper::isEnabled('system', 'plugin_googlemap3')) )
	{ 
        $this->output['COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'] = 'googlemap';
	}
    
    if ( $this->config['show_teams_of_club'] )
	{ 
        $this->output['COM_SPORTSMANAGEMENT_CLUBINFO_TEAMS'] = 'teams';
	}
    
    if ( $this->config['show_club_rssfeed'] )
	{
		if ( $this->rssfeeditems )
        {
        $this->output['COM_SPORTSMANAGEMENT_CLUBINFO_RSSFEED'] = 'rssfeed';  
        }
	}
  
/**
   * je nach einstellung der templates im backend, wird das template
   * aus dem verzeichnis globalviews geladen.
   * hat den vorteil, das weniger programmcode erzeugt wird.
   * no_tabs
   * show_tabs
   * show_slider
   */  
echo $this->loadTemplate($this->config['show_clubinfo_tabs']);

?>
<div id="backbuttonfooter">
<?PHP    
echo $this->loadTemplate('backbutton');
echo $this->loadTemplate('footer');
?>
</div>
<?PHP
	?>
<!-- ende clubinfo -->    
</div>
