<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       copy.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage projectteams
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm">
  
    <fieldset>
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_COPY_DEST')?></legend>
    <table class="admintable">
        <tr>
            <td class="key">
                <label for="dest"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SELECT_PROJECT').':'; ?></label>
            </td>
            <td>
                <?php echo $this->lists['projects']; ?>
            </td>
        </tr>
    </table>
    </fieldset>
  
    <?php foreach ($this->ptids as $ptid): ?>
    <input type="hidden" name="ptids[]" value="<?php echo $ptid; ?>"/>
    <?php endforeach; ?>
    <input type="hidden" name="task" value="" />
</form>
