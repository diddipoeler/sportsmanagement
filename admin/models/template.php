<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       template.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;

/**
 * sportsmanagementModeltemplate
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModeltemplate extends JSMModelAdmin
{

	/**
	 * sportsmanagementModeltemplate::getAllTemplatesList()
	 *
	 * @param   mixed $project_id
	 * @param   mixed $master_id
	 * @return
	 */
	function getAllTemplatesList($project_id,$master_id)
	{

			  $app = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		// Create a new query object.
		$db    = $this->getDbo();
		$query1    = $db->getQuery(true);
		$query2    = $db->getQuery(true);
		$query3    = $db->getQuery(true);
		$result1 = array();
		$result2 = array();

					  // Select some fields
		$query1->select('template');

		// From table
		$query1->from('#__sportsmanagement_template_config');
		$query1->where('project_id = ' . $project_id);
		$db->setQuery($query1);

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			// Joomla! 3.0 code here
			$current = $db->loadColumn();
		}
		elseif (version_compare(JVERSION, '2.5.0', 'ge'))
		{
				// Joomla! 2.5 code here
				  $current = $db->loadResultArray();
		}

		if ($current)
		{
			$current = implode("','", $current);
		}

			  // Select some fields
		$query2->select('id as value, title as text');

		// From table
		$query2->from('#__sportsmanagement_template_config');
		$query2->where('project_id = ' . $master_id);

		if ($current)
		{
			$query2->where('template NOT IN (\'' . $current . '\') ');
		}

		$db->setQuery($query2);
		$result1 = $db->loadObjectList();

		foreach ($result1 as $template)
		{
			$template->text = Text::_($template->text);
		}

			  // Select some fields
			$query3->select('id as value, title as text');

			// From table
			$query3->from('#__sportsmanagement_template_config');
			$query3->where('project_id = ' . $project_id);
			$query3->order('title');
			$db->setQuery($query3);
			$result2 = $db->loadObjectList();

		foreach ($result2 as $template)
		{
			$template->text = Text::_($template->text);
		}

		if ($result1)
		{
			return array_merge($result2, $result1);
		}
		else
		{
			return ($result2);
		}

	}


	/**
	 * sportsmanagementModeltemplate::import()
	 *
	 * @param   mixed $templateid
	 * @param   mixed $projectid
	 * @return
	 */
	function import($templateid,$projectid)
	{
		$row =& $this->getTable();

		// Load record to copy
		if (!$row->load($templateid))
		{
			 $this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Copy to new element
		$row->id = null;
		$row->project_id = (int) $projectid;

		// Make sure the item is valid
		if (!$row->check())
		{
			 $this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Store the item to the database
		if (!$row->store())
		{
			 $this->setError($this->_db->getErrorMsg());

			return false;
		}

		return true;
	}


	/**
	 * sportsmanagementModeltemplate::delete()
	 *
	 * @param   mixed $pks
	 * @return
	 */
	public function delete(&$pks)
	{
		$app = Factory::getApplication();

		  return parent::delete($pks);

	}

	/**
	 * Method to update one or more records.
	 *
	 * @param   array &$pks An array of record primary keys.
	 *
	 * @return
	 *
	 * @since
	 */
	public function update(&$pks)
	{
		$dispatcher = \JEventDispatcher::getInstance();
		$pks = (array) $pks;

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			$table_row = $this->getTable();

			if ($table_row->load($pk))
			{
				// Make sure the item is valid
				if (!$table_row->check())
				{
					$this->setError($this->_db->getErrorMsg());

					return false;
				}

				$templatepath = JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'settings';
				$xmlfile = $templatepath . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . $table_row->template . '.xml';

				// Create form containing ALL keys and their default values
				$form = Form::getInstance($table_row->template, $xmlfile, array('control' => 'params'));

				// Set current settings
				$form->bind(json_decode($table_row->params, true));

				// Get all fields and build key => value pairs ...
				$fieldsets = $form->getFieldset();

				$params = array();

				foreach ($fieldsets as $f)
				{
					switch ($f->type)
					{
						case  "Spacer":
						case  "JSMMessage":
							// Spacers and Message fields are no changeable parameters
						break;
						default:
							$params[$f->fieldname] = $f->value;
						break;
					}
				}

				// .. to generate new JSON setting
				$table_row->params = json_encode($params);

				// Store the item to the database
				if (!$table_row->store())
				{
					$this->setError($this->_db->getErrorMsg());

					return false;
				}

				$form->reset();
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
}
