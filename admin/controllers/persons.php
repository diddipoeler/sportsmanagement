<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');
 
/**
 * SportsManagements Controller
 */
class sportsmanagementControllerpersons extends JControllerAdmin
{
 
    /**
	 * Method to assign persons
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
  function assign()
	{
		$post = JRequest::get('post');
        // Check for request forgeries
		JRequest::checkToken() or die('JINVALID_TOKEN');

        $model = $this->getModel();
       $msg = $model->storeAssign($post);
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
	}
    
    
    
    /**
	 * Method to update checked persons
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
    function saveshort()
	{
	   $model = $this->getModel();
       $model->saveshort();
       $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
    } 
    
      
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'Person', $prefix = 'sportsmanagementModel') 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}