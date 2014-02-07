<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');
 

class sportsmanagementControllersmimageimports extends JControllerAdmin
{
  
  function import()
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $model	= $this->getModel();
        $result = $model->import();
        
        $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));

}

	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'smimageimport', $prefix = 'sportsmanagementModel') 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}