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
jimport('joomla.html.html.bootstrap');
?> 
<div class="row" >
    <div id="j-sidebar-container" class="col-md-2">
        <?php echo $this->sidebar; ?>
    </div>

    <div class="col-md-8">
        <div id="dashboard-icons" class="dashboard-icons">
            <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=predictiongames">
                <img src="components/com_sportsmanagement/assets/icons/tippspiel.png" alt="<?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_GAMES') ?>" /><br />
                <span><?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_GAMES') ?></span>
            </a>
            <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=predictiongroups">
                <img src="components/com_sportsmanagement/assets/icons/tippspielgruppen.png" alt="<?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_GROUPS') ?>" /><br />
                <span><?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_GROUPS') ?></span>
            </a>
            <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=predictionmembers">
                <img src="components/com_sportsmanagement/assets/icons/tippspielmitglieder.png" alt="<?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_MEMBERS') ?>" /><br />
                <span><?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_MEMBERS') ?></span>
            </a>
            <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=predictiontemplates">
                <img src="components/com_sportsmanagement/assets/icons/tippspieltemplates.png" alt="<?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_TEMPLATES') ?>" /><br />
                <span><?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_TEMPLATES') ?></span>
            </a>
        </div> 




