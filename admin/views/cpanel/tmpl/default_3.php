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
 
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

?> 
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

<table width="100%" border="0">
	<tr>
		<td width="100%" valign="top">
<div id="cpanel">          
                     
              <?php echo $this->addIcon('sportarten.png','index.php?option=com_sportsmanagement&view=sportstypes', JText::_('COM_SPORTSMANAGEMENT_D_MENU_SPORTSTYPES'));?>
                        
              <?php echo $this->addIcon('saisons.png','index.php?option=com_sportsmanagement&view=seasons', JText::_('COM_SPORTSMANAGEMENT_D_MENU_SEASONS'));?>            
                        
              <?php echo $this->addIcon('ligen.png','index.php?option=com_sportsmanagement&view=leagues', JText::_('COM_SPORTSMANAGEMENT_D_MENU_LEAGUES'));?>            
                        
              <?php echo $this->addIcon('federation.png','index.php?option=com_sportsmanagement&view=jlextfederations', JText::_('COM_SPORTSMANAGEMENT_D_MENU_FEDERATIONS'));?>            
                        
              <?php echo $this->addIcon('laender.png','index.php?option=com_sportsmanagement&view=jlextcountries', JText::_('COM_SPORTSMANAGEMENT_D_MENU_COUNTRIES'));?>            
                        
              <?php echo $this->addIcon('landesverbaende.png','index.php?option=com_sportsmanagement&view=jlextassociations', JText::_('COM_SPORTSMANAGEMENT_D_MENU_ASSOCIATIONS'));?>            
                        
              <?php echo $this->addIcon('positionen.png','index.php?option=com_sportsmanagement&view=positions', JText::_('COM_SPORTSMANAGEMENT_D_MENU_POSITIONS'));?>            
                        
              <?php echo $this->addIcon('ereignisse.png','index.php?option=com_sportsmanagement&view=eventtypes', JText::_('COM_SPORTSMANAGEMENT_D_MENU_EVENTS'));?>            
                        
              <?php echo $this->addIcon('altersklassen.png','index.php?option=com_sportsmanagement&view=agegroups', JText::_('COM_SPORTSMANAGEMENT_D_MENU_AGEGROUPS'));?>            
            </div>          

</td>
		
	</tr>
	
    
    
<tr>
		<td width="100%" valign="top">
<div id="cpanel">    
<?php echo $this->addIcon('vereine.png','index.php?option=com_sportsmanagement&view=clubs', JText::_('COM_SPORTSMANAGEMENT_D_MENU_CLUBS'));?>            
                          
              <?php echo $this->addIcon('mannschaften.png','index.php?option=com_sportsmanagement&view=teams', JText::_('COM_SPORTSMANAGEMENT_D_MENU_TEAMS'));?>            
                          
              <?php echo $this->addIcon('personen.png','index.php?option=com_sportsmanagement&view=persons', JText::_('COM_SPORTSMANAGEMENT_D_MENU_PERSONS'));?>            
                          
              <?php echo $this->addIcon('spielorte.png','index.php?option=com_sportsmanagement&view=playgrounds', JText::_('COM_SPORTSMANAGEMENT_D_MENU_VENUES'));?>            
                          
              <?php echo $this->addIcon('spielfeldpositionen.png','index.php?option=com_sportsmanagement&view=rosterpositions', JText::_('COM_SPORTSMANAGEMENT_D_MENU_ROSTER_POSITION'));?>    
</div>          

</td>
		
	</tr>    
</table>        