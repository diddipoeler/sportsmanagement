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

class JFormFieldEvents extends JFormField
{

	protected $type = 'events';

	function getInput() {
		$db = &JFactory::getDBO();
		$lang = JFactory::getLanguage();
		$extension = "com_joomleague";
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		
		$query = 'SELECT e.id, e.name FROM #__joomleague_eventtype e WHERE published=1 ORDER BY name';
		$db->setQuery( $query );
		$events = $db->loadObjectList();
		$mitems = array(JHTML::_('select.option', '', JText::_('COM_JOOMLEAGUE_GLOBAL_SELECT')));

		foreach ( $events as $event ) {
			$mitems[] = JHTML::_('select.option',  $event->id, '&nbsp;'.JText::_($event->name). ' ('.$event->id.')' );
		}
		
		$output= JHTML::_('select.genericlist',  $mitems, $this->name, 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $this->value, $this->id );
		return $output;
	}
}
 