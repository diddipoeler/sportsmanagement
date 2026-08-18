<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       currentround.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * FormFieldCurrentround
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldCurrentround extends ListField
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'Currentround';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
		$projectId = Factory::getApplication()->getInput()->getInt('id', 0);
		$options = array();

		if ($projectId)
		{
			$db    = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->createQuery();

			$query->select('id AS value');
			$query->select('CASE LENGTH(name) WHEN 0 THEN CONCAT(' . $db->quote(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME')) . ', " ", id) ELSE CONCAT(name, \' (\', round_date_first, \')\') END AS text');
			$query->from('#__sportsmanagement_round');
			$query->where('project_id = ' . (int) $projectId);
			$query->order('roundcode, round_date_first');
			$db->setQuery($query);
			$options = $db->loadObjectList();
		}

		return array_merge(parent::getOptions(), $options);
	}
}
