<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       predictionproteamid.php
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
 * JFormFieldpredictionproteamid
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class JFormFieldpredictionproteamid extends FormField
{
	var    $_name = 'predictionproteamid';


	/**
	 * JFormFieldpredictionproteamid::getInput()
	 *
	 * @return
	 */
	protected function getInput()
	{
		$db = sportsmanagementHelper::getDBConnection();
		  $app = Factory::getApplication();
		$option = 'com_sportsmanagement';

			  $prediction_id = $app->getUserState("$option.prediction_id", '0');

			  // Welche tabelle soll genutzt werden
		$params = ComponentHelper::getParams('com_sportsmanagement');
		$database_table    = $params->get('cfg_which_database_table');

			  $query    = $db->getQuery(true);
		  $query->select('tl.id AS id,t.name as teamname');
		  $query->from('#__sportsmanagement_project_team AS tl');
		  $query->join('LEFT', '#__sportsmanagement_season_team_id AS st on tl.team_id = st.id');
		  $query->join('LEFT', '#__sportsmanagement_team AS t on st.team_id = t.id');
		  $query->join('INNER', '#__sportsmanagement_prediction_project as prepro on prepro.project_id = tl.project_id');
		  $query->where('prepro.prediction_id = ' . $prediction_id);
		  $query->group('tl.id');

		$db->setQuery($query);

			 $teams = $db->loadObjectList();

		if (!$teams)
		{
		}

			$mitems = array();

		foreach ($teams as $team)
		{
			$mitems[] = HTMLHelper::_('select.option',  $team->id, '&nbsp;' . ' ( ' . $team->teamname . ' ) ');
		}

			  $output = HTMLHelper::_('select.genericlist',  $mitems, $this->name . '[]', 'class="inputbox" multiple="multiple" size="' . count($mitems) . '"', 'value', 'text', $this->value, $this->id);

			return $output;
	}
}

