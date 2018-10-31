<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      github.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');


/**
 * sportsmanagementControllergithub
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementControllergithub extends JControllerForm
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
        $this->app = Factory::getApplication();
		$this->jinput = $this->app->input;
		$this->option = $this->jinput->getCmd('option');
        $this->model = $this->getModel();
        $this->post = $this->jinput->post->getArray(array());  

		//$this->registerTask('saveshort',	'saveshort');
	}
    
    /**
     * sportsmanagementControllergithub::cancel()
     * 
     * @param mixed $key
     * @return void
     */
    function cancel($key = NULL)
{
    $msg = Text::_('JLIB_HTML_BEHAVIOR_CLOSE');
		
        
        
            $link = 'index.php?option='.$this->option.'&view=close&tmpl=component';
        
        
		//echo $link.'<br />';
		$this->setRedirect($link,$msg);
    }
    
/**
 * sportsmanagementControllergithub::addissue()
 * 
 * @return void
 */
function addissue()
{
  // Check for request forgeries
		JSession::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));

       $msg = $this->model->addissue();
       $this->setRedirect('index.php?option=com_sportsmanagement&view=github&tmpl=component&layout=github_result',$msg);    
    
}

/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'github', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

}

?>
