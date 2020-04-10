<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlxmlimports
 * @file       update.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

?>
<div id='editcell'>
    <a name='page_top'></a>
    <table class='adminlist'>
        <thead>
        <tr>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TABLE_TITLE_4'); ?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php echo '&nbsp;'; ?></td>
        </tr>
        </tbody>
    </table>
	<?php


	if (is_array($this->importData))
	{
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
	}


	if (ComponentHelper::getParams($this->option)->get('show_debug_info_backend', 0))
	{
		?>
        <fieldset>
        <legend><?php echo Text::_('Post data from importform was:'); ?></legend>
        <table class='adminlist'>
            <tr>
                <td><?php echo TVarDumper::dump($this->xml, 10, true); ?></td>
            </tr>
        </table>
        </fieldset><?php
	}
	?>
</div>
