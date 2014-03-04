<?php 
/**
* @copyright	Copyright (C) 2007-2012 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
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