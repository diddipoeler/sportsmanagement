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

defined('_JEXEC') or die('Restricted access');
?>
<form name="adminForm" id="adminForm" method="post">
	<?php $dateformat="%d-%m-%Y"; ?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
        <td><?php
                echo "".JHTML::_('select.genericlist', $this->lists['fromteamart'], 'teamartsel' , 'class="inputbox" size="1" onchange="hideclubplandate();" ', 'value', 'text', $this->teamartsel )."";
                echo "".JHTML::_('select.genericlist', $this->lists['fromteamprojects'], 'teamprojectssel' , 'class="inputbox" size="1" onchange="hideclubplandate();" ', 'value', 'text', $this->teamprojectssel )."";
                echo "".JHTML::_('select.genericlist', $this->lists['fromteamseasons'], 'teamseasonssel' , 'class="inputbox" size="1" onchange="hideclubplandate();" ', 'value', 'text', $this->teamseasonssel )."";                
				?>
            <td>
        </tr>
        <tr>
			<td><div class="clubplandate"><?php
				echo JHTML::calendar(sportsmanagementHelper::convertDate($this->startdate,1),'startdate','startdate',$dateformat);
				echo ' - '.JHTML::calendar(sportsmanagementHelper::convertDate($this->enddate,1),'enddate','enddate',$dateformat);
                ?>
                </div>
                <?PHP
                echo "".JHTML::_('select.genericlist', $this->lists['type'], 'type' , 'class="inputbox" size="1" onchange="" ', 'value', 'text', $this->type )."";
				?>
                <input type="submit" class="button" name="reload View" value="<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_FILTER'); ?>" />
                </td>
			
            
            <td>
                       
            
            <?php
            
            
            
			if ( $this->club )
			{
				$picture=$this->club->logo_big;

?>                                    
<a href="<?php echo JURI::root().$picture;?>" title="<?php echo $this->club->name;?>" class="modal">
<img src="<?php echo JURI::root().$picture;?>" alt="<?php echo $this->club->name;?>" width="50" />
</a>
<?PHP            
            }
			?></td>
		</tr>
        
	</table>
	<?php echo JHTML::_('form.token')."\n"; ?>
</form><br />