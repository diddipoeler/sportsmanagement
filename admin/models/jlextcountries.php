<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');
//require_once (JPATH_COMPONENT.DS.'models'.DS.'list.php');


/**
 * sportsmanagementModeljlextcountries
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeljlextcountries extends JModelList
{
	var $_identifier = "jlextcountries";
	
    /**
     * sportsmanagementModeljlextcountries::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'objcountry.name',
                        'objcountry.picture',
                        'objcountry.id',
                        'objcountry.alpha2',
                        'objcountry.alpha3',
                        'objcountry.itu',
                        'objcountry.fips',
                        'objcountry.ioc',
                        'objcountry.fifa',
                        'objcountry.ds',
                        'objcountry.wmo',
                        'objcountry.ordering',
                        'objcountry.checked_out',
                        'objcountry.checked_out_time'
                        );
                parent::__construct($config);
                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($getDBConnection);
        }
        
    /**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Initialise variables.
		$app = JFactory::getApplication('administrator');
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' request<br><pre>'.print_r($_REQUEST,true).'</pre>'),'');
        


		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		$federation = $this->getUserStateFromRequest($this->context.'.filter.federation', 'filter_federation', '');
		$this->setState('filter.federation', $federation);
        
        $value = JRequest::getUInt('limitstart', 0);
		$this->setState('list.start', $value);
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('objcountry.name', 'asc');
	}
    
	/**
	 * sportsmanagementModeljlextcountries::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
		$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');

        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        // Select some fields
		$query->select(implode(",",$this->filter_fields));
        $query->select('f.name as federation_name');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_countries AS objcountry');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_federations AS f ON f.id = objcountry.federation');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = objcountry.checked_out');
        
        if ($this->getState('filter.search'))
		{
        $query->where('LOWER(objcountry.name) LIKE '.$db->Quote('%'.$this->getState('filter.search').'%'));
        }
        if ($this->getState('filter.federation'))
		{
        $query->where('objcountry.federation = '.$this->getState('filter.federation'));
        }

        
        $query->order($db->escape($this->getState('list.ordering', 'objcountry.name')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = ' <br><pre>'.print_r($query->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
        
		return $query;
        
        
        
        
        
	}
      
    
    
    /**
     * sportsmanagementModeljlextcountries::getFederation()
     * 
     * @return void
     */
    function getFederation()
    {
        $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		// Select some fields
		$query->select('id as value,name as text');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_federations as objassoc');
        $db->setQuery($query);
		$results = $db->loadObjectList();
        
        if (!$results)
		{
//		  $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
//          $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
          $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_FEDERATIONS_NULL'),'Error');
		}  
        
		return $results;
    }




	
}
?>