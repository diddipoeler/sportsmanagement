<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_freeteams.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @subpackage results
 */

defined('_JEXEC') or die('Restricted access'); ?>

<?php if (!empty($this->rounds)) : ?>
<table class="table" >
    <tr>
        <td style="text-align:left; ">
    <?php echo sportsmanagementViewResults::showNotPlayingTeams($this->matches, $this->teams, $this->config, $this->favteams, $this->project); ?>
        </td>
    </tr>
</table>
<?php endif; ?>
