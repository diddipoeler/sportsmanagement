<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       projects.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementControllerprojects
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerprojects extends JSMControllerAdmin
{

	
	/**
	 * sportsmanagementControllerprojects::saveshort()
	 * 
	 * @return void
	 */
	function saveshort()
	{
		$model = $this->getModel();
		$msg   = $model->saveshort();
		$this->setRedirect('index.php?option=com_sportsmanagement&view=projects', $msg);
	}
    
    
    /**
     * sportsmanagementControllerprojects::setleaguechampion()
     * 
     * @return void
     */
    function setleaguechampion()
	{
		$model = $this->getModel();
		$msg   = $model->setleaguechampion();
		$this->setRedirect('index.php?option=com_sportsmanagement&view=projects', $msg);
	}
	
	/**
	 * sportsmanagementControllerprojects::copy()
	 * 
	 * @return void
	 */
	function copy()
	{
		$model = $this->getModel();
		//$msg   = $model->saveshort();
		$this->setRedirect('index.php?option=com_sportsmanagement&view=projects', $msg);
	}


	/**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'Project', $prefix = 'sportsmanagementModel', $config = Array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}


}
