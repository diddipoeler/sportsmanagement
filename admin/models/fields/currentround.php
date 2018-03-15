<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      currentround.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fields
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');


/**
 * JFormFieldCurrentround
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldCurrentround extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	public $type = 'Currentround';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
		// JInput object 
         $jinput = $app->input; 

        // Initialize variables.
		$options = array();

		$varname = (string) $this->element['varname'];
        //$project_id = $app->getUserState( "$option.pid", '0' );
         $project_id = $jinput->get->get('id');
		/*
        $project_id = JFactory::getApplication()->input->getVar($varname);
		if (is_array($project_id)) {
			$project_id = $project_id[0];
		}
		*/
		if ($project_id)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('id AS value');
			$query->select('CASE LENGTH(name) when 0 then CONCAT('.$db->Quote(JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME')). ', " ", id)	else name END as text ');
			$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round ');
			$query->where('project_id = '.$project_id);
			$query->order('roundcode');
			$db->setQuery($query);
			$options = $db->loadObjectList();
		}
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
