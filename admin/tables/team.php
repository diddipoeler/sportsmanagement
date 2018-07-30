<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      team.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage tables
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
// import Joomla table library
jimport('joomla.database.table');
// Include library dependencies
jimport('joomla.filter.input');

/**
 * sportsmanagementTableTeam
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementTableTeam extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(& $db) {
	   $db = sportsmanagementHelper::getDBConnection();
		parent::__construct('#__sportsmanagement_team', 'id', $db);
	}

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @access public
	 * @return boolean True on success
	 * @since 1.0
	 */
	function check()
	{
		if (empty($this->name)) {
			$this->setError(JText::_('NAME REQUIRED'));
			return false;
		}
		
		// add default middle size name
		if (empty($this->middle_name)) {
			$parts = explode(" ", $this->name);
			$this->middle_name = substr($parts[0], 0, 20);
		}
	
		// add default short size name
		if (empty($this->short_name)) {
			$parts = explode(" ", $this->name);
			$this->short_name = substr($parts[0], 0, 2);
		}
	
		// setting alias
        $this->alias = JFilterOutput::stringURLSafe( $this->name );
        
//		if ( empty( $this->alias ) )
//		{
//			$this->alias = JFilterOutput::stringURLSafe( $this->name );
//		}
//		else {
//			$this->alias = JFilterOutput::stringURLSafe( $this->alias ); // make sure the user didn't modify it to something illegal...
//		}
		
		return true;
	}
	
    /**
     * sportsmanagementTableTeam::bind()
     * 
     * @param mixed $array
     * @param string $ignore
     * @return
     */
    public function bind($array, $ignore = '')
   {
      //$app = JFactory::getApplication();
      //$option = JFactory::getApplication()->input->getCmd('option');
      //$app->enqueueMessage(JText::_('sportsmanagementTableTeam bind season_ids<br><pre>'.print_r($array,true).'</pre>'   ),'');
      //$app->enqueueMessage(JText::_('sportsmanagementTableTeam bind season_ids<br><pre>'.print_r($array['season_ids'],true).'</pre>'   ),'');
        
      if (isset($array['season_ids']) && is_array($array['season_ids'])) {
         $array['season_ids'] = implode(',', $array['season_ids']);
      }
      return parent::bind($array, $ignore);
   }
    
    
	
	
}
?>
