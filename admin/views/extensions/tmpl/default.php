<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/
 
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<table width="100%" border="0">
	<tr>
		<td width="100%" valign="top">
			<div id="cpanel">
				<?php 
                
                foreach ( $this->sporttypes as $key => $value )
                {
                    switch ($value)
                    {
                        case 'soccer':
                        echo $this->addIcon('dfbnetimport.png','index.php?option=com_sportsmanagement&view=jlextdfbnetplayerimport', JText::_('COM_SPORTSMANAGEMENT_EXT_DFBNETIMPORT'));
                        echo $this->addIcon('dfbschluessel.png','index.php?option=com_sportsmanagement&view=jlextdfbkeyimport', JText::_('COM_SPORTSMANAGEMENT_EXT_DFBKEY'));
                        echo $this->addIcon('lmoimport.png','index.php?option=com_sportsmanagement&view=jlextlmoimports', JText::_('COM_SPORTSMANAGEMENT_EXT_LMO_IMPORT'));
                        echo $this->addIcon('profleagueimport.png','index.php?option=com_sportsmanagement&view=jlextprofleagimport', JText::_('COM_SPORTSMANAGEMENT_EXT_PROF_LEAGUE_IMPORT'));
                        break;
                        case 'basketball':
                        echo $this->addIcon('dbbimport.png','index.php?option=com_sportsmanagement&view=jlextdbbimport', JText::_('COM_SPORTSMANAGEMENT_EXT_DBB_IMPORT'));
                        break;
                        case 'handball':
                        echo $this->addIcon('sisimport.png','index.php?option=com_sportsmanagement&view=jlextsisimport', JText::_('COM_SPORTSMANAGEMENT_EXT_SIS_IMPORT'));
                        break;
                        default:
                        break;
                    }
                }
                ?>

			</div>
		</td>
	</tr>
</table>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   