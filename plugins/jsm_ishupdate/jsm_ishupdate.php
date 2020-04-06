<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       jsm_ishupdate.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage plugins
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

defined('_JEXEC') or die();
use Joomla\CMS\Factory;

//if (!defined('DS')) {
//    define('DS', DIRECTORY_SEPARATOR);
//}
if (! defined('JSM_PATH')) {
    DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}
require_once JPATH_SITE.DIRECTORY_SEPARATOR.JSM_PATH.DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.'jsminlinehockey'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'jsminlinehockey.php';

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');




/**
 * PlgSystemjsm_ishupdate
 * 
 * @package 
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
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
     * @param  mixed $subject
     * @param  mixed $params
     * @return void
     */
    public function __construct(&$subject, $params)
    {
        parent::__construct($subject, $params);
        $app = Factory::getApplication();
   
        $date = time();    // aktuelles Datum 
        //echo date('d.m.Y h:i:s', $date) . ""; 
        $stunden = 4;   // z.B. ein Tag 
        $enddatum = $date + ($stunden * 60 * 60);  // Ein Tag sp?ter (stunden * minuten * sekunden) 
        self::$datstring = date('Y-m-d H:i:s', $enddatum); // Datum im DB-Format   
    
        if (!class_exists(self::$classname)) {
            $file = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.JSM_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'sportsmanagement.php';
            if (file_exists($file)) {
                include_once $file;
            }
        }
        self::$match_timestamp = sportsmanagementHelper::getTimestamp(self::$datstring);      
    
        if ($this->params->get('load_debug', 1) ) {

        }

    
    }

   
    /**
     * PlgSystemjsm_ishupdate::onBeforeRender()
     * 
     * @return void
     */
    public function onBeforeRender()
    {
        $app = Factory::getApplication();
        if ($this->params->get('load_debug', 1) ) {

        }
        
        if (self::$projectid ) {
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
        $app = Factory::getApplication();
        if ($this->params->get('load_debug', 1) ) {

        }
        
    }

   
    /**
     * PlgSystemjsm_ishupdate::onAfterRoute()
     * 
     * @return void
     */
    public function onAfterRoute()
    {
        $app = Factory::getApplication();
        self::$projectid = Factory::getApplication()->input->getInt('p', 0);
        if ($this->params->get('load_debug', 1) ) {

        }
        
        if (self::$projectid ) {
            $this->getmatchestoupdate(1);
            if (self::$matchestoupdate ) {
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
        $app = Factory::getApplication();
        if ($this->params->get('load_debug', 1) ) {

        }

    }

    
    /**
     * PlgSystemjsm_ishupdate::onAfterInitialise()
     * 
     * @return void
     */
    public function onAfterInitialise()
    {
        $app = Factory::getApplication();
        if ($this->params->get('load_debug', 1) ) {

        }
        
        
    }
    
    
    
    /**
     * PlgSystemjsm_ishupdate::getmatchestoupdate()
     * 
     * @return void
     */
    public function getmatchestoupdate($step=1)
    {
        $app = Factory::getApplication();
        $db = Factory::getDBO();
        $query = $db->getQuery(true); 
        if ($this->params->get('load_debug', 1) ) {

        }
        $query->clear(); 
        $query->select('count(*) AS count');
        $query->from('#__sportsmanagement_match AS m ');
        $query->join('INNER', '#__sportsmanagement_round AS r on r.id = m.round_id ');
        $query->join('INNER', '#__sportsmanagement_project AS p on p.id = r.project_id ');
        $query->where('p.id ='. self::$projectid);
        $query->where('m.team1_result IS NULL ');
        
        //$query->where('m.match_date < '. $db->Quote(''.self::$datstring.'') );
        $query->where('m.match_timestamp < '. self::$match_timestamp);
        
        try {
            $db->setQuery($query);
            $result = $db->loadResult();
        } catch (Exception $e) {
            $msg = $e->getMessage(); // Returns "Normally you would have other code...
            $code = $e->getCode(); // Returns '500';
            if ($this->params->get('load_debug', 1) ) {
                    Factory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
            }
        }
    
        switch ($step)
        {
        case 1:
            self::$matchestoupdate = $result;
            $app->enqueueMessage(JText::sprintf('Es müssen [ %1$s ] Spiele aktualisiert werden !', "<strong>".self::$matchestoupdate."</strong>"), 'Notice');
            break;
        case 2:
            self::$matchesafterupdate = $result;
            $dif = self::$matchestoupdate - self::$matchesafterupdate;
            $app->enqueueMessage(JText::sprintf('Es wurden [ %1$s ] Spiele aktualisiert !', "<strong>".$dif."</strong>"), 'Notice');
            break;
        }
    
        
    }
    

}

?>
