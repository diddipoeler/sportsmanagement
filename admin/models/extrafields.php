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
 * sportsmanagementModelextrafields
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelextrafields extends JModelList
{
	var $_identifier = "extrafields";
	
    /**
     * sportsmanagementModelextrafields::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'objcountry.name',
                        'objcountry.template_backend',
                        'objcountry.template_frontend',
                        'objcountry.published',
                        'objcountry.id',
                        'objcountry.ordering',
                        'objcountry.checked_out',
                        'objcountry.checked_out_time',
                        
                        'objcountry.views_backend',
                        'objcountry.fieldtyp',
                        'objcountry.views_backend_field',
                        'objcountry.select_columns',
                        'objcountry.select_values'
                        
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
		$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Initialise variables.
		$app = JFactory::getApplication('administrator');
        
        //$app->enqueueMessage(JText::_('sportsmanagementModelsmquotes populateState context<br><pre>'.print_r($this->context,true).'</pre>'   ),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('objcountry.name', 'asc');
	}
    
	/**
	 * sportsmanagementModelextrafields::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
		$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');

        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		// Select some fields
		$query->select(implode(",",$this->filter_fields));
		// From the hello table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_user_extra_fields AS objcountry');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = objcountry.checked_out');
		
        if ($this->getState('filter.search'))
		{
        $query->where('LOWER(objcountry.name) LIKE '.$this->_db->Quote('%'.$this->getState('filter.search').'%'));
        }
		
        $query->order($db->escape($this->getState('list.ordering', 'objcountry.name')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text .= ' <br><pre>'.print_r($query->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
        
        return $query;
	}

/**
 * sportsmanagementModelextrafields::getExtraFieldsProject()
 * 
 * @param integer $project_id
 * @return
 */
function getExtraFieldsProject($project_id=0)
{
$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $result = '';

        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
        $query->select('ef.name');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_user_extra_fields_values as ev ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_user_extra_fields as ef ON ef.id = ev.field_id');
        $query->where('ev.jl_id = '.$project_id);
        $query->where('ef.template_backend LIKE '.$db->Quote(''.'project'.''));
        $query->where('ev.fieldvalue != '.$db->Quote(''.'')); 
        
        $db->setQuery($query);
        //$result = $db->loadObjectList();
        $column = $db->loadColumn(0);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($result,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($column,true).'</pre>'),'Notice');
        
        if ( $column )
        {
            $result = implode('<br>', $column);
        }
        return $result;   
}

/**
 * sportsmanagementModelextrafields::getExtraFields()
 * 
 * @param string $template_backend
 * @param string $template_frontend
 * @return
 */
function getExtraFields($template_backend = '', $template_frontend = '')
    {
        $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
        // Select some fields
		$query->select('id,name');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_user_extra_fields');
        if ($template_backend)
		{
        $query->where('template_backend LIKE '.$db->Quote(''.$template_backend.''));
        }
        if ($template_frontend)
		{
        $query->where('template_frontend LIKE '.$db->Quote(''.$template_frontend.''));
        }
        $query->order('name ASC');

        $db->setQuery($query);
        $result = $db->loadObjectList();
        return $result;
}        



	
}
?>