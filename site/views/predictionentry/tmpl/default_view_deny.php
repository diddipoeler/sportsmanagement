<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_view_deny.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage predictionentry
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

?><p><?php
echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_DENY_INFO_01');
?></p><p><?php
echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_DENY_INFO_02', '<a href="index.php?option=com_users&view=login"><b><i>', '</i></b></a>');
?></p><p><?php
echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_DENY_INFO_03', '<a href="index.php?option=com_users&view=registration"><b><i>', '</i></b></a>');
?></p><br />
