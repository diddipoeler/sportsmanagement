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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * sportsmanagementModelseason
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelseason extends JSMModelAdmin
{

	/**
	 * Override parent constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	
//    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($config,true).'</pre>'),'');
//    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getName<br><pre>'.print_r($this->getName(),true).'</pre>'),'');
    
	}	
    
    /**
     * sportsmanagementModelseason::saveshortpersons()
     * 
     * @return void
     */
    function saveshortpersons()
    {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db = sportsmanagementHelper::getDBConnection();
        
        $date = JFactory::getDate();
	   $user = JFactory::getUser();
       $modified = $date->toSql();
	   $modified_by = $user->get('id');
       
        //$post = JFactory::getApplication()->input->post->getArray(array());
        //$post = $jinput->post;
        $pks = $jinput->getVar('cid', null, 'post', 'array');
        $teams = $jinput->getVar('team_id', null, 'post', 'array');
        $season_id = $jinput->getVar('season_id', 0, 'post', 'array');
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' pks<br><pre>'.print_r($pks, true).'</pre><br>','');
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($teams, true).'</pre><br>','');
        
        foreach ( $pks as $key => $value )
        {
        // Create a new query object.
        $query = $db->getQuery(true);
        // Insert columns.
        $columns = array('person_id','season_id','modified','modified_by');
        // Insert values.
        $values = array($value,$season_id,$db->Quote(''.$modified.''),$modified_by);
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query);

		if (!$db->execute())
		{
		  $app->enqueueMessage(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(), true).'</pre><br>','Error');
		} 
        
        if ( isset($teams[$value]) )
        {
        $query->clear();
        // Insert columns.
        $columns = array('person_id','season_id','team_id','published','persontype','modified','modified_by'   );
        // Insert values.
        $values = array($value,$season_id,$teams[$value],'1','1',$db->Quote(''.$modified.''),$modified_by);
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query);

		if (!$db->execute())
		{
		  $app->enqueueMessage(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(), true).'</pre><br>','Error');
		}
        
        }
             
        }
        
    }
    
    /**
     * sportsmanagementModelseason::saveshortteams()
     * 
     * @return void
     */
    function saveshortteams()
    {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db = sportsmanagementHelper::getDBConnection();
        //$post = JFactory::getApplication()->input->post->getArray(array());
        //$post = $jinput->post;
        $pks = $jinput->getVar('cid', null, 'post', 'array');
        $season_id = $jinput->getVar('season_id', 0, 'post', 'array');
        
//        $app->enqueueMessage(__METHOD__.' '.__LINE__.' season_id<br><pre>'.print_r($season_id, true).'</pre><br>','');
//        $app->enqueueMessage(__METHOD__.' '.__LINE__.' pks<br><pre>'.print_r($pks, true).'</pre><br>','');
//        $app->enqueueMessage(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post, true).'</pre><br>','');
        
        foreach ( $pks as $key => $value )
        {
            // Create a new query object.
        $query = $db->getQuery(true);
        // Insert columns.
        $columns = array('team_id','season_id');
        // Insert values.
        $values = array($value,$season_id);
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query);

		if (!$db->execute())
		{
		  $app->enqueueMessage(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(), true).'</pre><br>','Error');
		}  
        
        }
        
    }
	
}
