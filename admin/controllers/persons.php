<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      persons.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
 
/**
 * sportsmanagementControllerpersons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerpersons extends JSMControllerAdmin
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
		$post = Factory::getApplication()->input->post->getArray(array());
        // Check for request forgeries
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $model = $this->getModel();
       $msg = $model->storeAssign($post);
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
	}
    
    /**
     * sportsmanagementControllerpersons::close()
     * 
     * @return void
     */
    function close()
	{
		$post = Factory::getApplication()->input->post->getArray(array());
        // Check for request forgeries
		Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));

//        $model = $this->getModel();
//       $msg = $model->storeAssign($post);
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
       $this->setRedirect(Route::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
    } 
    
      
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'Person', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}