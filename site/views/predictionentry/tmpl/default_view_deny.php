<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_view_deny.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionentry
 */

defined('_JEXEC') or die('Restricted access');


?><p><?php
echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_DENY_INFO_01');
?></p><p><?php
echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_DENY_INFO_02','<a href="index.php?option=com_users&view=login"><b><i>','</i></b></a>');
?></p><p><?php
echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_DENY_INFO_03','<a href="index.php?option=com_users&view=registration"><b><i>','</i></b></a>');
?></p><br />
