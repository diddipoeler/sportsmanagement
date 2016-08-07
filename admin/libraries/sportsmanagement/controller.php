<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

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
	 * @param	array	$config		An optional associative array of configuration settings.
	 * @return	void
	 * @since	1.5
	 */
	function __construct($config = array())
	{
		parent::__construct($config);
        $this->jsmdb = sportsmanagementHelper::getDBConnection();
        // Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        //$this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmdocument = JFactory::getDocument();
        $this->jsmuser = JFactory::getUser();
        $this->jsmdate = JFactory::getDate();
//        $this->option = $this->jsmjinput->getCmd('option');
        $this->club_id = $this->jsmapp->getUserState( "$this->option.club_id", '0' );
        
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($config,true).'</pre>'),'');
        
		// Map the apply task to the save method.
		//$this->registerTask('apply', 'save');
	}

 /**
  * JSMControllerForm::save()
  * 
  * @param mixed $key
  * @param mixed $urlVar
  * @return void
  */
 function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
     		// Initialise variables.
		//$app = JFactory::getApplication();
        //$db = sportsmanagementHelper::getDBConnection();
        $id	= $this->jsmjinput->getInt('id');
        $tmpl = $this->jsmjinput->getVar('tmpl');
		$model = $this->getModel($this->view_item);
        $data = $this->jsmjinput->getVar('jform', array(), 'post', 'array');
        $createTeam = $this->jsmjinput->getVar('createTeam');
        $return = $model->save($data);
        if ( empty($id) )
        {
            $id = $this->jsmdb->insertid();
        }
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTask<br><pre>'.print_r($this->getTask(),true).'</pre>'),'');
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' return<br><pre>'.print_r($return,true).'</pre>'),'');
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' key<br><pre>'.print_r($key,true).'</pre>'),'');
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' urlVar<br><pre>'.print_r($urlVar,true).'</pre>'),'');
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' this->option <br><pre>'.print_r($this->option ,true).'</pre>'),'');
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' this->view_item <br><pre>'.print_r($this->view_item ,true).'</pre>'),'');
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' this->view_list<br><pre>'.print_r($this->view_list,true).'</pre>'),'');
        
        switch ($this->view_item)
		{
		case 'club':  
        
        if ($createTeam)
		{
			 $mdlTeam = JModelLegacy::getInstance("team", "sportsmanagementModel");
             $team_name = $data['name'];
		     $team_short_name = strtoupper(substr(ereg_replace("[^a-zA-Z]","",$team_name),0,3));
			
				$tpost['id'] = 0;
				$tpost['name'] = $team_name;
				$tpost['short_name'] = $team_short_name ;
				$tpost['club_id'] = $id;
				$mdlTeam->save($tpost);
        }
        break;
        }     
        
        // Set the redirect based on the task.
		switch ($this->getTask())
		{
			case 'apply':
				$message = JText::_('JLIB_APPLICATION_SAVE_SUCCESS');
                if ( $tmpl )
                {
				$this->setRedirect('index.php?option=com_sportsmanagement&view='.$this->view_item.'&layout=edit&tmpl=component&id='.$id, $message);
                }
                else
                {
                //$this->setRedirect('index.php?option=com_sportsmanagement&view=club&layout=edit&id='.$id, $message);   
                $this->setRedirect(
								JRoute::_(
										'index.php?option=' . $this->option . '&view=' . $this->view_item .
												 $this->getRedirectToItemAppend($id), false), $message); 
                }
				break;

			case 'save2new':
            $message = JText::_('JLIB_APPLICATION_SAVE_SUCCESS');
						$this->setRedirect(
								JRoute::_(
										'index.php?option=' . $this->option . '&view=' . $this->view_item .
												 $this->getRedirectToItemAppend(null, $urlVar), false), $message);
            
            break;
			default:
            $message = JText::_('JLIB_APPLICATION_SAVE_SUCCESS');
            if ( $tmpl )
                {
				$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
                }
                else
                {
                    switch ($this->view_item)
		{
		case 'club':  
                	$this->setRedirect(
								JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&club_id='.$this->club_id.$this->getRedirectToListAppend(), false), $message);
                                break;
                                default:
                                $this->setRedirect(
								JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false), $message);
                                break;
                                }   
                }
				break;
		}

		return true;   
     }      
    
}    