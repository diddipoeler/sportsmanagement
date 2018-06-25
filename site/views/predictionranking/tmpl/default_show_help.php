<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_show_help.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionranking
 */

defined('_JEXEC') or die('Restricted access');
//echo '<br /><pre>~' . print_r($this->config,true) . '~</pre><br />';
?><p style='font-weight: bold; '><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_NOTICE'); ?></p><p><ul><li><i><?php
							echo JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_NOTICE_INFO_01');
							if (!$this->config['show_all_user'])
							{
								?></i></li><li><i><?php
								echo JText::_('COM_SPORTSMANAGEMENT_PRED_RANK_NOTICE_INFO_02');
							}
							?></i></li></ul></p>