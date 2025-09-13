<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage plugins
 * @file       jsm_registercomp.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;

if (! defined('JSM_PATH')) {
    DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

JLoader::import('components.com_sportsmanagement.helpers.browser', JPATH_ADMINISTRATOR);



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
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' params <br><pre>'.print_r($params ,true).'</pre>'   ),'');
 
$classpath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'browser.php';
JLoader::register('Browser', $classpath);
  
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
//$option = JRequest::getCmd('option');
$jsmjinput = $app->input;
//$query = $db->getQuery(true);

//$view = JRequest::get('view');
$option = $jsmjinput->getCmd('option');

$plugin_id = PluginHelper::getPlugin('system','jsm_registercomp')->id;
switch ( $option )
{
  case 'com_sportsmanagement':
    if ($app->isClient('administrator'))
				{
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' option <br><pre>'.print_r($option ,true).'</pre>'   ),'');    
     // $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' plugin_id <br><pre>'.print_r($plugin_id ,true).'</pre>'   ),'');
      if ($this->params->get('load_debug', 1) ) {
      //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params <br><pre>'.print_r($this->params ,true).'</pre>'   ),'');    
try
{
// Create an instance of a default JHttp object.
$http = HttpFactory::getHttp();      
// Prepare the data.
$data = array('homepage' => Uri::base(), 'notes' => '', 'homepagename' => $app->getCfg('sitename') , 'isadmin' => 1 );
// Invoke the POST request.
$response = $http->post('https://www.fussballineuropa.de/diddipoeler/jsmpaket.php', $data);      

// Create an instance of a default JHttp object.
$http = HttpFactory::getHttp();      
// Prepare the data.
$data = array('homepage' => Uri::root(), 'notes' => '', 'homepagename' => $app->getCfg('sitename') , 'isadmin' => 0 );
// Invoke the POST request.
$response = $http->post('https://www.fussballineuropa.de/diddipoeler/jsmpaket.php', $data);
}
catch (Exception $e)
{
//$this->app->enqueueMessage(Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');	
}      
        
        if ( $plugin_id )
        {
$object = new stdClass();            
$object->extension_id = $plugin_id;
$object->enabled = 0;            
//$result = Factory::getDbo()->updateObject('#__extensions', $object, 'extension_id');          
        }
        
      }
    }

    if ($app->isClient('site'))
				{

$browser = new Browser();
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' option <br><pre>'.print_r($browser->getBrowser() ,true).'</pre>'   ),'');

      //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' option <br><pre>'.print_r($option ,true).'</pre>'   ),'');
      $_ref = $_SERVER['HTTP_USER_AGENT'];
      //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' _ref <br><pre>'.print_r($_ref ,true).'</pre>'   ),'');

    /**
    unter edge
    Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0

    unter chrome
    Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36

    unter firefox
    Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0

    unter opera
    Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0


    */

if ( str_contains($_SERVER['HTTP_USER_AGENT'], 'Firefox') ) {
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' Firefox'.''   ),'');
$startjsm = (int) $this->params->get('load_firefox', 0);
}
elseif ( str_contains($_SERVER['HTTP_USER_AGENT'], 'Edg') ) {
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' Edge'.''   ),'');
$startjsm = (int) $this->params->get('load_edge', 0);
}
elseif ( str_contains($_SERVER['HTTP_USER_AGENT'], 'OPR') ) {
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' Opera'.''   ),'');
$startjsm = (int) $this->params->get('load_opera', 0);
}
else{
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' Google Chrome'.''   ),'');
$startjsm = (int) $this->params->get('load_chrome', 0);
}

//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' starten '.$startjsm.''   ),'');


$keinstart = $startjsm ? 0 : Factory::getApplication()->redirect('https://www.google.com', 403);
    }





    break;
}
        

        
    
      
    
    
      
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
