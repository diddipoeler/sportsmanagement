<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage libraries
 * @file       controller.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Log\Log;

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
	var $team_club_id = 0;

	/**
	 * Constructor.
	 *
	 * @param   array An optional associative array of configuration settings.
	 *
	 * @throws Exception
	 * @since  1.6
	 * @see    JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->jsmapp    = Factory::getApplication();
		$this->jsmjinput = $this->jsmapp->input;
		$this->jsmoption = $this->jsmjinput->getCmd('option');

	}
    
    /**
	 * Method to save the submitted ordering values for records.
	 *
	 * Overrides JControllerAdmin::saveorder to check the core.admin permission.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.6
	 */
	public function saveorder()
	{
		if (!JFactory::getUser()->authorise('core.admin', $this->option))
		{
			JError::raiseError(500, JText::_('JERROR_ALERTNOAUTHOR'));
			jexit();
		}

		return parent::saveorder();
	}

	/**
	 * JSMControllerAdmin::cancel()
	 *
	 * @return void
	 */
	function cancel()
	{
		$msg = '';
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);
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
	var $team_club_id = 0;

	/**
	 * Class Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @return void
	 * @throws Exception
	 * @since  1.5
	 */
	function __construct($config = array())
	{
		parent::__construct($config);
		$this->jsmdb = sportsmanagementHelper::getDBConnection();

		// Reference global application object
		$this->jsmapp = Factory::getApplication();

		// JInput object
		$this->jsmjinput    = $this->jsmapp->input;
		$this->jsmoption    = $this->jsmjinput->getCmd('option');
		$this->team_club_id = $this->jsmapp->getUserState("$this->jsmoption.club_id", '0');
		$this->jsmdocument  = Factory::getDocument();
		$this->jsmuser      = Factory::getUser();
		$this->jsmdate      = Factory::getDate();

		//      if ( $this->view_list == 'people' )
		//      {
		//      $this->view_list == 'persons' ;
		//      }
		//      $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$this->view_list), '');
		//      $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$this->view_item), '');
		/**
		 *
		 * Map the apply task to the save method.
		 */
		// $this->registerTask('apply', 'save');
	}


	/**
	 * JSMControllerForm::import()
	 *
	 * @return void
	 */
	function import()
	{
		$message = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_IMPORT');
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message);
	}

	/**
	 * JSMControllerForm::export()
	 *
	 * @return void
	 */
	function export()
	{
		$message = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_EXPORT');
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message);
	}

	/**
	 * JSMControllerForm::cancelmodal()
	 *
	 * @param   mixed  $key
	 *
	 * @return void
	 */
	function cancelmodal($key = null)
	{
		$msg = '';
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);
	}

	/**
	 * JSMControllerForm::save()
	 *
	 * @param   mixed  $key
	 * @param   mixed  $urlVar
	 *
	 * @return boolean
	 */
	function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

				
		// Initialise variables.
		$post            = $this->jsmjinput->post->getArray();
		$tmpl            = $this->jsmjinput->getVar('tmpl');
		$model           = $this->getModel($this->view_item);
		$data            = $this->jsmjinput->getVar('jform', array(), 'post', 'array');
		$setRedirect     = '';
		$createTeam      = $this->jsmjinput->getVar('createTeam');
		
		//$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' task '.$this->jsmjinput->get('task')), '');
		//$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' view_list '.$this->view_list), '');
		//$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' view_item '.$this->view_item), '');
		//$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.'data<pre>'.print_r($data,true).'</pre>'), '');
		
		if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
		
			switch ($this->view_item)
			{
			case 'round':
			switch ($this->jsmjinput->get('task'))
			{
			case 'save':
			if ( !$data )
			{
				$data['round_date_first'] = '0000-00-00';
				$data['round_date_last'] = '0000-00-00';
			}	
			break;
			}
				
				
				
			break;
			}
		}
		
		
		$return          = $model->save($data);
		
		$this->jsmapp->enqueueMessage($model->getError(), 'error');
		
		$this->club_id   = $this->jsmapp->getUserState("$this->jsmoption.club_id", '0');
		$this->person_id = $this->jsmapp->getUserState("$this->jsmoption.person_id", '0');
		$this->team_id   = $this->jsmapp->getUserState("$this->jsmoption.team_id", '0');
$this->insert_id   =      $this->jsmjinput->getInt('insert_id');
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . 'post<pre>'.print_r($post,true).'</pre>'), Log::NOTICE, 'jsmerror');
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . 'data<pre>'.print_r($data,true).'</pre>'), Log::NOTICE, 'jsmerror');
		
		$id = $this->insert_id ? $this->insert_id : $data['id'];

		if ( empty($data['id']) )
		{
			$id = $this->jsmjinput->getInt('insert_id');
		}

		if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend'))
		{
		}

		// $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' return '.$return), '');
		// $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' getRedirectToItemAppend '.$this->getRedirectToItemAppend($id)), '');
		if ($return)
		{
			switch ($this->view_item)
			{
				case 'club':

					if ($createTeam)
					{
						$mdlTeam         = BaseDatabaseModel::getInstance("team", "sportsmanagementModel");
						$team_name       = $data['name'];
						$team_short_name = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $team_name), 0, 3));

						$tpost['id']         = 0;
						$tpost['name']       = $team_name;
						$tpost['short_name'] = $team_short_name;
						$tpost['club_id']    = $this->club_id;
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

			// Set the redirect based on the task.
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
		else
		{
			$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id) . $setRedirect, false), $message);

			return false;
		}
	}

	/**
	 * Function that allows child controller access to model data after the data
	 * has been saved.
	 *
	 * @param   BaseDatabaseModel  $model      The data model object.
	 * @param   array              $validData  The validated data.
	 *
	 * @return void
	 */
	protected function postSaveHook(BaseDatabaseModel $model, $validData = array())
	{
		return;
	}

}
