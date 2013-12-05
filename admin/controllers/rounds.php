<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');
 
/**
 * SportsManagements Controller
 */
class sportsmanagementControllerrounds extends JControllerAdmin
{
	
  /**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		//$this->registerTask('saveshort',	'saveshort');
	}
  
  
  
  
  /**
	 * Method to add mass rounds
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
  function massadd()
	{
		
        // Check for request forgeries
		JRequest::checkToken() or die('JINVALID_TOKEN');

        $model = $this->getModel();
       $msg = $model->massadd();
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
	}
    
    /**
	 * Method to delete matches in round
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */

    function deleteRoundMatches()
	{
	   $model = $this->getModel();
       $pks = JRequest::getVar('cid', null, 'post', 'array');
       $msg = $model->deleteRoundMatches($pks);
       //$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
       $this->setRedirect('index.php?option=com_sportsmanagement&view=rounds',$msg);
    } 
    
    
  /**
	 * Method to update checked rounds
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */

    function saveshort()
	{
	   $model = $this->getModel();
       $msg = $model->saveshort();
       //$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
       $this->setRedirect('index.php?option=com_sportsmanagement&view=rounds',$msg);
    } 
  
  
  /**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'Round', $prefix = 'sportsmanagementModel') 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	


	
}