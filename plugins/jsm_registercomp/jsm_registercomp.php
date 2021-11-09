<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage plugins
 * @file       jsm_registercomp.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * System plugin
 * 1) onBeforeRender()
 * 2) onAfterRender()
 * 3) onAfterRoute()
 * 4) onAfterDispatch()
 * These events are triggered in 'JAdministrator' class in file 'application.php' at location
 * 'Joomla_base\administrator\includes'.
 * 5) onAfterInitialise()
 * This event is triggered in 'JApplication' class in file 'application.php' at location
 * 'Joomla_base\libraries\joomla\application'.
 */

defined('_JEXEC') or die();
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;


if (! defined('JSM_PATH')) {
    DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');





/**
 * PlgSystemjsm_registercomp
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2021
 * @version $Id$
 * @access public
 */
class PlgSystemjsm_registercomp extends CMSPlugin
{
     

    /**
     * PlgSystemjsm_registercomp::__construct()
     * 
     * @param mixed $subject
     * @param mixed $params
     * @return void
     */
    public function __construct(&$subject, $params)
    {
        parent::__construct($subject, $params);
        $app = Factory::getApplication();
 
 

  
    }

 
    
    /**
     * PlgSystemjsm_registercomp::onBeforeRender()
     * 
     * @return void
     */
    public function onBeforeRender()
    {
        $app = Factory::getApplication();
        
      
    }

  
   
    /**
     * PlgSystemjsm_registercomp::onAfterRender()
     * 
     * @return void
     */
    public function onAfterRender()
    {
        $app = Factory::getApplication();
       
      
    }

 
    
    /**
     * PlgSystemjsm_registercomp::onAfterRoute()
     * 
     * @return void
     */
    public function onAfterRoute()
    {
        $app = Factory::getApplication();
    
    
      
    }

  
   
    /**
     * PlgSystemjsm_registercomp::onAfterDispatch()
     * 
     * @return void
     */
    public function onAfterDispatch()
    {
        $app = Factory::getApplication();
        

    }

  
   
    /**
     * PlgSystemjsm_registercomp::onAfterInitialise()
     * 
     * @return void
     */
    public function onAfterInitialise()
    {
        $app = Factory::getApplication();
        
      
      
    }
  
  

}

?>
