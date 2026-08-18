<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       favteam.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\Database\DatabaseInterface;

/**
 * FormFieldFavteam
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldFavteam extends ListField
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'Favteam';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
		$app    = Factory::getApplication();
		$input  = $app->getInput();
		$option = $input->getCmd('option');
		$layout = $input->getCmd('layout');
		$id     = $input->getInt('id', 0);
		$options = array();

		$projectId = $layout === 'edit' ? $id : (int) $app->getUserState($option . '.pid', 0);

		if ($projectId)
		{
			$db    = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->createQuery();

			$query->select('t.id AS value, t.name AS text');
			$query->from('#__sportsmanagement_team AS t');
			$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = t.id');
			$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
			$query->where('pt.project_id = ' . (int) $projectId);
			$query->order('t.name');
			$db->setQuery($query);
			$options = $db->loadObjectList();
		}

		return array_merge(parent::getOptions(), $options);
	}
}
