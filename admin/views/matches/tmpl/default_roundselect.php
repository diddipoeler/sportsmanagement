<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matches
 * @file       default_roundselect.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<!-- round selector START -->
<div style="width: 75%; margin-bottom: 14px;">
    <form name="roundForm" id="roundForm" method="post">

        <input type="hidden" name="act" value="" id="short_act"/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name='boxchecked' value="0"/>
        <div style="float: right; vertical-align: middle; line-height: 27px;">
			<?php echo HTMLHelper::_('form.token') . "\n"; ?>

			<?php
			$lv = "";
			$nv = "";
			$sv = false;

			foreach ($this->ress as $v)
			{
				if ($v->id == $this->roundws->id)
				{
					break;
				}
				$lv = $v->id;
			}
			foreach ($this->ress as $v)
			{
				$nv = $v->id;
				if ($sv)
				{
					break;
				}
				if ($v->id == $this->roundws->id)
				{
					$sv = true;
				}
			}
			echo '<div style="float: left; text-align: center;">';
			if ($lv != "")
			{
				$query    = "option=com_sportsmanagement&view=matches&rid=" . $lv;
				$link     = Route::_('index.php?' . $query);
				$prevlink = HTMLHelper::link($link, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_PREV_MATCH'));
				echo $prevlink;
			}
			else
			{
				echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_PREV_MATCH');
			}
			echo '</div>';
			echo '<div style="float: left; text-align: center; margin-right: 10px; margin-left: 10px;">';
			echo $this->lists['project_rounds'];
			echo '</div>';
			echo '<div style="float: left; text-align: center;">';
			if (($nv != "") && ($nv != $this->roundws->id))
			{
				$query    = "option=com_sportsmanagement&view=matches&rid=" . $nv;
				$link     = Route::_('index.php?' . $query);
				$nextlink = HTMLHelper::link($link, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_NEXT_MATCH'));
				echo $nextlink;
			}
			else
			{
				echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_NEXT_MATCH');
			}
			echo '</div>';
			?>
        </div>
    </form>
</div>
<!-- round selector END -->
