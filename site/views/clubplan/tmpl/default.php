<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

defined('_JEXEC') or die('Restricted access');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">
	<?php
	echo $this->loadTemplate('projectheading');

	if (($this->config['show_sectionheader'])==1 && $this->club)
	{
		echo $this->loadTemplate('sectionheader');
	}

	echo $this->loadTemplate('datenav');
	switch ($this->config['type_matches'])
	{
	case 0 : // All matches
		if (!empty($this->allmatches))
		{
			$tm = count ($this->allmatches);
			echo "<h4>".$tm." ".JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES')."</h4>";
			$this->matches = $this->allmatches;
			echo $this->loadTemplate('matches');//or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h4>".JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_MATCHES')."</h4><br/>";
		}
		break;
	case 1 : // Home matches
		if (!empty($this->homematches))
		{
			$tm = count ($this->homematches);
			echo "<h4>".$tm." ".JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES')."</h4>";
			$this->matches = $this->homematches;
			echo $this->loadTemplate('matches');//or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h4>".JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_HOME_MATCHES')."</h4><br/>";
		}
		break;
	case 2 : // Away matches
		if (!empty($this->awaymatches))
		{
			$tm = count ($this->awaymatches);
			echo "<h4>".$tm." ".JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES')."</h4>";
			$this->matches = $this->awaymatches;
			echo $this->loadTemplate('matches');//or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h4>".JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_AWAY_MATCHES')."</h4><br/>";
		}
		break;
	case 4 : // matches sorted by date
		if (!empty($this->allmatches))
		{
			$tm = count ($this->allmatches);
			echo "<h4>".$tm." ".JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES')."</h4>";
			$this->matches = $this->allmatches;
			echo $this->loadTemplate('matches_sorted_by_date');//or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h4>".JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_MATCHES')."</h4><br/>";
		}
		break;
	default : // Home+Away matches
		if (!empty($this->homematches))
		{
			$tm = count ($this->homematches);
			if(count($this->awaymatches)==0) {
				echo "<h4>".$tm." ".JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES')."</h4>";
			} else {
				echo "<h4>".$tm." ".JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_HOME_MATCHES')."</h4>";
			}
			$this->matches = $this->homematches;
			echo $this->loadTemplate('matches');//or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h4>".JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_HOME_MATCHES')."</h4><br/>";
		}
		if (!empty($this->awaymatches))
		{
			$tm = count ($this->awaymatches);
			if(count($this->homematches)==0) {
				echo "<h4>".$tm." ".JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES')."</h4>";
			} else {
				echo "<h4>".$tm." ".JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_AWAY_MATCHES')."</h4>";
			}
			$this->matches = $this->awaymatches;
			echo $this->loadTemplate('matches');//or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h4>".JText::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_AWAY_MATCHES')."</h4><br/>";
		}
		break;
	}
?>
<div>
<?PHP    
echo $this->loadTemplate('backbutton');
echo $this->loadTemplate('footer');
?>
</div>
<?PHP
	?>
</div>
