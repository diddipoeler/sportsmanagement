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


defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');



/**
 * sportsmanagementModelhitlist
 * 
 * @package 
 * @author diddi
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class sportsmanagementModelhitlist extends JModelList
{

var $_identifier = "hitlist";
	var $limitstart = 0;
    var $limit = 0;
    static $cfg_which_database = 0;	
    static $projectid = 0;
    static $_success_text = NULL;
	
	/**
	 * sportsmanagementModelhitlist::__construct()
	 * 
	 * @param mixed $config
	 * @return void
	 */
	public function __construct($config = array())
        {   
            // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        self::$projectid = $jinput->getInt( "p", 0 );
        
        sportsmanagementModelProject::$projectid = self::$projectid;
        self::$cfg_which_database = $jinput->getInt('cfg_which_database',0);
            
                parent::__construct($config);
        }
        
        
        /**
         * sportsmanagementModelhitlist::getSportsmanagementHits()
         * 
         * @param mixed $config
         * @param integer $max_hits
         * @param string $table
         * @return void
         */
        public function getSportsmanagementHits($config=array(),$max_hits=0, $table='project')
        {
            // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
       $option = $jinput->getCmd('option');
       // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        
        switch ($table)
        {
            case 'person':
            $query->select('CONCAT_WS(\' - \',firstname,lastname) AS name,hits');
            break; 
            default:
            $query->select('name,hits');
            break; 
        }
        
        $query->from('#__sportsmanagement_'.$table);
        $query->where('hits != 0 ');
        $query->order('hits DESC');
        
		$db->setQuery($query,0,$max_hits);
		$db->query();
        $rows = $db->loadObjectList();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' rows<br><pre>'.print_r($rows,true).'</pre>'),'');
        
        self::$_success_text[JText::_('COM_SPORTSMANAGEMENT_HITLIST_'.strtoupper($table))] = $rows;
        
        //return $rows;
            
        }

    
}

?>    