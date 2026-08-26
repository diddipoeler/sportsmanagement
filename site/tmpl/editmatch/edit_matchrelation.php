<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       edit_matchrelation.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$oldMatchId = (int) ($this->match->old_match_id ?? 0);
$newMatchId = (int) ($this->match->new_match_id ?? 0);
?>
<fieldset class="adminform">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MREL_DETAILS'); ?></legend>
    <table class="admintable">
        <tbody>
        <tr>
            <td class="key" style="text-align:right;">
                <label><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MREL_OLD_ID'); ?></label>
            </td>
            <td style="text-align:left;">
                <?php echo $this->lists['old_match']; ?>
                <?php if ($oldMatchId > 0) : ?>
                    <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&tmpl=component&controller=match&task=edit&cid[]=' . $oldMatchId); ?>">
                        Match Link
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="key" style="text-align:right;">
                <label><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MREL_NEW_ID'); ?></label>
            </td>
            <td style="text-align:left;">
                <?php echo $this->lists['new_match']; ?>
                <?php if ($newMatchId > 0) : ?>
                    <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&tmpl=component&controller=match&task=edit&cid[]=' . $newMatchId); ?>">
                        Match Link
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        </tbody>
    </table>
</fieldset>
