<?php
/**
 * @copyright	Copyright (C) 2005-2013 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');

/**
 * Session form field class
 */
class JFormFieldprojectpositionreferee extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	public $type = 'projectpositionreferee';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        // Initialize variables.
		$options = array();

		$varname = (string) $this->element['varname'];
        $project_id = $mainframe->getUserState( "$option.pid", '0' );;
        /*
		$project_id = JRequest::getVar($varname);
		if (is_array($project_id)) {
			$project_id = $project_id[0];
		}
		*/
		if ($project_id)
		{		
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
		
                
			$query->select('ppos.id AS value,pos.name AS text');
			$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos');
			$query->join('inner', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON pos.id=ppos.position_id');
			$query->where('ppos.project_id='. $project_id.' AND pos.persontype=3');
			//$query->order('t.name');
			$db->setQuery($query);
			$options = $db->loadObjectList();
		}
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}
