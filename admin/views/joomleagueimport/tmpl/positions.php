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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// No direct access
defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">


<table class="<?php echo $this->table_data_class; ?>">
			<thead>
            <tr>
            <?php echo $this->lists['whichtable']; ?>
            </tr>
				<tr>
					<th width="5"><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
					<th width="20"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" /></th>

                    <th width="" style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_JOOMLEAGUE_POSITIONS'); ?></th>
                    <th width="" style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_SPORTSMANAGEMENT_POSITIONS'); ?></th>
                    </tr>
            </thead>        
            <tbody>
				<?php
				$k=0;
				for ($i=0,$n=count($this->joomleague); $i < $n; $i++)
				{
					$row =& $this->joomleague[$i];
					$checked = JHtml::_('grid.checkedout',$row,$i);
                    ?>
					<tr class="<?php echo "row$k"; ?>">
						<td class="center"><?php echo $i; ?></td>
						<td class="center"><?php echo $checked; ?></td>
						
						<td><?php echo $row->name; ?>
                        
                        </td>
						<td class="center">
                        <?php 
                        $append =' onchange="document.getElementById(\'cb'.$i.'\').checked=true" ';
                        echo JHtml::_(	'select.genericlist',$this->lists['position'],'position'.$row->id,
												'class="inputbox" size="1"'.$append,'value','text',0); 
                        ?>
                        </td>
						
						
					</tr>
					<?php
					$k=1 - $k;
				}
				?>
			</tbody>
		</table>        
                    	
<?PHP



?>
	<input type="hidden" name="search_mode" value="<?php echo $this->lists['search_mode']; ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
	<?php echo JHtml::_('form.token')."\n"; ?>

</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>    