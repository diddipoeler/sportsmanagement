<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       rosterpositions.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage controllers
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
 
/**
 * sportsmanagementControllerrosterpositions
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerrosterpositions extends JSMControllerAdmin
{

    /**
 * sportsmanagementControllerrosterpositions::addhome()
 * 
 * @return void
 */
    function addhome()
    {
        $this->setRedirect(Route::_('index.php?option='.$this->option.'&view=rosterposition&addposition=HOME_POS&layout=edit', false));    
    }

    /**
 * sportsmanagementControllerrosterpositions::addaway()
 * 
 * @return void
 */
    function addaway()
    {
        $this->setRedirect(Route::_('index.php?option='.$this->option.'&view=rosterposition&addposition=AWAY_POS&layout=edit', false));    
    }

  
    /**
     * Proxy for getModel.
     *
     * @since 1.6
     */
    public function getModel($name = 'rosterposition', $prefix = 'sportsmanagementModel', $config = Array() ) 
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }
}
