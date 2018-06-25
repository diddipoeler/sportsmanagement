<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      sportstype.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * SportsManagement Model
 */
class sportsmanagementModelsportstype extends JSMModelAdmin
{

    /**
	 * Override parent constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	
//    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($config,true).'</pre>'),'');
//    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getName<br><pre>'.print_r($this->getName(),true).'</pre>'),'');
    
	}	   

     /**
	 * return 
	 *
	 * @param int sportstype_id
	 * @return int
	 */
	function getSportstype($sportstype_id)
	{
		$this->jsmquery->clear();
        $this->jsmquery->select('*');
        $this->jsmquery->from('#__sportsmanagement_sports_type');
        $this->jsmquery->where('id = '.$sportstype_id);
		$this->jsmdb->setQuery($this->jsmquery);
		return $this->jsmdb->loadObject();
	}
	
	
}
