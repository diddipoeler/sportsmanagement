<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

// import Joomla table library
jimport('joomla.database.table');


/**
 * sportsmanagementTablePerson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementTablePerson extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(& $db)
	{
	   $db = sportsmanagementHelper::getDBConnection();
		parent::__construct( '#__'.COM_SPORTSMANAGEMENT_TABLE.'_person', 'id', $db );
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
		if ( empty( $this->firstname ) && empty( $this->lastname ) )
		{
			$this->setError( JText::_( 'ERROR FIRSTNAME OR LASTNAME REQUIRED' ) );
			return false;
		}
		$parts = array( trim( $this->firstname ), trim( $this->lastname ) );
		$alias = JFilterOutput::stringURLSafe( implode( ' ', $parts ) );
	
		// setting alias
		if ( empty( $this->alias ) )
		{
			$this->alias = $alias;
		}
		else {
			$this->alias = JFilterOutput::stringURLSafe( $this->alias ); // make sure the user didn't modify it to something illegal...
		}
		//should check name unicity
		return true;
	}
	
	/**
	 * Overloaded bind function
	 *
	 * @param       array           named array
	 * @return      null|string     null is operation was satisfactory, otherwise returns an error
	 * @see JTable:bind
	 * @since 1.5
	 */
	function bind($array, $ignore = '')
	{
	   $app = JFactory::getApplication();
      $option = JFactory::getApplication()->input->getCmd('option');
		
//    if (isset($array['extended']) && is_array($array['extended'])) 
//		{
//			// Convert the params field to a string.
//			$parameter = new JRegistry;
//			//$parameter->loadArray($array['extended']);
//			//$array['extended'] = (string)$parameter;
//            
//            $parameter->loadJSON($array['extended']);
//			$array['extended'] = $parameter->toArray($array['extended']);;
//		}
        
        if (isset($array['extended']) && is_array($array['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($array['extended']);
			$array['extended'] = (string)$parameter;
		}
        
    if (isset($array['season_ids']) && is_array($array['season_ids'])) 
    {
         $array['season_ids'] = implode(',', $array['season_ids']);
      }
          
		return parent::bind($array, $ignore);
    
    
	}
    
    /**
	 * Overloaded load function
	 *
	 * @param       int $pk primary key
	 * @param       boolean $reset reset data
	 * @return      boolean
	 * @see JTable:load
	 */
//	public function load($pk = null, $reset = true) 
//	{
//		if (parent::load($pk, $reset)) 
//		{
//			// Convert the params field to a registry.
//			$params = new JRegistry;
//			$params->loadJSON($this->extended);
//			//$params->toArray($this->extended);
//            $this->extended = $params->toArray($this->extended);
//            
//			return true;
//			
//		}
//		else
//		{
//			return false;
//		}
//	}
	
	

}
?>