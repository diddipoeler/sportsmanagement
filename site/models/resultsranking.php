<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage resultsranking
 * @file       resultsranking.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelResultsranking
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelResultsranking extends BaseDatabaseModel
{
	static $divisionid = 0;

	static $roundid = 0;

	static $projectid = 0;

	static $cfg_which_database = 0;

	static $show_ranking_reiter = 0;

	/**
	 * sportsmanagementModelResultsranking::__construct()
	 *
	 * @return
	 */
	function __construct()
	{
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;

		parent::__construct();
		self::$divisionid                                 = (int) $jinput->get('division', 0, '');
		self::$roundid                                    = (int) $jinput->get('r', 0, '');
		self::$projectid                                  = (int) $jinput->get('p', 0, '');
		self::$cfg_which_database                         = $jinput->get('cfg_which_database', 0, '');
		self::$show_ranking_reiter                        = $jinput->get('show_ranking_reiter', 0, '');
		sportsmanagementModelProject::$projectid          = self::$projectid;
		sportsmanagementModelProject::$cfg_which_database = self::$cfg_which_database;
	}

}

