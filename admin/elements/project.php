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

class JFormFieldProject extends JFormField
{

	protected $type = 'project';

	protected function getInput() {
		$db			= JFactory::getDBO();
		$lang		= JFactory::getLanguage();
		$extension	= "com_joomleague";
		$source 	= JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		
		$query = 'SELECT p.id, concat(p.name, \' ('.JText::_('COM_JOOMLEAGUE_GLOBAL_LEAGUE').': \', l.name, \')\', \' ('.JText::_('COM_JOOMLEAGUE_GLOBAL_SEASON').': \', s.name, \' )\' ) as name 
					FROM #__joomleague_project AS p 
					LEFT JOIN #__joomleague_season AS s ON s.id = p.season_id 
					LEFT JOIN #__joomleague_league AS l ON l.id = p.league_id 
					WHERE p.published=1 ORDER BY p.ordering DESC';
		$db->setQuery( $query );
		$projects = $db->loadObjectList();
		$mitems = array(JHTML::_('select.option', '', JText::_('COM_JOOMLEAGUE_GLOBAL_SELECT')));

		foreach ( $projects as $project ) {
			$mitems[] = JHTML::_('select.option',  $project->id, JText::_($project->name));
		}
		return  JHTML::_('select.genericlist',  $mitems, $this->name, 'class="inputbox" style="width:50%;" size="1"', 'value', 'text', $this->value, $this->id);
	}
}
