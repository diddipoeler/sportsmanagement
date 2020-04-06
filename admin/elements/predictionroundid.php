<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       predictionroundid.php
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
 * JFormFieldpredictionroundid
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldpredictionroundid extends FormField
{
	var    $_name = 'predictionroundid';

	/**
	 * JFormFieldpredictionroundid::getInput()
	 *
	 * @return
	 */
	protected function getInput()
	{
		$db = sportsmanagementHelper::getDBConnection();
		  $app            = Factory::getApplication();
		$option                = 'com_sportsmanagement';

			  $prediction_id = $app->getUserState("$option.prediction_id", '0');

			  // Welche tabelle soll genutzt werden
		$params = ComponentHelper::getParams('com_sportsmanagement');
		$database_table    = $params->get('cfg_which_database_table');

			  $query    = $db->getQuery(true);
		  $query->select('r.id AS id,r.name as roundname');
		  $query->from('#__sportsmanagement_match AS m');
		  $query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id');
		  $query->join('INNER', '#__sportsmanagement_prediction_project as prepro on prepro.project_id = r.project_id');
		  $query->where('prepro.prediction_id = ' . $prediction_id);
		  $query->group('r.id');

		$db->setQuery($query);

			 $teams = $db->loadObjectList();

		if (!$teams)
		{
		}

			$mitems = array();

		foreach ($teams as $team)
		{
			$mitems[] = HTMLHelper::_('select.option',  $team->id, '&nbsp;' . ' ( ' . $team->roundname . ' ) ');
		}

			  $output = HTMLHelper::_('select.genericlist',  $mitems, $this->name . '[]', 'class="inputbox" multiple="multiple" size="' . count($mitems) . '"', 'value', 'text', $this->value, $this->id);

			return $output;
	}
}

