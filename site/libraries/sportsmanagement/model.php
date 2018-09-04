<?PHP        
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      model.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage libraries
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

/**
 * JSMModelAdmin
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class JSMModelAdmin extends AdminModel
{
    
    
    /**
     * JSMModelAdmin::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
	{
// Reference global application object
$this->jsmapp = Factory::getApplication('site');
$this->jsmjinput = $this->jsmapp->input;
$this->jsmoption = $this->jsmjinput->getCmd('option');
$this->jsmview = $this->jsmjinput->getCmd('view');	   
       parent::__construct($config);
       }
    
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		
	}
    
}

/**
 * JSMModelList
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class JSMModelList extends ListModel
{
    
    /**
     * JSMModelList::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
	{
// Reference global application object
$this->jsmapp = Factory::getApplication('site');
$this->jsmjinput = $this->jsmapp->input;
$this->jsmoption = $this->jsmjinput->getCmd('option');
$this->jsmview = $this->jsmjinput->getCmd('view');	   
       parent::__construct($config);
       }
    
    
}


/**
 * JSMModelLegacy
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class JSMModelLegacy extends BaseDatabaseModel
{

/**
 * JSMModelLegacy::__construct()
 * 
 * @param mixed $config
 * @return void
 */
public function __construct($config = array())
	{
// Reference global application object
$this->jsmapp = Factory::getApplication('site');
$this->jsmjinput = $this->jsmapp->input;
$this->jsmoption = $this->jsmjinput->getCmd('option');
$this->jsmview = $this->jsmjinput->getCmd('view');	   
       parent::__construct($config);
       }    

    
}
