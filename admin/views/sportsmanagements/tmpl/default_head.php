<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_head.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage sportsmanagements
 */


defined('_JEXEC') or die('Restricted Access');
use Joomla\CMS\Language\Text;
?>
<tr>
    <th width="5">
    <?php echo Text::_('COM_HELLOWORLD_HELLOWORLD_HEADING_ID'); ?>
    </th>
    <th width="20">
        <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
    </th>          
    <th>
    <?php echo Text::_('COM_HELLOWORLD_HELLOWORLD_HEADING_GREETING'); ?>
    </th>
</tr>
