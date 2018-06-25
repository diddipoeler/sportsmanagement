<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      rounds.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

/**
 * sportsmanagementControllerrounds
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
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
        $this->app = JFactory::getApplication();
		$this->jinput = $this->app->input;
		$this->option = $this->jinput->getCmd('option');

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
		JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

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
       $pks = JFactory::getApplication()->input->getVar('cid', null, 'post', 'array');
       $msg = $model->deleteRoundMatches($pks);
       $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
       //$this->setRedirect('index.php?option=com_sportsmanagement&view=rounds',$msg);
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
	   $this->project_id = $this->app->getUserState( "$this->option.pid", '0' );
       $model = $this->getModel();
       $msg = $model->saveshort();
       //$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
       $this->setRedirect('index.php?option=com_sportsmanagement&view=rounds&pid='.$this->project_id,$msg);
    } 
  
  /**
	 * display the populate form
	 */
	public function populate()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$division_id = $jinput->getInt('division_id',0);
		
		$this->setRedirect('index.php?option='.$this->option.'&view=rounds&layout=populate&division_id='.$division_id);
	}

	
  /**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'Round', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	


	
}
