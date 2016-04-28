<?php
/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.26
* @file		administrator/components/sportsmanagement/models/divisions.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license This file is part of Joomla Sports Management.
*
* Joomla Sports Management is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Joomla Sports Management is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Joomla Sports Management. If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von Joomla Sports Management.
*
* Joomla Sports Management ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* Joomla Sports Management wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
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
jimport('joomla.application.component.modellist');


/**
 * sportsmanagementModelDivisions
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelDivisions extends JModelList
{
	var $_identifier = "divisions";
    var $_project_id = 0;
	
    /**
     * sportsmanagementModelDivisions::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'dv.name',
                        'dv.alias',
                        'dv.id',
                        'dv.ordering',
                        'dv.picture'
                        );
                parent::__construct($config);
                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($getDBConnection);
                $app = JFactory::getApplication();
                $this->jinput = $app->input;
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
        
        //$app->enqueueMessage(JText::_('sportsmanagementModelsmquotes populateState context<br><pre>'.print_r($this->context,true).'</pre>'   ),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);
        
        $value = JRequest::getUInt('limitstart', 0);
		$this->setState('list.start', $value);

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('dv.name', 'asc');
	}
    
	/**
	 * sportsmanagementModelDivisions::getListQuery()
	 * 
	 * @return
	 */
	protected function getListQuery()
	{
		$app	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
        //$this->jinput = $app->input;
        //$this->_project_id	= $app->getUserState( "$option.pid", '0' );
        $this->_project_id = $this->jinput->getVar('pid');
        
        //$app->enqueueMessage(JText::_('sportsmanagementModelDivisions _project_id<br><pre>'.print_r($this->_project_id,true).'</pre>'),'Notice');
        
       
        // Create a new query object.
        $query = $this->_db->getQuery(true);
        $query->select('dv.*,dvp.name AS parent_name,u.name AS editor');
        $query->from('#__sportsmanagement_division AS dv');
        $query->join('LEFT', '#__sportsmanagement_division AS dvp ON dvp.id = dv.parent_id');
        $query->join('LEFT', '#__users AS u ON u.id = dv.checked_out');

        $query->where(' dv.project_id = ' . $this->_project_id);
        
        if ($this->getState('filter.search') )
		{
        $query->where('LOWER(dv.name) LIKE ' . $this->_db->Quote( '%' . $this->getState('filter.search') . '%' ));
        }
        
        $query->order($this->_db->escape($this->getState('list.ordering', 'dv.name')).' '.
                $this->_db->escape($this->getState('list.direction', 'ASC')));

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = ' <br><pre>'.print_r($query->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }

		return $query;
	}




	
	/**
	* Method to return a divisions array (id, name)
	*
	* @param int $project_id
	* @access  public
	* @return  array
	* @since 0.1
	*/
	function getDivisions($project_id)
	{
	   $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $starttime = microtime(); 
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        $query->select('id AS value,name AS text');
        $query->from('#__sportsmanagement_division');
        $query->where('project_id = ' . $project_id);
        $query->order('name ASC');
        
		$db->setQuery( $query );
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		if ( !$result = $db->loadObjectList("value") )
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
			return array();
		}
		else
		{
			return $result;
		}
		
	}
	
	/**
	 * return count of project divisions
	 *
	 * @param int project_id
	 * @return int
	 */
	function getProjectDivisionsCount($project_id)
	{
	   $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $starttime = microtime(); 
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        
        $query->select('count(*) AS count');
        $query->from('#__sportsmanagement_division AS d');
        $query->join('INNER', '#__sportsmanagement_project AS p on p.id = d.project_id');
        $query->where('p.id = ' . $project_id);
                
		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }
        
		return $db->loadResult();
	}
	
}
?>
