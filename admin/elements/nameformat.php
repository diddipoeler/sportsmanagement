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

class JFormFieldNameFormat extends JFormField
{
	protected $type = 'nameformat';

	function getInput() {
		$lang = JFactory::getLanguage();
		$extension = "com_joomleague";
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		
		$mitems = array();
		$mitems[] = JHTML::_('select.option', 0, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_FIRST_NICK_LAST'));
		$mitems[] = JHTML::_('select.option', 1, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_LAST_NICK_FIRST'));
		$mitems[] = JHTML::_('select.option', 2, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_LAST_FIRST_NICK'));
		$mitems[] = JHTML::_('select.option', 3, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_FIRST_LAST'));
		$mitems[] = JHTML::_('select.option', 4, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_LAST_FIRST'));
		$mitems[] = JHTML::_('select.option', 5, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_NICK_FIRST_LAST'));
		$mitems[] = JHTML::_('select.option', 6, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_NICK_LAST_FIRST'));
		$mitems[] = JHTML::_('select.option', 7, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_FIRST_LAST_NICK'));
		$mitems[] = JHTML::_('select.option', 8, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_FIRST_LAST2'));
		$mitems[] = JHTML::_('select.option', 9, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_LAST_FIRST2'));
		$mitems[] = JHTML::_('select.option',10, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_LAST'));
		$mitems[] = JHTML::_('select.option',11, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_FIRST_NICK_LAST2'));
		$mitems[] = JHTML::_('select.option',12, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_NICK'));
		$mitems[] = JHTML::_('select.option',13, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_FIRST_LAST3'));
		$mitems[] = JHTML::_('select.option',14, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_LAST2_FIRST'));
		$mitems[] = JHTML::_('select.option',15, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_LAST_NEWLINE_FIRST'));
		$mitems[] = JHTML::_('select.option',16, JText::_('COM_JOOMLEAGUE_GLOBAL_NAME_FORMAT_FIRST_NEWLINE_LAST'));
		
		$output= JHTML::_('select.genericlist',  $mitems,
							$this->name,
							'class="inputbox" size="1"', 
							'value', 'text', $this->value, $this->id);
		return $output;
	}
}
