<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_4.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage predictions
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
jimport('joomla.html.html.bootstrap');
?> 
<div class="row" >
    <div id="j-sidebar-container" class="col-md-2">
        <?php echo $this->sidebar; ?>
    </div>

    <div class="col-md-8">
        <div id="dashboard-icons" class="dashboard-icons">
            <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=predictiongames">
                <img src="components/com_sportsmanagement/assets/icons/tippspiel.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_GAMES') ?>" /><br />
                <span><?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_GAMES') ?></span>
            </a>
            <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=predictiongroups">
                <img src="components/com_sportsmanagement/assets/icons/tippspielgruppen.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_GROUPS') ?>" /><br />
                <span><?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_GROUPS') ?></span>
            </a>
            <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=predictionmembers">
                <img src="components/com_sportsmanagement/assets/icons/tippspielmitglieder.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_MEMBERS') ?>" /><br />
                <span><?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_MEMBERS') ?></span>
            </a>
            <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=predictiontemplates">
                <img src="components/com_sportsmanagement/assets/icons/tippspieltemplates.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_TEMPLATES') ?>" /><br />
                <span><?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_TEMPLATES') ?></span>
            </a>
        </div> 




