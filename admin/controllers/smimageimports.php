<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      smimageimports.php
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
 * sportsmanagementControllersmimageimports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllersmimageimports extends JControllerAdmin
{
  
  /**
   * sportsmanagementControllersmimageimports::import()
   * 
   * @return void
   */
  function import()
    {
        $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $model	= $this->getModel();
        $result = $model->import();
        
        $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));

}

	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'smimageimport', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}