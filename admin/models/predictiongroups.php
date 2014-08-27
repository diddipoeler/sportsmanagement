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

jimport( 'joomla.application.component.modellist' );



/**
 * sportsmanagementModelpredictiongroups
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelpredictiongroups extends JModelList
{
	var $_identifier = "predictiongroups";
	
    /**
     * sportsmanagementModelpredictiongroups::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        's.name',
                        's.ordering',
                        's.id'
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
		$app = JFactory::getApplication('administrator');
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelsmquotes populateState context<br><pre>'.print_r($this->context,true).'</pre>'   ),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.prediction_id', 'filter_prediction_id', '');
        $this->setState('filter.prediction_id', $temp_user_request);

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('s.name', 'asc');
	}
    
	/**
	 * sportsmanagementModelpredictiongroups::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
        $search	= $this->getState('filter.search');
        
        $query->select(array('s.*', 'u.name AS editor'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_groups AS s')
        ->join('LEFT', '#__users AS u ON u.id = s.checked_out');
        
        if ($search)
		{
        $query->where('(LOWER(s.name) LIKE ' . $db->Quote( '%' . $search . '%' ) );
        }
        
         $query->order($db->escape($this->getState('list.ordering', 's.name')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
 
//$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');

		
		return $query;
	}




	
}
?>