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
?>

<div class="">
	<?php
	echo $this->loadTemplate('projectheading');

	if ($this->match)
	{
		if (($this->config['show_sectionheader'])==1)
		{
			echo $this->loadTemplate('sectionheader');
		}
		
		if (($this->config['show_nextmatch'])==1)
		{
			echo $this->loadTemplate('nextmatch');
		}


$this->output = array();
if (($this->config['show_details'])==1)
		{
            $this->output['COM_SPORTSMANAGEMENT_NEXTMATCH_DETAILS'] = 'details';
		}

		if (($this->config['show_preview'])==1)
		{
            $this->output['COM_SPORTSMANAGEMENT_NEXTMATCH_PREVIEW'] = 'preview';
		}

		if (($this->config['show_stats'])==1)
		{
            $this->output['COM_SPORTSMANAGEMENT_NEXTMATCH_H2H'] = 'stats';
		}

		if (($this->config['show_history'])==1)
		{
            $this->output['COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY'] = 'history';
		}

if (($this->config['show_previousx'])==1)
		{
            $this->output['COM_SPORTSMANAGEMENT_NEXTMATCH_PREVIOUS'] = 'previousx';
		}
        
        if (($this->config['show_commentary'])==1 && $this->matchcommentary )
	{
        $this->output['COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_COMMENTARY'] = 'commentary';
	}



echo $this->loadTemplate($this->config['show_nextmatch_tabs']);
            




		echo "<div>";
			echo $this->loadTemplate('backbutton');
			echo $this->loadTemplate('footer');
		echo "</div>";
	}
	else
	{
		echo "<p>" . JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_NO_MORE_MATCHES') . "</p>";
	}
	?>
</div>
