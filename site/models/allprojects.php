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

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');


/**
 * sportsmanagementModelallprojects
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelallprojects extends JModelList
{

var $_identifier = "clubs";
	
	/**
	 * sportsmanagementModelallprojects::__construct()
	 * 
	 * @param mixed $config
	 * @return void
	 */
	public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'v.name',
                        'v.picture',
                        'v.website',
                        'l.name',
                        's.name',
                        'v.location',
                        'v.country'
                        );
                parent::__construct($config);
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
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Initialise variables.
		$app = JFactory::getApplication('site');
        
        // List state information
		//$value = JRequest::getUInt('limit', $app->getCfg('list_limit', 0));
		$value = $this->getUserStateFromRequest($this->context.'.limit', 'limit', $app->getCfg('list_limit', 0));
        $this->setState('list.limit', $value);
        
        //$temp_user_request = $this->getUserStateFromRequest($this->context.'.limit', 'limit', $app->getCfg('list_limit', 0));
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($temp_user_request,true).'</pre>'   ),'');

		$value = JRequest::getUInt('limitstart', 0);
		$this->setState('list.start', $value);
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($this->context,true).'</pre>'   ),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);
        
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_leagues', 'filter_search_leagues', '');
        if ( !$temp_user_request )
        {
//            $temp_user_request = JRequest::getInt( "l", 0 );
//            JRequest::setVar( "l", 0 );
        }
        
		$this->setState('filter.search_leagues', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_seasons', 'filter_search_seasons', '');
		$this->setState('filter.search_seasons', $temp_user_request);

        //$filter_order = JRequest::getCmd('filter_order');
        $filter_order = $this->getUserStateFromRequest($this->context.'.filter_order', 'filter_order', '', 'string');
        if (!in_array($filter_order, $this->filter_fields)) 
        {
			$filter_order = 'v.name';
		}
        
        //$filter_order_Dir = JRequest::getCmd('filter_order_Dir');
        $filter_order_Dir = $this->getUserStateFromRequest($this->context.'.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
        if (!in_array(strtoupper($filter_order_Dir), array('ASC', 'DESC', ''))) 
        {
			$filter_order_Dir = 'ASC';
		}

        $this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);
  
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ordering<br><pre>'.print_r($filter_order,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' direction<br><pre>'.print_r($filter_order_Dir,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context<br><pre>'.print_r($this->context,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' listOrder<br><pre>'.print_r($listOrder,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' orderCol<br><pre>'.print_r($orderCol,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		//parent::populateState('v.name', 'ASC');
	}
    
    
    /**
     * sportsmanagementModelallplaygrounds::getListQuery()
     * 
     * @return
     */
    function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $search	= $this->getState('filter.search');
        $search_nation	= $this->getState('filter.search_nation');
        
        $search_leagues	= $this->getState('filter.search_leagues');
        $search_seasons	= $this->getState('filter.search_seasons');
        
        
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        // Select some fields
		$query->select('v.*');
        $query->select('l.country,l.name as leaguename');
        $query->select('s.name as seasonname');
        $query->select('CASE WHEN CHAR_LENGTH( v.alias ) THEN CONCAT_WS( \':\', v.id, v.alias ) ELSE v.id END AS slug');
        $query->select('CASE WHEN CHAR_LENGTH( l.alias ) THEN CONCAT_WS( \':\', l.id, l.alias ) ELSE l.id END AS leagueslug');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS v');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON l.id = v.league_id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s ON s.id = v.season_id');
//        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
//        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt.project_id');
        
//        // Join over the users for the checked out user.
//		$query->select('uc.name AS editor');
//		$query->join('LEFT', '#__users AS uc ON uc.id = v.checked_out');
        
        
        if ($search)
		{
        $query->where('LOWER(v.name) LIKE '.$db->Quote('%'.$search.'%'));
        }
        if ($search_nation)
		{
        $query->where("l.country = '".$search_nation."'");
        }
        
        if ($search_leagues)
		{
        $query->where('v.league_id = ' . $search_leagues);
        }
        if ($search_seasons)
		{
        $query->where('v.season_id = ' . $search_seasons);
        }
        
        $query->group('v.id');
        

        $query->order($db->escape($this->getState('filter_order', 'v.name')).' '.$db->escape($this->getState('filter_order_Dir', 'ASC') ) );
        
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' ordering<br><pre>'.print_r($this->getState('filter_order'),true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' direction<br><pre>'.print_r($this->getState('filter_order_Dir'),true).'</pre>'),'');
        
		return $query;

	}
    
    
}

?>    