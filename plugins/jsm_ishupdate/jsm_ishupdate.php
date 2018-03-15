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

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
if (! defined('JSM_PATH'))
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}
require_once(JPATH_SITE.DS.JSM_PATH.DS.'extensions'.DS.'jsminlinehockey'.DS.'admin'.DS.'models'.DS.'jsminlinehockey.php');

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');




/**
 * PlgSystemjsm_ishupdate
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class PlgSystemjsm_ishupdate extends JPlugin
{

    var $config;
static $aufruf = 0;
static $matchestoupdate = 0;
static $matchesafterupdate = 0;
static $datstring = '';
static $projectid = 0;
static $match_timestamp = 0;
static $linkresult = '';
    static $classname = 'sportsmanagementHelper';
    
    /**
     * PlgSystemjsm_ishupdate::__construct()
     * 
     * @param mixed $subject
     * @param mixed $params
     * @return void
     */
    public function __construct(&$subject, $params)
    {
        parent::__construct($subject, $params);
        $app = JFactory::getApplication();
    
    //self::$projectid = $app->input->getInt('p');
    //self::$projectid = JRequest::getInt('p',0);
    
    $date = time();    // aktuelles Datum 
    //echo date('d.m.Y h:i:s', $date) . ""; 
    $stunden = 4;   // z.B. ein Tag 
    $enddatum = $date + ($stunden * 60 * 60);  // Ein Tag sp?ter (stunden * minuten * sekunden) 
    self::$datstring = date('Y-m-d H:i:s', $enddatum) ; // Datum im DB-Format   
    
    if (!class_exists(self::$classname))
        {
        $file = JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php';
        if (file_exists($file))
        {
        require_once($file);
        }
        }
    self::$match_timestamp = sportsmanagementHelper::getTimestamp(self::$datstring);      
    
    if ( $this->params->get('load_debug', 1) )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'projectid<br><pre>'.print_r(self::$projectid,true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params<br><pre>'.print_r($params,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' app<br><pre>'.print_r($app,true).'</pre>'),'Notice');
        }

    
    }

   
    /**
     * PlgSystemjsm_ishupdate::onBeforeRender()
     * 
     * @return void
     */
    public function onBeforeRender()
    {
        $app = JFactory::getApplication();
        if ( $this->params->get('load_debug', 1) )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'projectid<br><pre>'.print_r(self::$projectid,true).'</pre>'),'Notice');
        }
        
        if ( self::$projectid )
        {
//        $actionsModel = JModelLegacy::getInstance('jsminlinehockey', 'sportsmanagementModel');    
//        $actionsModel->getmatches(self::$projectid);
        $this->getmatchestoupdate(2);
        }
        
    }

    
    /**
     * PlgSystemjsm_ishupdate::onAfterRender()
     * 
     * @return void
     */
    public function onAfterRender()
    {
        $app = JFactory::getApplication();
        if ( $this->params->get('load_debug', 1) )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'projectid<br><pre>'.print_r(self::$projectid,true).'</pre>'),'Notice');
        }
        
    }

   
    /**
     * PlgSystemjsm_ishupdate::onAfterRoute()
     * 
     * @return void
     */
    public function onAfterRoute()
    {
        $app = JFactory::getApplication();
        self::$projectid = JRequest::getInt('p',0);
        if ( $this->params->get('load_debug', 1) )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'projectid<br><pre>'.print_r(self::$projectid,true).'</pre>'),'Notice');
        }
        
        if ( self::$projectid )
        {
       $this->getmatchestoupdate(1);
		if ( self::$matchestoupdate )
		{
       $actionsModel = JModelLegacy::getInstance('jsminlinehockey', 'sportsmanagementModel');    
        $actionsModel->getmatches(self::$projectid);
	}
        }
        
    }

    
    /**
     * PlgSystemjsm_ishupdate::onAfterDispatch()
     * 
     * @return void
     */
    public function onAfterDispatch()
    {
        $app = JFactory::getApplication();
        if ( $this->params->get('load_debug', 1) )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'projectid<br><pre>'.print_r(self::$projectid,true).'</pre>'),'Notice');
        }

    }

    
    /**
     * PlgSystemjsm_ishupdate::onAfterInitialise()
     * 
     * @return void
     */
    public function onAfterInitialise()
    {
        $app = JFactory::getApplication();
        if ( $this->params->get('load_debug', 1) )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'projectid<br><pre>'.print_r(self::$projectid,true).'</pre>'),'Notice');
        }
        
        
    }
    
    
    
    /**
     * PlgSystemjsm_ishupdate::getmatchestoupdate()
     * 
     * @return void
     */
    public function getmatchestoupdate($step=1)
    {
    $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true); 
        if ( $this->params->get('load_debug', 1) )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'projectid<br><pre>'.print_r(self::$projectid,true).'</pre>'),'Notice');
        }
        $query->clear(); 
        $query->select('count(*) AS count');
        $query->from('#__sportsmanagement_match AS m ');
        $query->join('INNER','#__sportsmanagement_round AS r on r.id = m.round_id ');
        $query->join('INNER','#__sportsmanagement_project AS p on p.id = r.project_id ');
        $query->where('p.id ='. self::$projectid);
		$query->where('m.team1_result IS NULL ');
        
        //$query->where('m.match_date < '. $db->Quote(''.self::$datstring.'') );
        $query->where('m.match_timestamp < '. self::$match_timestamp );
        
        try {
        $db->setQuery($query);
        $result = $db->loadResult();
        } catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    if ( $this->params->get('load_debug', 1) )
        {
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
    }
}
    
    switch ($step)
    {
        case 1:
        self::$matchestoupdate = $result;
        $app->enqueueMessage(JText::sprintf('Es müssen [ %1$s ] Spiele aktualisiert werden !',"<strong>".self::$matchestoupdate."</strong>"),'Notice');
        break;
        case 2:
        self::$matchesafterupdate = $result;
        $dif = self::$matchestoupdate - self::$matchesafterupdate;
        $app->enqueueMessage(JText::sprintf('Es wurden [ %1$s ] Spiele aktualisiert !',"<strong>".$dif."</strong>"),'Notice');
        break;
    }
    
        
    }
    

}

?>
