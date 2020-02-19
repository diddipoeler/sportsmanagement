<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jlxmlexports.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage controllers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
 
/**
 * sportsmanagementControllerjlxmlexports
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerjlxmlexports extends JSMControllerAdmin
{

  
  /**
   * sportsmanagementControllerjlxmlexports::export()
   * 
   * @return void
   */
  public function export()
    {
    // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;    
        $project_id = $jinput->getVar('pid');
      $model = $this->getModel();
       $model->exportData();  
      $this->setRedirect(Route::_('index.php?option='.$this->option.'&view='.$this->view_list.'&pid='.$project_id, false));  
        
    } 

    
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'jlxmlexports', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}