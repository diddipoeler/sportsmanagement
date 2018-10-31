<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      projectpositionreferee.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fields
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');


/**
 * FormFieldprojectpositionreferee
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldprojectpositionreferee extends \JFormFieldList
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
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
        // Initialize variables.
		$options = array();

		$varname = (string) $this->element['varname'];
        $project_id = $app->getUserState( "$option.pid", '0' );;
        /*
		$project_id = Factory::getApplication()->input->getVar($varname);
		if (is_array($project_id)) {
			$project_id = $project_id[0];
		}
		*/
		if ($project_id)
		{		
			$db = Factory::getDbo();
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
