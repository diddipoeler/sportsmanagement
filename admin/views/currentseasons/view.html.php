<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage currentseasons
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementViewCurrentseasons
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewCurrentseasons extends sportsmanagementView
{

	/**
	 * sportsmanagementViewCurrentseasons::init()
	 *
	 * @return void
	 */
	public function init()
	{

		if ($this->items) {
			$mdlProjectDivisions = BaseDatabaseModel::getInstance("divisions", "sportsmanagementModel");
			$mdlProjectPositions = BaseDatabaseModel::getInstance("Projectpositions", "sportsmanagementModel");
			$mdlProjectReferees = BaseDatabaseModel::getInstance("Projectreferees", "sportsmanagementModel");
			$mdlProjecteams = BaseDatabaseModel::getInstance("Projectteams", "sportsmanagementModel");
			$mdlRounds = BaseDatabaseModel::getInstance("Rounds", "sportsmanagementModel");

			foreach ($this->items as $item) {
				$item->count_projectdivisions = $mdlProjectDivisions->getProjectDivisionsCount($item->id);
				$item->count_projectpositions = $mdlProjectPositions->getProjectPositionsCount($item->id);
				$item->count_projectreferees = $mdlProjectReferees->getProjectRefereesCount($item->id);
				$item->count_projectteams = $mdlProjecteams->getProjectTeamsCount($item->id);
				$item->count_matchdays = $mdlRounds->getRoundsCount($item->id);
			}
		}

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.6
	 */
	protected function addToolbar()
	{

		// Set toolbar items for the page
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_TITLE');
		$this->icon = 'currentseason';

		parent::addToolbar();
	}
}