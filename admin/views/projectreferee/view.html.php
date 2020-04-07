<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage projectreferee
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementViewProjectReferee
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewProjectReferee extends sportsmanagementView
{

	/**
	 * sportsmanagementViewProjectReferee::init()
	 *
	 * @return
	 */
	public function init()
	{
		$lists = array();

			  $this->_persontype = $this->jinput->get('persontype');

		if (empty($this->_persontype))
		{
			$this->_persontype    = $this->app->getUserState("$this->option.persontype", '0');
		}

			  $this->project_id    = $this->item->project_id;
		$mdlProject = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
		$project = $mdlProject->getProject($this->project_id);
		$this->project    = $project;

			  $person_id    = $this->item->person_id;
		$mdlPerson = BaseDatabaseModel::getInstance("player", "sportsmanagementModel");
		$project_person = $mdlPerson->getPerson(0, $person_id);
		/**
 * name für den titel setzen
 */
		$this->item->name = $project_person->lastname . ' - ' . $project_person->firstname;
		$this->project_person    = $project_person;
		$extended = sportsmanagementHelper::getExtended($this->item->extended, 'projectreferee');
		$this->extended    = $extended;
	}


	/**
	 * sportsmanagementViewProjectReferee::addToolbar()
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		$this->jinput->set('hidemainmenu', true);
		$isNew = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_REF_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_REF_NEW');
		$this->icon = 'projectreferee';
		$this->app->setUserState("$this->option.pid", $this->item->project_id);
		$this->app->setUserState("$this->option.persontype", $this->_persontype);
		parent::addToolbar();
	}

}
