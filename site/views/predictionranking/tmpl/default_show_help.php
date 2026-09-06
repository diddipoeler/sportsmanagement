<?php
/**
 * Joomla 5/6 prediction ranking compatibility layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

?><p style='font-weight: bold; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_NOTICE'); ?></p><p>
<ul>
    <li><i><?php
			echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_NOTICE_INFO_01');

			if (!$this->config['show_all_user'])
			{
			?></i></li>
    <li><i><?php
			echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_NOTICE_INFO_02');
			}
			?></i></li>
</ul></p>
