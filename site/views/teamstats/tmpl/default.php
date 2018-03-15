<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

defined( '_JEXEC' ) or die( 'Restricted access' ); 

// Set display style dependent on what stat parts should be shown
$show_general = 'none';
if ( $this->config['show_general_stats'] == 1 )
{
	$show_general = 'inline';
}

$show_goals = 'none';
if ( $this->config['show_goals_stats'] == 1 )
{
	$show_goals = 'inline';
}

$show_attendance = 'none';
if ( $this->config['show_attendance_stats'] == 1 )
{
	$show_attendance = 'inline';
}

$show_flash = '';
if ( $this->config['show_goals_stats_flash'] == 0 )
{
	$show_flash = 'display:none;';
}

//$show_att_ranking	= '';
//if ( $this->config['show_attendance_ranking'] == 0 )
//{
//	$show_att_ranking = 'display:none;';
//}

//$show_events = '';
//if ( $this->config['show_events_stats'] == 0 )
//{
//	$show_events  = 'display:none;';
//}

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="">
	<?php 
	echo $this->loadTemplate('projectheading'); 

	if ($this->config['show_sectionheader'])
	{
		echo $this->loadTemplate('sectionheader'); 
	}

	if ($this->config['show_general_stats'])
	{
		echo $this->loadTemplate('stats'); 
	}

	if ($this->config['show_attendance_stats'])
	{
		echo $this->loadTemplate('attendance_stats'); 
	}	

	echo '<div style="width:100%; float:left">';
	if ( $this->config['show_goals_stats_flash'] )
	{
		echo $this->loadTemplate('flashchart'); 
	}
	echo '</div>';

	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
