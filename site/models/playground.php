<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      playground.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @subpackage playground
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelPlayground
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPlayground extends BaseDatabaseModel
{
    static $playgroundid = 0;
    var $playground = null;
    static $projectid = 0;
    
    static $cfg_which_database = 0;

    /**
     * sportsmanagementModelPlayground::__construct()
     * 
     * @return
     */
    function __construct( )
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;

        self::$projectid = $jinput->getInt( "p", 0 );
        self::$playgroundid = $jinput->getInt( "pgid", 0 );
        sportsmanagementModelProject::$projectid = self::$projectid;
        self::$cfg_which_database = $jinput->getInt('cfg_which_database',0);
        
        parent::__construct( ); 
    }

}
?>
