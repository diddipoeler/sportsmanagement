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
defined('_JEXEC') or die('Restricted access');


jimport('joomla.application.component.modellist');


/**
 * sportsmanagementModelrosterpositions
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelrosterpositions extends JModelList
{
	var $_identifier = "rosterpositions";
	
    /**
     * sportsmanagementModelrosterpositions::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'obj.name',
                        'obj.country',
                        'obj.alias',
                        'obj.id',
                        'obj.ordering'
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
        $option = JFactory::getApplication()->input->getCmd('option');
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
		parent::populateState('obj.name', 'asc');
	}
    
        
	protected function getListQuery()
	{
		$app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $search	= $this->getState('filter.search');
        
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		// Select some fields
		$query->select('obj.*');
		// From the hello table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_rosterposition as obj');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
        if ($search)
		{
        $query->where('LOWER(obj.name) LIKE '.$db->Quote('%'.$search.'%'));
        }

 $query->order($db->escape($this->getState('list.ordering', 'obj.name')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
 
//$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');

        return $query;
        
        
        
	}




    
    function getRosterHome()
    {
        $bildpositionenhome = array();
$bildpositionenhome['HOME_POS'][0]['heim']['oben'] = 5;
$bildpositionenhome['HOME_POS'][0]['heim']['links'] = 233;
$bildpositionenhome['HOME_POS'][1]['heim']['oben'] = 113;
$bildpositionenhome['HOME_POS'][1]['heim']['links'] = 69;
$bildpositionenhome['HOME_POS'][2]['heim']['oben'] = 113;
$bildpositionenhome['HOME_POS'][2]['heim']['links'] = 179;
$bildpositionenhome['HOME_POS'][3]['heim']['oben'] = 113;
$bildpositionenhome['HOME_POS'][3]['heim']['links'] = 288;
$bildpositionenhome['HOME_POS'][4]['heim']['oben'] = 113;
$bildpositionenhome['HOME_POS'][4]['heim']['links'] = 397;
$bildpositionenhome['HOME_POS'][5]['heim']['oben'] = 236;
$bildpositionenhome['HOME_POS'][5]['heim']['links'] = 179;
$bildpositionenhome['HOME_POS'][6]['heim']['oben'] = 236;
$bildpositionenhome['HOME_POS'][6]['heim']['links'] = 288;
$bildpositionenhome['HOME_POS'][7]['heim']['oben'] = 318;
$bildpositionenhome['HOME_POS'][7]['heim']['links'] = 69;
$bildpositionenhome['HOME_POS'][8]['heim']['oben'] = 318;
$bildpositionenhome['HOME_POS'][8]['heim']['links'] = 233;
$bildpositionenhome['HOME_POS'][9]['heim']['oben'] = 318;
$bildpositionenhome['HOME_POS'][9]['heim']['links'] = 397;
$bildpositionenhome['HOME_POS'][10]['heim']['oben'] = 400;
$bildpositionenhome['HOME_POS'][10]['heim']['links'] = 233;
        return $bildpositionenhome;
    }
    
    function getRosterAway()
    {
        $bildpositionenaway = array();
$bildpositionenaway['AWAY_POS'][0]['heim']['oben'] = 970;
$bildpositionenaway['AWAY_POS'][0]['heim']['links'] = 233;
$bildpositionenaway['AWAY_POS'][1]['heim']['oben'] = 828;
$bildpositionenaway['AWAY_POS'][1]['heim']['links'] = 69;
$bildpositionenaway['AWAY_POS'][2]['heim']['oben'] = 828;
$bildpositionenaway['AWAY_POS'][2]['heim']['links'] = 179;
$bildpositionenaway['AWAY_POS'][3]['heim']['oben'] = 828;
$bildpositionenaway['AWAY_POS'][3]['heim']['links'] = 288;
$bildpositionenaway['AWAY_POS'][4]['heim']['oben'] = 828;
$bildpositionenaway['AWAY_POS'][4]['heim']['links'] = 397;
$bildpositionenaway['AWAY_POS'][5]['heim']['oben'] = 746;
$bildpositionenaway['AWAY_POS'][5]['heim']['links'] = 179;
$bildpositionenaway['AWAY_POS'][6]['heim']['oben'] = 746;
$bildpositionenaway['AWAY_POS'][6]['heim']['links'] = 288;
$bildpositionenaway['AWAY_POS'][7]['heim']['oben'] = 664;
$bildpositionenaway['AWAY_POS'][7]['heim']['links'] = 69;
$bildpositionenaway['AWAY_POS'][8]['heim']['oben'] = 664;
$bildpositionenaway['AWAY_POS'][8]['heim']['links'] = 397;
$bildpositionenaway['AWAY_POS'][9]['heim']['oben'] = 587;
$bildpositionenaway['AWAY_POS'][9]['heim']['links'] = 179;
$bildpositionenaway['AWAY_POS'][10]['heim']['oben'] = 587;
$bildpositionenaway['AWAY_POS'][10]['heim']['links'] = 288;
return $bildpositionenaway;
    }
    

	
}
?>