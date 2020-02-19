<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_commentary.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage nextmatch
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

?>
<!-- START of match commentary -->
<div class="<?php echo $this->divclassrow;?> table-responsive" id="nextmatch">
<?php

if (!empty($this->matchcommentary)) {
    ?>
    <table class="table">
        <tr>
            <td class="contentheading">
                <?php
                echo '&nbsp;' . Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_COMMENTARY');
                ?>
            </td>
        </tr>
    </table>

    <table class="table">
        <?php
        foreach ($this->matchcommentary as $commentary) {
            ?>

            <tr>
                <td class="list">
                    <dl>
                        <?php echo $commentary->event_time; ?>
                    </dl>
                </td>
                <td class="list">
                    <dl>
                        <?php echo $commentary->notes; ?>
                    </dl>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
    <?PHP
}

?>
</div>
