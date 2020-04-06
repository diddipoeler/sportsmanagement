<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       smimageimports.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage controllers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
 
/**
 * sportsmanagementControllersmimageimports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllersmimageimports extends JSMControllerAdmin
{
  
    /**
   * sportsmanagementControllersmimageimports::import()
   * 
   * @return void
   */
    function import()
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $model    = $this->getModel();
        $result = $model->import();
        
        $this->setRedirect(Route::_('index.php?option='.$this->option.'&view='.$this->view_list, false));

    }

    /**
     * Proxy for getModel.
     *
     * @since 1.6
     */
    public function getModel($name = 'smimageimport', $prefix = 'sportsmanagementModel', $config = Array() ) 
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }
}
