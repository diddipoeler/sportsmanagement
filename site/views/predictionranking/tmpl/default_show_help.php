<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionranking
 * @file       default_show_help.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
