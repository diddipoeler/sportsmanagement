<?php
/**
 * @copyright	Copyright (C) 2007-2013 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined('_JEXEC') or die('Restricted access');

class JFormFieldTeamNameFormat extends JFormField
{
	protected $type = 'teamnameformat';

	function getInput() {
		$lang = JFactory::getLanguage();
		$extension = "com_joomleague";
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		$mitems = array();
		$mitems[] = JHTML::_('select.option', 0, JText::_('COM_JOOMLEAGUE_GLOBAL_TEAM_NAME_FORMAT_SHORT'));
		$mitems[] = JHTML::_('select.option', 1, JText::_('COM_JOOMLEAGUE_GLOBAL_TEAM_NAME_FORMAT_MEDIUM'));
		$mitems[] = JHTML::_('select.option', 2, JText::_('COM_JOOMLEAGUE_GLOBAL_TEAM_NAME_FORMAT_FULL'));

		$output= JHTML::_('select.genericlist',  $mitems,
				$this->name,
				'class="inputbox" size="1"',
				'value', 'text', $this->value, $this->id);
		return $output;
	}
}
