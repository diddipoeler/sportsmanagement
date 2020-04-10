<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       databasetool.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * sportsmanagementControllerDatabaseTool
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerDatabaseTool extends BaseController
{

	/**
	 * sportsmanagementControllerDatabaseTool::repair()
	 *
	 * @return void
	 */
	function repair()
	{
		$app        = Factory::getApplication();
		$model      = $this->getModel('databasetool');
		$jsm_tables = $model->getSportsManagementTables();

		foreach ($jsm_tables as $key => $value)
		{
			$model->setSportsManagementTableQuery($value, $this->getTask());
		}

		$msg = 'Alle Tabellen repariert';
		$this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools', $msg);

	}

	/**
	 * sportsmanagementControllerDatabaseTool::optimize()
	 *
	 * @return void
	 */
	function optimize()
	{
		$app        = Factory::getApplication();
		$model      = $this->getModel('databasetool');
		$jsm_tables = $model->getSportsManagementTables();

		foreach ($jsm_tables as $key => $value)
		{
			$model->setSportsManagementTableQuery($value, $this->getTask());
		}

		$msg = 'Alle Tabellen optimiert';
		$this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools', $msg);
	}


	/**
	 * sportsmanagementControllerDatabaseTool::truncate()
	 *
	 * @return void
	 */
	function truncate()
	{
		$app        = Factory::getApplication();
		$model      = $this->getModel('databasetool');
		$jsm_tables = $model->getSportsManagementTables();

		foreach ($jsm_tables as $key => $value)
		{
			$model->setSportsManagementTableQuery($value, $this->getTask());
		}

		$msg = 'Alle Tabellen geleert';
		$this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools', $msg);
	}


	/**
	 * sportsmanagementControllerDatabaseTool::truncatejl()
	 *
	 * @return void
	 */
	function truncatejl()
	{
		$app       = Factory::getApplication();
		$model     = $this->getModel('databasetool');
		$jl_tables = $model->getJoomleagueTablesTruncate();

		foreach ($jl_tables as $key => $value)
		{
			$model->setSportsManagementTableQuery($value, 'TRUNCATE');
		}

		$msg = 'Alle JL Tabellen geleert';
		$this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools', $msg);
	}


	/**
	 * sportsmanagementControllerDatabaseTool::updatetemplatemasters()
	 *
	 * @return void
	 */
	function updatetemplatemasters()
	{

		$msg = 'Alle Templates angepasst';
		$this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools', $msg);
	}

	/**
	 * sportsmanagementControllerDatabaseTool::picturepath()
	 *
	 * @return void
	 */
	function picturepath()
	{
		$app   = Factory::getApplication();
		$model = $this->getModel('databasetool');
		$model->setNewPicturePath();
		$msg = 'Alle Bilderpfade angepasst';
		$this->setRedirect('index.php?option=com_sportsmanagement&view=databasetools', $msg);
	}

}
