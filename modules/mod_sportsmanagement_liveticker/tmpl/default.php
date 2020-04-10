<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version   1.0.05
 * @file      agegroup.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SportsManagement is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SportsManagement is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Diese Datei ist Teil von SportsManagement.
 *
 * SportsManagement ist Freie Software: Sie können es unter den Bedingungen
 * der GNU General Public License, wie von der Free Software Foundation,
 * Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
 * veröffentlichten Version, weiterverbreiten und/oder modifizieren.
 *
 * SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
 * OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
 * Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
 * Siehe die GNU General Public License für weitere Details.
 *
 * Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 * Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
 *
 * Note : All ini files need to be saved as UTF-8 without BOM
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

?>

<div id="turtushout-warning"><?php echo Text::_('!Warning! JavaScript must be enabled for proper operation.'); ?></div>
<?php

$display_add_box = 0;

if ($display_add_box)
{
	?>
    <form name='turtushout-form' id='turtushout-form' style='display:none;'>
		<?php
		if ($userId)
		{
			if ($display_welcome)
			{
				?>
                Hi, you logged in as <?php echo $name; ?><br/>
				<?php
			}
		}
		else
		{
			if ($display_username)
			{
				?>
                <label><?php echo Text::_('Name') ?></label>
                <input class="inputbox" type="text" name="created_by_alias" size="<?php echo $size; ?>"><br/>
				<?php
			}
		}
		?>

		<?php if ($display_title)
		{
			?>
            <label><?php echo Text::_('Title') ?></label>
            <input class="inputbox" type="text" name="title" size="<?php echo $size; ?>"><br/>
		<?php } ?>
        <label><?php echo Text::_('Text') ?></label>
        <textarea class="inputbox" name="text" rows="<?php echo $rows; ?>" cols="<?php echo $cols; ?>"></textarea>
        <input type="submit" name="Submit" class="button" value="<?php echo Text::_('Submit') ?>"/>

    </form>

	<?php
}
?>
<div id="turtushout-status" style='display:none;'></div>

<div id="turtushout-shout">
	<?php echo $list_html; ?>
</div>

<script>
    var turtushout_update_timeout = <?php echo $update_timeout * 1000; ?>;
    var turtushout_server_url = '<?php echo Uri::root(); ?>';
</script>
