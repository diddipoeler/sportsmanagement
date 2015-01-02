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



/**
 * sportsmanagementModelLeagues
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelLeagues extends JModelList
{
	var $_identifier = "leagues";
	
    /**
     * sportsmanagementModelLeagues::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'obj.name',
                        'obj.alias',
                        'obj.short_name',
                        'obj.country',
                        'st.name',
                        'obj.id',
                        'obj.ordering',
                        'ag.name',
                        'fed.name'
                        );
                //$config['dbo'] = sportsmanagementHelper::getDBConnection();  
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
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context ->'.$this->context.''),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);
        
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_association', 'filter_search_association', '');
		$this->setState('filter.search_association', $temp_user_request);
        
        $value = JRequest::getUInt('limitstart', 0);
		$this->setState('list.start', $value);

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('obj.name', 'asc');
	}
    
	/**
	 * sportsmanagementModelLeagues::getListQuery()
	 * 
	 * @return
	 */
	protected function getListQuery()
	{
		$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        //$search	= $this->getState('filter.search');
        //$search_nation	= $this->getState('filter.search_nation');
        
        // Create a new query object.		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Select some fields
		$query->select('obj.name,obj.short_name,obj.alias,obj.associations,obj.country,obj.ordering,obj.id,obj.picture,obj.checked_out,obj.checked_out_time');
        $query->select('st.name AS sportstype');
		// From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_league as obj');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = obj.sports_type_id');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
        
        $query->select('ag.name AS agegroup');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_agegroup AS ag ON ag.id = obj.agegroup_id');
        
        $query->select('fed.name AS fedname');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations AS fed ON fed.id = obj.associations');
        
        if ($this->getState('filter.search'))
		{
        $query->where('LOWER(obj.name) LIKE '.$db->Quote('%'.$this->getState('filter.search').'%'));
        }
        
        if ($this->getState('filter.search_nation'))
		{
        $query->where('obj.country LIKE '.$db->Quote(''.$this->getState('filter.search_nation').''));
        }
        
        if ($this->getState('filter.search_association'))
		{
        $query->where('obj.associations = '.$this->getState('filter.search_association') );
        }
        

        $query->order($db->escape($this->getState('list.ordering', 'obj.name')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
                
                
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');        
 
		if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }
        
        return $query;
	}

  



    
    /**
     * Method to return a leagues array (id,name)
     *
     * @access	public
     * @return	array seasons
     * @since	1.5.0a
     */
    public function getLeagues()
    {
        $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $search_nation = '';
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($app,true).'</pre>'),'Notice');
        
        if ( $app->isAdmin() )
        {
        $search_nation	= $this->getState('filter.search_nation');
        //$search_nation	= self::getState('filter.search_nation');
        }
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection();
        // Create a new query object.
        $query = $db->getQuery(true);
        $query->select('id,name');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_league');
        
        if ($search_nation)
		{
        $query->where('country LIKE '.$db->Quote(''.$search_nation.''));
        }
        
        $query->order('name ASC');

        $db->setQuery($query);
        if (!$result = $db->loadObjectList())
        {
            sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getErrorMsg<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            return array();
        }
        foreach ($result as $league)
        {
            $league->name = JText::_($league->name);
        }
        return $result;
    }

	
}
?>