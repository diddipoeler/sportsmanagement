<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictions
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?>

<?php if (!empty($this->sidebar)) : ?>
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
                <?php echo $this->addIcon('tippspiel.png', 'index.php?option=com_sportsmanagement&view=predictiongames', Text::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_GAMES'));?>
                <?php echo $this->addIcon('tippspielgruppen.png', 'index.php?option=com_sportsmanagement&view=predictiongroups', Text::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_GROUPS'));?>
                <?php echo $this->addIcon('tippspielmitglieder.png', 'index.php?option=com_sportsmanagement&view=predictionmembers', Text::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_MEMBERS'));?>
                <?php echo $this->addIcon('tippspieltemplates.png', 'index.php?option=com_sportsmanagement&view=predictiontemplates', Text::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_TEMPLATES'));?>

            </div>
        </td>
      
    </tr>
  
</table>
