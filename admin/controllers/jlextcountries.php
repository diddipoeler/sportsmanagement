<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jlextcountries.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
 
/**
 * sportsmanagementControllerjlextcountries
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerjlextcountries extends JSMControllerAdmin
{
  
  
  /**
   * sportsmanagementControllerjlextcountries::importplz()
   * 
   * @return void
   */
  function importplz()
    {
    $model = $this->getModel();
       $model->importplz();
       $this->setRedirect(Route::_('index.php?option='.$this->option.'&view='.$this->view_list, false));    
    }    
    
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'Jlextcountry', $prefix = 'sportsmanagementModel',$config = array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}