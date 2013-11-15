<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');
 
/**
 * SportsManagements Controller
 */
class sportsmanagementControllertemplates extends JControllerAdmin
{
  
	
    public function changetemplate() 
	{
	$post=JRequest::get('post');
    $msg = '';
    $this->setRedirect('index.php?option=com_sportsmanagement&view=template&layout=edit&id='.$post['new_id'],$msg);	
	}
    
    
    /**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'template', $prefix = 'sportsmanagementModel') 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}