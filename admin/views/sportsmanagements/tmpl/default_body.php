<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_body.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage sportsmanagements
 */


defined('_JEXEC') or die('Restricted Access');
use Joomla\CMS\HTML\HTMLHelper;
?>
<?php foreach($this->items as $i => $item): ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td>
    <?php echo $item->id; ?>
        </td>
        <td>
    <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
        </td>
        <td>
    <?php echo $item->greeting; ?>
        </td>
    </tr>
<?php endforeach; ?>
