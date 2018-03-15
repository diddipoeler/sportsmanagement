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
				<?php echo $this->addIcon('tippspiel.png','index.php?option=com_sportsmanagement&view=predictiongames', JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_GAMES'));?>
                <?php echo $this->addIcon('tippspielgruppen.png','index.php?option=com_sportsmanagement&view=predictiongroups', JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_GROUPS'));?>
                <?php echo $this->addIcon('tippspielmitglieder.png','index.php?option=com_sportsmanagement&view=predictionmembers', JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_MEMBERS'));?>
                <?php echo $this->addIcon('tippspieltemplates.png','index.php?option=com_sportsmanagement&view=predictiontemplates', JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_TEMPLATES'));?>

			</div>
		</td>
		
	</tr>
	
</table>
