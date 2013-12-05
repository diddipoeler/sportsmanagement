<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 

/**
 * sportsmanagementControllerpredictiongame
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementControllerpredictionproject extends JControllerForm
{

/**
	 * Method to store prediction project
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
  function store()
	{
		$post = JRequest::get('post');
        // Check for request forgeries
		JRequest::checkToken() or die('JINVALID_TOKEN');

        $model = $this->getModel();
       $msg = $model->save($post['jform']);
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
	}

}
?>