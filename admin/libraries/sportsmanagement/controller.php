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

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');


/**
 * JSMControllerAdmin
 *
 * @package
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class JSMControllerAdmin extends JControllerAdmin
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
        $this->app = JFactory::getApplication();
        $this->jinput = $this->app->input;
        $this->option = $this->jinput->getCmd('option');

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
class JSMControllerForm extends JControllerForm
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
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmdocument = JFactory::getDocument();
        $this->jsmuser = JFactory::getUser();
        $this->jsmdate = JFactory::getDate();
//        $this->option = $this->jsmjinput->getCmd('option');
        //$this->club_id = $this->jsmapp->getUserState( "$this->jsmoption.club_id", '0' );

//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($config,true).'</pre>'),'');

        // Map the apply task to the save method.
        //$this->registerTask('apply', 'save');
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
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        //$app = JFactory::getApplication();
        //$db = sportsmanagementHelper::getDBConnection();
        $post = $this->jsmjinput->post->getArray();
        $tmpl = $this->jsmjinput->getVar('tmpl');
        $model = $this->getModel($this->view_item);
        $data = $this->jsmjinput->getVar('jform', array(), 'post', 'array');
        $setRedirect = '';
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'');
        $createTeam = $this->jsmjinput->getVar('createTeam');
        $return = $model->save($data);
        $this->club_id = $this->jsmapp->getUserState("$this->jsmoption.club_id", '0');
        $this->person_id = $this->jsmapp->getUserState("$this->jsmoption.person_id", '0');
        $this->team_id = $this->jsmapp->getUserState("$this->jsmoption.team_id", '0');

        $id = $this->jsmdb->insertid();
        if (empty($id)) {
            $id = $this->jsmjinput->getInt('insert_id');
        }

        if (JComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend')) {
            $this->jsmapp->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' this->club_id<br><pre>' . print_r($this->club_id, true) . '</pre>'), '');
            $this->jsmapp->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' this->team_id<br><pre>' . print_r($this->team_id, true) . '</pre>'), '');
            $this->jsmapp->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' this->view_item <br><pre>' . print_r($this->view_item, true) . '</pre>'), '');
            $this->jsmapp->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' this->view_list<br><pre>' . print_r($this->view_list, true) . '</pre>'), '');


        }
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTask<br><pre>'.print_r($this->getTask(),true).'</pre>'),'');
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' return<br><pre>'.print_r($return,true).'</pre>'),'');
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' id<br><pre>'.print_r($id,true).'</pre>'),'');
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' key<br><pre>'.print_r($key,true).'</pre>'),'');
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' urlVar<br><pre>'.print_r($urlVar,true).'</pre>'),'');
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' this->option <br><pre>'.print_r($this->option ,true).'</pre>'),'');


        if ($return) {
            switch ($this->view_item) {
                case 'club':

                    if ($createTeam) {
                        $mdlTeam = JModelLegacy::getInstance("team", "sportsmanagementModel");
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
                    $message = JText::_('JLIB_APPLICATION_SAVE_SUCCESS');
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
                                $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($this->club_id) . $setRedirect, false), $message);
                                break;
                            case 'person':
                                $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($this->person_id) . $setRedirect, false), $message);
                                break;
                            case 'team':
                                $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($this->team_id) . $setRedirect, false), $message);
                                break;
                            default:
                                $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id) . $setRedirect, false), $message);
                                break;
                        }

                    }
                    break;
                case 'save2copy':
                    $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id) . $setRedirect, false));
                    break;

                case 'save2new':
                    $message = JText::_('JLIB_APPLICATION_SAVE_SUCCESS');
                    $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend(null, $urlVar) . $setRedirect, false), $message);

                    break;
                default:
                    $message = JText::_('JLIB_APPLICATION_SAVE_SUCCESS');
                    if ($tmpl) {
                        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
                    } else {
                        switch ($this->view_item) {
                            case 'club':
                                $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&club_id=' . $this->club_id . $this->getRedirectToListAppend(), false), $message);
                                break;
                            case 'team':
                                $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . '&team_id=' . $this->team_id . $this->getRedirectToListAppend(), false), $message);
                                break;
                            default:
                                $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend() . $setRedirect, false), $message);
                                break;
                        }
                    }
                    break;
            }

            return true;
        } else {
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id) . $setRedirect, false), $message);
            //JError::raiseError( 4711, $this->jsmdb->getErrorMsg() );
            return false;
        }
    }

    /**
     * Function that allows child controller access to model data after the data
     * has been saved.
     *
     * @param JModelLegacy $model The data model object.
     * @param array $validData The validated data.
     *
     * @return void
     */
    protected function postSaveHook(JModelLegacy $model, $validData = array())
    {
        return;
    }

}
