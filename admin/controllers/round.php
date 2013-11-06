<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * SportsManagement Controller
 */
class sportsmanagementControllerround extends JControllerForm
{


  /**
	 * Method to remove a matchdays in rounds
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
function deletematches()
  {
  $mainframe =& JFactory::getApplication();
    $pks = JRequest::getVar('cid', array(), 'post', 'array');
    $model = $this->getModel('round');
    $model->deleteRoundMatches($pks);
	
    $this->setRedirect('index.php?option=com_sportsmanagement&view=rounds');  
    
    
  }
  
  /**
	 * Method to remove a round
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function remove()
	{
	$mainframe =& JFactory::getApplication();
    $pks = JRequest::getVar('cid', array(), 'post', 'array');
    $model = $this->getModel('round');
    $model->delete($pks);
	
    $this->setRedirect('index.php?option=com_sportsmanagement&view=rounds');    
        
   }    
  

}
