<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

/**
 * sportsmanagementControllerjoomleagueimports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerjoomleagueimports extends JControllerAdmin
{



/**
 * sportsmanagementControllerjoomleagueimports::importjoomleaguenew()
 * 
 * @return void
 */
function importjoomleaguenew()
{
        $app = JFactory::getApplication();
        //$option = JRequest::getCmd('option');
        $model	= $this->getModel();
        $result = $model->importjoomleaguenew();
        $app->setUserState( $this->option.".jl_table_import_success", $result );
        
//        $model::$_success = $result;
        $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));

}

  
///**
// * sportsmanagementControllerjoomleagueimports::updateplayerproposition()
// * 
// * @return void
// */
//function updateplayerproposition()
//{
//    $app = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        $model	= $this->getModel();  
//        $model->updateplayerproposition();
//    $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view=joomleagueimport&layout=positions'  , false));
//}

///**
// * sportsmanagementControllerjoomleagueimports::updatestaffproposition()
// * 
// * @return void
// */
//function updatestaffproposition()
//{
//    $app = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        $model	= $this->getModel();  
//        $model->updatestaffproposition();
//    $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view=joomleagueimport&layout=positions'  , false));
//}


///**
// * sportsmanagementControllerjoomleagueimports::updatepositions()
// * 
// * @return void
// */
//function updatepositions()
//{
//    $app = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        $model	= $this->getModel();  
//        $model->updatepositions();
//    $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view=joomleagueimport&layout=positions'  , false));
//}

  
//  /**
//   * sportsmanagementControllerjoomleagueimports::positions()
//   * 
//   * @return void
//   */
//  function positions()
//  {
//  $app = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        $model	= $this->getModel();  
//    $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view=joomleagueimport&layout=positions'  , false));
//  }
  
//  /**
//   * sportsmanagementControllerjoomleagueimports::checkimport()
//   * 
//   * @return void
//   */
//  function checkimport()
//    {
//        $app = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        $model	= $this->getModel();
//        //$post = JRequest::get('post');
//        //JRequest::setVar('post', $post,'post');
//        
//        if ( $model->checkimport() )
//        {
//            $totals = $model->gettotals();
//            $app->setUserState( "$option.step", 0);
//            $app->setUserState( "$option.totals", $totals);  
//        $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view=joomleagueimport&task=joomleagueimport.newstructur&tmpl=component'  , false));    
//        }
//        else
//        {
//        $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));    
//        }
//        
//    }    


//  /**
//   * sportsmanagementControllerjoomleagueimports::import()
//   * 
//   * @return void
//   */
//  function import()
//    {
//        $app = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        $model	= $this->getModel();
//        $result = $model->import();
//        
//        $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
//
//}

///**
// * sportsmanagementControllerjoomleagueimports::newstructur()
// * 
// * @return void
// */
//function newstructur()
//{
//    $app = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        $model	= $this->getModel();
//        $result = $model->newstructur();
//        
//        $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
//    
//}

	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'joomleagueimports', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}

?>