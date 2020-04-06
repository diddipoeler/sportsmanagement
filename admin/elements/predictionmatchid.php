<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       predictionmatchid.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage elements
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormField;

/**
 * JFormFieldpredictionmatchid
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldpredictionmatchid extends FormField
{
	var    $_name = 'predictionmatchid';

	/**
	 * JFormFieldpredictionmatchid::getInput()
	 *
	 * @return
	 */
	protected function getInput()
	{
		$db = sportsmanagementHelper::getDBConnection();
		  $app = Factory::getApplication();
		$option    = 'com_sportsmanagement';
		$prediction_id = $app->getUserState("$option.prediction_id", '0');

		// Welche tabelle soll genutzt werden
		$params = ComponentHelper::getParams('com_sportsmanagement');

		  $query = $db->getQuery(true);
		  $query->select('m.id AS id,m.match_date');
		  $query->select('r.roundcode,r.name as roundname');
		  $query->select('t1.name as home');
		  $query->select('t2.name as away');
		  $query->from('#__sportsmanagement_match AS m');
		  $query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id');
		  $query->join('INNER', '#__sportsmanagement_prediction_project as prepro on prepro.project_id = r.project_id');
		  $query->join('LEFT', '#__sportsmanagement_project_team AS tt1 ON m.projectteam1_id = tt1.id');
		  $query->join('LEFT', '#__sportsmanagement_project_team AS tt2 ON m.projectteam2_id = tt2.id');

		  $query->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.id = tt1.team_id ');
		  $query->join('LEFT', '#__sportsmanagement_season_team_id AS st2 ON st2.id = tt2.team_id ');

				$query->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
		  $query->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');

		  $query->where('prepro.prediction_id = ' . $prediction_id);

		$db->setQuery($query);
		$teams = $db->loadObjectList();

		if (!$teams)
		{
		}

			$mitems = array();

		foreach ($teams as $team)
		{
			$mitems[] = HTMLHelper::_('select.option',  $team->id, '&nbsp;' . $team->match_date . ' ( ' . $team->roundname . ' ) ' . ' -> [ ' . $team->home . ' - ' . $team->away . ' ] ');
		}

			  $output = HTMLHelper::_('select.genericlist',  $mitems, $this->name . '[]', 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $this->value, $this->id);

			return $output;
	}
}

