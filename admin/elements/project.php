<?php
/**
* @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
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
        // welche tabelle soll genutzt werden
        $params =& JComponentHelper::getParams( 'com_sportsmanagement' );
        $database_table	= $params->get( 'cfg_which_database_table' );
        
		$extension	= "com_sportsmanagement";
		$source 	= JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		
		$query = 'SELECT p.id, concat(p.name, \' ('.JText::_('COM_SPORTSMANAGEMENT_GLOBAL_LEAGUE').': \', l.name, \')\', \' ('.JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SEASON').': \', s.name, \' )\' ) as name 
					FROM #__'.$database_table.'_project AS p 
					LEFT JOIN #__'.$database_table.'_season AS s ON s.id = p.season_id 
					LEFT JOIN #__'.$database_table.'_league AS l ON l.id = p.league_id 
					WHERE p.published=1 ORDER BY p.ordering DESC';
		$db->setQuery( $query );
		$projects = $db->loadObjectList();
		$mitems = array(JHTML::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));

		foreach ( $projects as $project ) {
			$mitems[] = JHTML::_('select.option',  $project->id, JText::_($project->name));
		}
		return  JHTML::_('select.genericlist',  $mitems, $this->name, 'class="inputbox" style="width:50%;" size="1"', 'value', 'text', $this->value, $this->id);
	}
}
