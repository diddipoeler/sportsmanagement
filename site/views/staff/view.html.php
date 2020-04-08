<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage staff
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewStaff
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewStaff extends JViewLegacy
{

	/**
	 * sportsmanagementViewStaff::display()
	 *
	 * @param   mixed $tpl
	 * @return
	 */
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document = Factory::getDocument();

			  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;

		$model = $this->getModel();
		$option = $jinput->getCmd('option');
		$model::$projectid = $jinput->getInt('p', 0);
		$model::$personid = $jinput->getInt('pid', 0);
		$model::$teamplayerid = $jinput->getInt('pt', 0);

		//        $mdlPerson->projectid = $jinput->getInt( 'p', 0 );
		//		$mdlPerson->personid = $jinput->getInt( 'pid', 0 );
		//		$mdlPerson->teamplayerid = $jinput->getInt( 'pt', 0 );

			  //      sportsmanagementModelPerson::projectid = $jinput->getInt( 'p', 0 );
		//		sportsmanagementModelPerson::personid = $jinput->getInt( 'pid', 0 );
		//		sportsmanagementModelPerson::teamplayerid = $jinput->getInt( 'pt', 0 );

			  $config = sportsmanagementModelProject::getTemplateConfig($this->getName(), $model::$cfg_which_database);
		$person = sportsmanagementModelPerson::getPerson(0, $model::$cfg_which_database);

			  $this->project = sportsmanagementModelProject::getProject($model::$cfg_which_database);
		$this->overallconfig = sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database);
		$this->config = $config;
		$this->person = $person;
		$this->showediticon = sportsmanagementModelPerson::getAllowed($config['edit_own_player']);

			$staff = $model->getTeamStaff();
		$titleStr = Text::sprintf('COM_SPORTSMANAGEMENT_STAFF_ABOUT_AS_A_STAFF', sportsmanagementHelper::formatName(null, $this->person->firstname, $this->person->nickname, $this->person->lastname, $this->config["name_format"]));

			  $this->inprojectinfo = $staff;
		$this->history = $model->getStaffHistory('ASC');
		$this->stats = $model->getStats();
		$this->staffstats = $model->getStaffStats();
		$this->historystats = $model->getHistoryStaffStats();
		$this->title = $titleStr;

		$extended = sportsmanagementHelper::getExtended($person->extended, 'staff');
		$this->extended = $extended;
		$document->setTitle($titleStr);

			  $view = $jinput->getVar("view");
		$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'components/' . $option . '/assets/css/' . $view . '.css' . '" type="text/css" />' . "\n";
		$document->addCustomTag($stylelink);

		parent::display($tpl);
	}

}
