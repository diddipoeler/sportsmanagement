<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      controller.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage libraries
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;

/**
 * JSMControllerAdmin
 *
 * @package
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class JSMControllerAdmin extends AdminController
{

    /**
     * Constructor.
     *
     * @param    array An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     * @throws Exception
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->jsmapp = Factory::getApplication();
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');

    }

     /**
      * JSMControllerAdmin::cancel()
      * 
      * @return void
      */
     function cancel()
	{
	$msg = '';
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
	}

}


/**
 * JSMControllerForm
 *
 * @package
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class JSMControllerForm extends FormController
{

    /**
     * Class Constructor
     *
     * @param    array $config An optional associative array of configuration settings.
     * @throws Exception
     * @return    void
     * @since    1.5
     */
    function __construct($config = array())
    {
        parent::__construct($config);
        $this->jsmdb = sportsmanagementHelper::getDBConnection();
        // Reference global application object
        $this->jsmapp = Factory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmdocument = Factory::getDocument();
        $this->jsmuser = Factory::getUser();
        $this->jsmdate = Factory::getDate();
//        $this->option = $this->jsmjinput->getCmd('option');
        //$this->club_id = $this->jsmapp->getUserState( "$this->jsmoption.club_id", '0' );

//        $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($config,true).'</pre>'),'');

        // Map the apply task to the save method.
        //$this->registerTask('apply', 'save');
    }

	
	function cancel($key = NULL)
	{
	$msg = '';
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
	}
	
    /**
     * JSMControllerForm::save()
     *
     * @param mixed $key
     * @param mixed $urlVar
     * @return bool
     */
    function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // Initialise variables.
        //$app = Factory::::getApplication();
        //$db = sportsmanagementHelper::getDBConnection();
        $post = $this->jsmjinput->post->getArray();
        $tmpl = $this->jsmjinput->getVar('tmpl');
        $model = $this->getModel($this->view_item);
        $data = $this->jsmjinput->getVar('jform', array(), 'post', 'array');
        $setRedirect = '';
        //$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'');
        $createTeam = $this->jsmjinput->getVar('createTeam');
        $return = $model->save($data);
        $this->club_id = $this->jsmapp->getUserState("$this->jsmoption.club_id", '0');
        $this->person_id = $this->jsmapp->getUserState("$this->jsmoption.person_id", '0');
        $this->team_id = $this->jsmapp->getUserState("$this->jsmoption.team_id", '0');

        $id = $this->jsmdb->insertid();
        if (empty($id)) {
            $id = $this->jsmjinput->getInt('insert_id');
        }

        if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend')) {
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' this->club_id<br><pre>' . print_r($this->club_id, true) . '</pre>'), '');
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' this->team_id<br><pre>' . print_r($this->team_id, true) . '</pre>'), '');
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' this->view_item <br><pre>' . print_r($this->view_item, true) . '</pre>'), '');
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' this->view_list<br><pre>' . print_r($this->view_list, true) . '</pre>'), '');

$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'');
$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');            
        }

        if ($return) {
            switch ($this->view_item) {
                case 'club':

                    if ($createTeam) {
                        $mdlTeam = BaseDatabaseModel::getInstance("team", "sportsmanagementModel");
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
                case 'projectteam':
                    $setRedirect = '&pid=' . $data['project_id'];
                    break;
            }

            // Set the redirect based on the task.
            switch ($this->getTask()) {
                case 'apply':
                    $message = Text::_('JLIB_APPLICATION_SAVE_SUCCESS');
                    if ($tmpl) {

                        switch ($this->view_item) {
                            case 'club':
                                $this->setRedirect('index.php?option=com_sportsmanagement&view=' . $this->view_item . '&layout=edit&tmpl=component&id=' . $this->club_id, $message);
                                break;
                            default:
                                $this->setRedirect('index.php?option=com_sportsmanagement&view=' . $this->view_item . '&layout=edit&tmpl=component&id=' . $id, $message);
                                break;
                        }
                    } else {
                        switch ($this->view_item) {
                            case 'club':
                                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($this->club_id) . $setRedirect, false), $message);
                                break;
                            case 'person':
                                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($this->person_id) . $setRedirect, false), $message);
                                break;
                            case 'team':
                                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($this->team_id) . $setRedirect, false), $message);
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
                    if ($tmpl) {
                        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
                    } else {
                        switch ($this->view_item) {
                            case 'club':
                                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&club_id=' . $this->club_id . $this->getRedirectToListAppend(), false), $message);
                                break;
                            case 'team':
                                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&team_id=' . $this->team_id . $this->getRedirectToListAppend(), false), $message);
                                break;
                            default:
                                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend() . $setRedirect, false), $message);
                                break;
                        }
                    }
                    break;
            }

            return true;
        } else {
            $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id) . $setRedirect, false), $message);
            //JError::raiseError( 4711, $this->jsmdb->getErrorMsg() );
            return false;
        }
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
