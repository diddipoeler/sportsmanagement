<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jsmpersons.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage controllers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
 

/**
 * sportsmanagementControllerjsmpersons
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2019
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerjsmpersons extends JSMControllerAdmin
{
   
  /**
   * sportsmanagementControllerjsmpersons::assign()
   * 
   * @return
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
     * sportsmanagementControllerjsmpersons::close()
     * 
     * @return
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
     * sportsmanagementControllerjsmpersons::saveshort()
     * 
     * @return
     */
    function saveshort()
	{
	   $model = $this->getModel();
       $model->saveshort();
       $this->setRedirect(Route::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
    } 
      
	/**
	 * sportsmanagementControllerjsmpersons::getModel()
	 * 
	 * @param string $name
	 * @param string $prefix
	 * @param mixed $config
	 * @return
	 */
	public function getModel($name = 'jsmperson', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}