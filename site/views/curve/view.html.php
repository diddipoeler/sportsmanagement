<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage curve
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * https://www.chartjs.org/
 * https://github.com/chartjs/Chart.js
 * https://cdnjs.com/libraries/Chart.js
 *
 * https://www.chartjs.org/samples/latest/charts/line/basic.html
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

/**
 * sportsmanagementViewCurve
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewCurve extends sportsmanagementView
{

	/**
	 * sportsmanagementViewCurve::init()
	 *
	 * @return void
	 */
	function init()
	{

		$this->teamranking = array();

		if ($this->config['which_curve'])
		{
			$js = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.js';
			$this->document->addScript($js);
		}

			$this->season_id = sportsmanagementModelCurve::$season_id;
			$this->cfg_which_database = sportsmanagementModelCurve::$cfg_which_database;

		if (isset($this->project))
		{
			$teamid1 = sportsmanagementModelCurve::$teamid1;
			$teamid2 = sportsmanagementModelCurve::$teamid2;
			$options = array(    HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_CURVE_CHOOSE_TEAM')) );
			$divisions = sportsmanagementModelProject::getDivisions(0, sportsmanagementModelCurve::$cfg_which_database);

			if (count($divisions) > 0)
			{
				foreach ($divisions as $d)
				{
					$options = array();
					$teams = sportsmanagementModelProject::getTeams($d->id, 'name', sportsmanagementModelCurve::$cfg_which_database);
					$i = 0;

					foreach ((array) $teams as $t)
					{
										$options[] = HTMLHelper::_('select.option', $t->id, $t->name);

						if ($i == 0)
						{
											$teamid1 = $t->id;
						}

						if ($i == 1)
						{
							$teamid2 = $t->id;
						}

										$i++;
					}

					if ($this->config['which_curve'])
					{
						$team1select[$d->id] = HTMLHelper::_('select.genericlist', $options, 'tid1_' . $d->id, 'onchange="" class="inputbox" style="font-size:9px;"', 'value', 'text', $teamid1);
						$team2select[$d->id] = HTMLHelper::_('select.genericlist', $options, 'tid2_' . $d->id, 'onchange="" class="inputbox" style="font-size:9px;"', 'value', 'text', $teamid2);
					}
					else
					{
						$team1select[$d->id] = HTMLHelper::_('select.genericlist', $options, 'tid1_' . $d->id, 'onchange="reload_curve_chart_' . $d->id . '()" class="inputbox" style="font-size:9px;"', 'value', 'text', $teamid1);
						$team2select[$d->id] = HTMLHelper::_('select.genericlist', $options, 'tid2_' . $d->id, 'onchange="reload_curve_chart_' . $d->id . '()" class="inputbox" style="font-size:9px;"', 'value', 'text', $teamid2);
					}
				}
			}
			else
			{
				$divisions = array();
				$team1select = array();
				$team2select = array();
				$div = $this->model->getDivision(sportsmanagementModelCurve::$division);

				if (empty($div))
				{
						$div = new stdClass;
						$div->id = 0;
						$div->name = '';
				}

				$divisions[0] = $div;
				$teams = sportsmanagementModelProject::getTeams(sportsmanagementModelCurve::$division, 'name', sportsmanagementModelCurve::$cfg_which_database);

						$i = 0;

				foreach ((array) $teams as $t)
				{
						$options[] = HTMLHelper::_('select.option', $t->id, $t->name);

					if ($i == 0 && $teamid1 == 0)
					{
							// $teamid1 = $t->id;
							$teamid1 = $t->team_id;
					}

					if ($i == 1 && $teamid2 == 0)
					{
						// $teamid2 = $t->id;
						$teamid2 = $t->team_id;
					}

						$i++;
				}

				if ($this->config['which_curve'])
				{
					$team1select[$div->id] = HTMLHelper::_('select.genericlist', $options, 'tid1_' . $div->id, 'onchange="" class="inputbox" style="font-size:9px;"', 'value', 'text', $teamid1);
					$team2select[$div->id] = HTMLHelper::_('select.genericlist', $options, 'tid2_' . $div->id, 'onchange="" class="inputbox" style="font-size:9px;"', 'value', 'text', $teamid2);
				}
				else
				{
					$team1select[$div->id] = HTMLHelper::_('select.genericlist', $options, 'tid1_' . $div->id, 'onchange="reload_curve_chart_' . $div->id . '()" class="inputbox" style="font-size:9px;"', 'value', 'text', $teamid1);
					$team2select[$div->id] = HTMLHelper::_('select.genericlist', $options, 'tid2_' . $div->id, 'onchange="reload_curve_chart_' . $div->id . '()" class="inputbox" style="font-size:9px;"', 'value', 'text', $teamid2);
				}
			}

			if (!isset($this->overallconfig['seperator']))
			{
				$this->overallconfig['seperator'] = ":";
			}

			$rankingconfig = sportsmanagementModelProject::getTemplateConfig("ranking", sportsmanagementModelCurve::$cfg_which_database);
			$this->colors = sportsmanagementModelProject::getColors($rankingconfig['colors'], sportsmanagementModelCurve::$cfg_which_database);
			$this->divisions = $divisions;
			$this->division = $this->model->getDivision(sportsmanagementModelCurve::$division);
			$this->favteams = sportsmanagementModelProject::getFavTeams(sportsmanagementModelCurve::$cfg_which_database);
			$this->team1 = $this->model->getTeam1(sportsmanagementModelCurve::$division);
			$this->team2 = $this->model->getTeam2(sportsmanagementModelCurve::$division);
			$this->allteams = sportsmanagementModelProject::getTeams(sportsmanagementModelCurve::$division, 'name', sportsmanagementModelCurve::$cfg_which_database);
			$this->team1select = $team1select;
			$this->team2select = $team2select;

			if ($this->config['which_curve'])
			{
				$rounds    = sportsmanagementModelProject::getRounds('ASC', sportsmanagementModelCurve::$cfg_which_database);
				$this->round_labels = array();

				foreach ($rounds as $r)
				{
								  $this->round_labels[] = '"' . $r->name . '"';
				}

				$this->_setChartdata(array_merge(sportsmanagementModelProject::getTemplateConfig("flash", sportsmanagementModelCurve::$cfg_which_database), $this->config));
			}
			else
			{
				  $this->_setChartdata(array_merge(sportsmanagementModelProject::getTemplateConfig("flash", sportsmanagementModelCurve::$cfg_which_database), $this->config));
			}

				// Set page title
			  $pageTitle = Text::_('COM_SPORTSMANAGEMENT_CURVE_PAGE_TITLE');

			if (( isset($this->team1) ) && (isset($this->team1)))
			{
			}

			  $this->document->setTitle($pageTitle);
		}

	}

	/**
	 * assign the chartdata object for open flash chart library
	 *
	 * @param  $config
	 * @return unknown_type
	 */
	function _setChartdata($config)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		  $option = $jinput->getCmd('option');

			  $model = $this->getModel();
		$rounds    = sportsmanagementModelProject::getRounds('ASC', $model::$cfg_which_database);
		$round_labels = array();

		foreach ($rounds as $r)
		{
			$round_labels[] = $r->name;
		}

		$divisions    = $this->divisions;

		foreach ($divisions as $division)
		{
			$data = $model->getDataByDivision($division->id);
			$this->teamranking[$division->id] = $data;
		}
	}
}

