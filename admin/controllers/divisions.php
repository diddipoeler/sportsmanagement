<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      divisions.php
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
 * sportsmanagementControllerdivisions
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerdivisions extends JControllerAdmin
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

		//$this->registerTask('saveshort',	'saveshort');
	}

	/**
	 * sportsmanagementControllerdivisions::saveOrder()
	 * 
	 * @return void
	 */
	public function saveOrder()
	{
	$this->project_id = $this->app->getUserState( "$this->option.pid", '0' );

	$model = $this->getModel();
    //$pks = JFactory::getApplication()->input->getInt( 'cid', array() );	//is sanitized
//	$order = JFactory::getApplication()->input->getInt('order', array() );   
    
    $pks = $this->jinput->get('cid',array(),'array');
    $order = $this->jinput->get('order',array(),'array');
    
    $msg = $model->saveorder($pks,$order);
    $this->setRedirect('index.php?option=com_sportsmanagement&view=divisions&pid='.$this->project_id,$msg);   
    }   
    
/**
 * sportsmanagementControllerdivisions::saveshort()
 * 
 * @return void
 */
function saveshort()
	{
	   $this->project_id = $this->app->getUserState( "$this->option.pid", '0' );
	   $model = $this->getModel();
       $msg = $model->saveshort();
       $this->setRedirect('index.php?option=com_sportsmanagement&view=divisions&pid='.$this->project_id,$msg);
    } 
      
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'Division', $prefix = 'sportsmanagementModel', $config = Array()  ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}