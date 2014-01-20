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

/**
 * System plugin
 * 1) onBeforeRender()
 * 2) onAfterRender()
 * 3) onAfterRoute()
 * 4) onAfterDispatch()
 * These events are triggered in 'JAdministrator' class in file 'application.php' at location
 * 'Joomla_base\administrator\includes'.
 * 5) onAfterInitialise()
 * This event is triggered in 'JApplication' class in file 'application.php' at location
 * 'Joomla_base\libraries\joomla\application'.
 */


// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

class PlgSystemjsm_siscron extends JPlugin
{


	public function PlgSystemjsm_siscron(&$subject, $params)
	{
		parent::__construct($subject, $params);
		// load language file for frontend
		JPlugin::loadLanguage( 'plg_jsm_siscron', JPATH_ADMINISTRATOR );
	}
    
    
	public function onBeforeRender()
	{
		$db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $projectid = JRequest::getInt('p',0);
        
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'   ),'');
        
        $params = JComponentHelper::getParams( 'com_sportsmanagement' );
        $sis_xmllink = $params->get( 'sis_xmllink' );
        $sis_nummer	= $params->get( 'sis_meinevereinsnummer' );
        $sis_passwort = $params->get( 'sis_meinvereinspasswort' );
        switch ($sis_xmllink)
        {
        case 'http://www.sis-handball.de':
        $country = 'DEU';
        break;
        case 'http://www.sis-handball.at':
        $country = 'AUT';
        break;
        }
        
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' sis_xmllink<br><pre>'.print_r($sis_xmllink,true).'</pre>'   ),'');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' sis_meinevereinsnummer<br><pre>'.print_r($sis_nummer,true).'</pre>'   ),'');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' sis_meinvereinspasswort<br><pre>'.print_r($sis_passwort,true).'</pre>'   ),'');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' country<br><pre>'.print_r($country,true).'</pre>'   ),'');
        
        $query = $db->getQuery(true);
        $query->select('staffel_id');    
        $query->from('#__sportsmanagement_project'); 
        $query->where('id = '.$projectid); 
        $db->setQuery($query);
		$staffel_id = $db->loadResult();
        $teamart = substr( $staffel_id , 17, 4);
        
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' staffel_id<br><pre>'.print_r($staffel_id,true).'</pre>'   ),'');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teamart<br><pre>'.print_r($teamart,true).'</pre>'   ),'');
        
        
	}
    
    public function onAfterRender()
	{
		
//        $app = JFactory::getApplication();
//        $projectid = JRequest::getInt('p',0);
//        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'   ),'');
	}
    
    public function onAfterRoute()
	{
		
//        $app = JFactory::getApplication();
//        $projectid = JRequest::getInt('p',0);
//        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'   ),'');
	}
    
    public function onAfterDispatch()
	{
		
//        $app = JFactory::getApplication();
//        $projectid = JRequest::getInt('p',0);
//        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'   ),'');
	}
    
    public function onAfterInitialise()
	{
		
//        $app = JFactory::getApplication();
//        $projectid = JRequest::getInt('p',0);
//        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'   ),'');
	}
    
    
    
    
    

}    

?>