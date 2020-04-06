<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       smquotes.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage controllers
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
 
/**
 * sportsmanagementControllersmquotes
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllersmquotes extends JSMControllerAdmin
{
  
    /**
 * sportsmanagementControllersmquotes::edittxt()
 * 
 * @return void
 */
    function edittxt()
    {
        $this->setRedirect(Route::_('index.php?option='.$this->option.'&view=smquotestxt', false));    
    
    }  
    /**
     * Proxy for getModel.
     *
     * @since 1.6
     */
    public function getModel($name = 'smquote', $prefix = 'sportsmanagementModel', $config = Array() ) 
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }
}
