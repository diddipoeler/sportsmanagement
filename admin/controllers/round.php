<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * HelloWorld Controller
 */
class sportsmanagementControllerround extends JControllerForm
{

function save()
	{
	   $post		= JRequest::get('post');
       $id		= JRequest::getInt('id');
       $model = $this->getModel('round');
	   if ($model->save($post))
		{
		//	$msg = JText::_('COM_JOOMLEAGUE_ADMIN_PERSON_CTRL_SAVED');
    }
    else
		{
			//$msg = JText::_('COM_JOOMLEAGUE_ADMIN_PERSON_CTRL_ERROR_SAVE').$model->getError();
		}
        
    if ($this->getTask() == 'save')
		{
			$link = 'index.php?option=com_sportsmanagement&view=rounds&pid='.$post['project_id'];
		}
		else
		{
			$link = 'index.php?option=com_sportsmanagement&view=round&layout=edit&id='.$id;
		}
		#echo $msg;
		$this->setRedirect($link,$msg);    
    }   

}
