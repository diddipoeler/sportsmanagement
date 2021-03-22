<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       projectdivisionlist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\Folder;

FormHelper::loadFieldClass('list');

/**
 * JFormFieldprojectdivisionlist
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class JFormFieldprojectdivisionlist extends \JFormFieldList
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'projectdivisionlist';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
		$this->jsmapp = Factory::getApplication();
		$this->jsmjinput = $this->jsmapp->input;
		$this->jsmoption = $this->jsmjinput->getCmd('option');

//		$team_id         = $this->jsmapp->getUserState("$this->jsmoption.team_id", '0');
//		$persontype      = $this->jsmapp->getUserState("$this->jsmoption.persontype", '0');
//		$project_team_id = $this->jsmapp->getUserState("$this->jsmoption.project_team_id", '0');
		$pid             = $this->jsmapp->getUserState("$this->jsmoption.pid", '0');

		$options   = array();
		$select_id = Factory::getApplication()->input->getVar('id');
		$db        = Factory::getDbo();
		$query     = $db->getQuery(true);
		$query->select('pos.id AS value, pos.name AS text');
		$query->from('#__sportsmanagement_division as pos');
//		$query->join('INNER', '#__sportsmanagement_project_position AS pp ON pp.position_id = pos.id');
//		$query->join('INNER', '#__sportsmanagement_sports_type AS s ON s.id = pos.sports_type_id');
//		$query->join('INNER', '#__sportsmanagement_person_project_position AS ppp ON pp.project_id = ppp.project_id');
		$query->where('pos.project_id = ' . $pid);
		$query->order('pos.ordering,pos.name');
		$query->group('pos.id');
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($db->getErrorMsg());
		}

		foreach ($options as $row)
		{
			$row->text = Text::_($row->text);
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
