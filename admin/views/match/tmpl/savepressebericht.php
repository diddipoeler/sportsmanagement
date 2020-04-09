<?PHP
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage match
 * @file       savepressebericht.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

foreach ($this->importData as $key => $value)
{
	?>
    <fieldset>
        <legend><?php echo Text::_($key); ?></legend>
        <table class='adminlist'>
            <tr>
                <td><?php echo $value; ?></td>
            </tr>
        </table>
    </fieldset>
	<?php
}




