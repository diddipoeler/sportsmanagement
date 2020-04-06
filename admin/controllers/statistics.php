<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       statistics.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage controllers
 */


defined('_JEXEC') or die('Restricted access');
 
/**
 * sportsmanagementControllerstatistics
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerstatistics extends JSMControllerAdmin
{
  
    /**
     * Proxy for getModel.
     *
     * @since 1.6
     */
    public function getModel($name = 'statistic', $prefix = 'sportsmanagementModel', $config = Array() ) 
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }
}
