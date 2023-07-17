<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_liveticker
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * https://www.tutorialrepublic.com/codelab.php?topic=bootstrap&file=accordion
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
