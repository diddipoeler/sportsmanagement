<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       installhelper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementControllerinstallhelper
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2021
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerinstallhelper extends JSMControllerForm
{

	/**
	 * Class Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @return void
	 * @since  1.5
	 */
	function __construct($config = array())
	{
		parent::__construct($config);
	}
    
    
    /**
     * sportsmanagementControllerinstallhelper::savesportstype()
     * 
     * @return void
     */
    function savesportstype()
    {
        $msg = '';
        $model = $this->getModel();
        $post = $this->jsmjinput->post->getArray(array());
        //$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <pre>'.print_r($post,true) .'</pre>'), '');
		$msg   = $model->savesportstype($post);
        
        if ( !$msg )
        {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=installhelper&step=1&error=1', $msg);    
        }
        else
        {
        $this->setRedirect('index.php?option=com_sportsmanagement&step=2', $msg);    
        }
		//$this->setRedirect('index.php?option=com_sportsmanagement&view=installhelper&step=2', $msg);
        
        
        
    }
    
    /**
	 * Proxy for getModel.
	 *
	 * @since 1.6
	 */
	public function getModel($name = 'installhelper', $prefix = 'sportsmanagementModel', $config = Array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
    

}
