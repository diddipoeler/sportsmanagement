<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      hitlist.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * sportsmanagementModelhitlist
 * 
 * @package 
 * @author diddi
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class sportsmanagementModelhitlist extends ListModel
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
        $app = Factory::getApplication();
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
        $app = Factory::getApplication();
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
       
        self::$_success_text[Text::_('COM_SPORTSMANAGEMENT_HITLIST_'.strtoupper($table))] = $rows;
           
        }

    
}

?>    