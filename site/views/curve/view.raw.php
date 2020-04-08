<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage curve
 * @file       view.raw.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * sportsmanagementViewCurve
 *
 * @package
 * @author    abcde
 * @copyright 2015
 * @version   $Id$
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
		$option = Factory::getApplication()->input->getCmd('option');
		$division = Factory::getApplication()->input->getInt('division', 0);

		$model = $this->getModel();
		$rankingconfig = sportsmanagementModelProject::getTemplateConfig("ranking", $model::$cfg_which_database);
		$flashconfig = sportsmanagementModelProject::getTemplateConfig("flash", $model::$cfg_which_database);
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName(), $model::$cfg_which_database);

		$this->project = sportsmanagementModelProject::getProject($model::$cfg_which_database);

		if (isset($this->project))
		{
			$this->overallconfig = sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database);

			if (!isset($this->overallconfig['seperator']))
			{
				$this->overallconfig['seperator'] = ":";
			}

			$this->config = $config;
			$this->model = $model;
			$this->colors = sportsmanagementModelProject::getColors($rankingconfig['colors'], $model::$cfg_which_database);
			$this->division = $model->getDivision($division);
			$this->team1 = $model->getTeam1($division);
			$this->team2 = $model->getTeam2($division);

					  $this->_setChartdata(array_merge($flashconfig, $rankingconfig));
		}

		// Parent::display( $tpl );
	}

	/**
	 * assign the chartdata object for open flash chart library
	 *
	 * @param  $config
	 * @return unknown_type
	 */
	function _setChartdata($config)
	{
		$model = $this->getModel();
		$rounds    = sportsmanagementModelProject::getRounds('ASC', $model::$cfg_which_database);
		$round_labels = array();

		foreach ($rounds as $r)
		{
			$round_labels[] = $r->name;
		}

		$division    = $this->get('division');
		$data = $model->getDataByDivision($division->id);

	}
}



