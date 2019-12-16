<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      controller.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage libraries
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
var $team_club_id = 0;
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
var $team_club_id = 0;
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
      $this->team_club_id = $this->jsmapp->getUserState("$this->jsmoption.club_id", '0');
        $this->jsmdocument = Factory::getDocument();
        $this->jsmuser = Factory::getUser();
        $this->jsmdate = Factory::getDate();
//      if ( $this->view_list == 'people' )
//      {
//      $this->view_list == 'persons' ; 
//      }
//      $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$this->view_list), '');
//      $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$this->view_item), '');
        /** Map the apply task to the save method. **/
        //$this->registerTask('apply', 'save');
    }

	
    /**
     * JSMControllerForm::import()
     * 
     * @return void
     */
    function import()
{
    $message = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_IMPORT');
    $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list , false), $message);
    }
    
    /**
     * JSMControllerForm::export()
     * 
     * @return void
     */
    function export()
{
    $message = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_EXPORT');
    $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list , false), $message);
    }
    
	/**
	 * JSMControllerForm::cancelmodal()
	 * 
	 * @param mixed $key
	 * @return void
	 */
	function cancelmodal($key = NULL)
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
//$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' task '.$this->jsmjinput->get('task')), '');
        // Initialise variables.
        $post = $this->jsmjinput->post->getArray();
        $tmpl = $this->jsmjinput->getVar('tmpl');
        $model = $this->getModel($this->view_item);
        $data = $this->jsmjinput->getVar('jform', array(), 'post', 'array');
        $setRedirect = '';
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

        }
//$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' return '.$return), '');
//$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' getRedirectToItemAppend '.$this->getRedirectToItemAppend($id)), '');
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
                            case 'player':
                                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($this->person_id) . $setRedirect, false), $message);
                                break;
                            case 'team':
                                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item .'&club_id=' .$this->team_club_id. $this->getRedirectToItemAppend($this->team_id) . $setRedirect, false), $message);
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
                                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list .'&club_id=' .$this->team_club_id. '&team_id=' . $this->team_id . $this->getRedirectToListAppend(), false), $message);
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
