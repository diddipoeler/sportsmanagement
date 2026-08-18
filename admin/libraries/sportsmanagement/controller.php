<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage libraries
 * @file       controller.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/**
 * JSMControllerAdmin
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class JSMControllerAdmin extends AdminController
{
	public $team_club_id = 0;
	public $jsmapp;
	public $jsmjinput;
	public $jsmoption;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws Exception
	 * @since  1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->jsmapp    = Factory::getApplication();
		$this->jsmjinput = $this->jsmapp->getInput();
		$this->jsmoption = $this->jsmjinput->getCmd('option');

		if (Factory::getConfig()->get('debug'))
		{
			Log::add(
				Text::_(__METHOD__ . ' ' . __LINE__ . ' layout' . $this->jsmjinput->getVar('layout')),
				Log::NOTICE,
				'jsmerror'
			);
		}
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * Overrides AdminController::saveorder to check the core.admin permission.
	 *
	 * @return boolean True on success
	 *
	 * @since 1.6
	 */
	public function saveorder()
	{
		if (!$this->jsmapp->getIdentity()->authorise('core.admin', $this->option))
		{
			throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		return parent::saveorder();
	}

	/**
	 * JSMControllerAdmin::cancel()
	 *
	 * @return void
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
	}
}

/**
 * JSMControllerForm
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class JSMControllerForm extends FormController
{
	public $team_club_id = 0;
	public $jsmdb;
	public $jsmapp;
	public $jsmjinput;
	public $jsmoption;
	public $jsmdocument;
	public $jsmuser;
	public $jsmdate;
	public $club_id = 0;
	public $person_id = 0;
	public $team_id = 0;
	public $insert_id = 0;

	/**
	 * Class Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws Exception
	 * @since  1.5
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->jsmdb = sportsmanagementHelper::getDBConnection();
		$this->jsmapp = Factory::getApplication();
		$this->jsmjinput = $this->jsmapp->getInput();
		$this->jsmoption = $this->jsmjinput->getCmd('option');
		$this->team_club_id = $this->jsmapp->getUserState($this->jsmoption . '.club_id', '0');
		$this->jsmdocument = $this->jsmapp->getDocument();
		$this->jsmuser = $this->jsmapp->getIdentity();
		$this->jsmdate = Factory::getDate();
	}

	/**
	 * JSMControllerForm::import()
	 *
	 * @return void
	 */
	public function import()
	{
		$message = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_IMPORT');
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message);
	}

	/**
	 * JSMControllerForm::export()
	 *
	 * @return void
	 */
	public function export()
	{
		$message = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_EXPORT');
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message);
	}

	/**
	 * JSMControllerForm::cancelmodal()
	 *
	 * @param mixed $key
	 *
	 * @return void
	 */
	public function cancelmodal($key = null)
	{
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
	}

	/**
	 * JSMControllerForm::save()
	 *
	 * @param mixed $key
	 * @param mixed $urlVar
	 *
	 * @return boolean
	 */
	public function save($key = null, $urlVar = null)
	{
		if (!Session::checkToken())
		{
			throw new RuntimeException(Text::_('JINVALID_TOKEN'), 403);
		}

		$post        = $this->jsmjinput->post->getArray();
		$tmpl        = $this->jsmjinput->getVar('tmpl');
		$model       = $this->getModel($this->view_item);
		$data        = $this->jsmjinput->getVar('jform', array(), 'post', 'array');
		$setRedirect = '';
		$createTeam  = $this->jsmjinput->getVar('createTeam');

		if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
		{
			switch ($this->view_item)
			{
				case 'round':
					switch ($this->jsmjinput->get('task'))
					{
						case 'save':
							if (!$data)
							{
								$data['round_date_first'] = '0000-00-00';
								$data['round_date_last'] = '0000-00-00';
							}
							break;
					}
					break;
			}
		}

		$return = $model->save($data);
		$modelError = $model->getError();

		if ($modelError)
		{
			$this->jsmapp->enqueueMessage($modelError, 'error');
		}

		$this->club_id = $this->jsmapp->getUserState($this->jsmoption . '.club_id', '0');
		$this->person_id = $this->jsmapp->getUserState($this->jsmoption . '.person_id', '0');
		$this->team_id = $this->jsmapp->getUserState($this->jsmoption . '.team_id', '0');
		$this->insert_id = $this->jsmjinput->getInt('insert_id');

		$id = $this->insert_id ? $this->insert_id : ($data['id'] ?? 0);

		if (empty($data['id']))
		{
			$id = $this->jsmjinput->getInt('insert_id');
		}

		if ($return)
		{
			switch ($this->view_item)
			{
				case 'club':
					if ($createTeam)
					{
						$mdlTeam = BaseDatabaseModel::getInstance('team', 'sportsmanagementModel');
						$team_name = $data['name'];
						$team_short_name = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $team_name), 0, 3));

						$tpost['id'] = 0;
						$tpost['name'] = $team_name;
						$tpost['short_name'] = $team_short_name;
						$tpost['club_id'] = $this->club_id;
						$mdlTeam->save($tpost);
					}
					break;

				case 'rounds':
					$setRedirect = '&pid=' . $post['pid'];
					break;

				case 'projects':
					$setRedirect = '&pid=' . $id;
					break;

				case 'project':
					$id = $this->jsmjinput->getInt('insert_project_id');
					$setRedirect = '&pid=' . $id;
					break;

				case 'projectteam':
					$setRedirect = '&pid=' . $data['project_id'];
					break;
			}

			switch ($this->getTask())
			{
				case 'apply':
					$message = Text::_('JLIB_APPLICATION_SAVE_SUCCESS');

					if ($tmpl)
					{
						switch ($this->view_item)
						{
							case 'club':
								$this->setRedirect('index.php?option=com_sportsmanagement&view=' . $this->view_item . '&layout=edit&tmpl=component&id=' . $this->club_id, $message);
								break;

							default:
								$this->setRedirect('index.php?option=com_sportsmanagement&view=' . $this->view_item . '&layout=edit&tmpl=component&id=' . $id, $message);
								break;
						}
					}
					else
					{
						switch ($this->view_item)
						{
							case 'club':
								$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($this->club_id) . $setRedirect, false), $message);
								break;

							case 'player':
								$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($this->person_id) . $setRedirect, false), $message);
								break;

							case 'team':
								$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&club_id=' . $this->team_club_id . $this->getRedirectToItemAppend($this->team_id) . $setRedirect, false), $message);
								break;

							default:
								$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id) . $setRedirect, false), $message);
								break;
						}
					}
					break;

				case 'save2copy':
					$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id) . $setRedirect, false));
					break;

				case 'save2new':
					$message = Text::_('JLIB_APPLICATION_SAVE_SUCCESS');
					$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend(null, $urlVar) . $setRedirect, false), $message);
					break;

				default:
					$message = Text::_('JLIB_APPLICATION_SAVE_SUCCESS');

					if ($tmpl)
					{
						$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
					}
					else
					{
						switch ($this->view_item)
						{
							case 'club':
								$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&club_id=' . $this->club_id . $this->getRedirectToListAppend(), false), $message);
								break;

							case 'team':
								$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&club_id=' . $this->team_club_id . '&team_id=' . $this->team_id . $this->getRedirectToListAppend(), false), $message);
								break;

							default:
								$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend() . $setRedirect, false), $message);
								break;
						}
					}
					break;
			}

			return true;
		}

		$message = $modelError ?: Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED');
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id) . $setRedirect, false), $message, 'error');

		return false;
	}

	/**
	 * Function that allows child controller access to model data after the data
	 * has been saved.
	 *
	 * @param BaseDatabaseModel $model The data model object.
	 * @param array $validData The validated data.
	 *
	 * @return void
	 */
	protected function postSaveHook(BaseDatabaseModel $model, $validData = array())
	{
		return;
	}
}
