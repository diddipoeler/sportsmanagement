<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       parentdivision.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\Database\DatabaseInterface;

/**
 * FormFieldparentdivision
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldparentdivision extends ListField
{
	public $type = 'parentdivision';

	/**
	 * Method to get the field options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$app       = Factory::getApplication();
		$option    = $app->getInput()->getCmd('option');
		$projectId = (int) $app->getUserState($option . '.pid', 0);
		$options   = array();

		if ($projectId)
		{
			$db    = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->createQuery();
			$query->select('dv.id AS value, dv.name AS text');
			$query->from('#__sportsmanagement_division AS dv');
			$query->where('dv.project_id = ' . $projectId);
			$query->where('dv.parent_id = 0');
			$query->order('dv.ordering ASC');
			$db->setQuery($query);
			$options = $db->loadObjectList();
		}

		return array_merge(parent::getOptions(), $options);
	}
}
