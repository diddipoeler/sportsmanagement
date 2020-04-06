<?PHP
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       ajaxcalls.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage controllers
 */

 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text; 
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

/**
 * sportsmanagementControllerajaxcalls
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerajaxcalls extends BaseController
{

    /**
 * sportsmanagementControllerajaxcalls::removeCommentary()
 * 
 * @return void
 */
    function removeCommentary()
    {
        
        //$response = self::getAjaxResponse();
        //$result = $response;
        
        // Check for request forgeries
        Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));
        
        // Check for request forgeries
        //Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));
        
         //       if (!Session::checkToken('post')) 
        //        {
           //$result='0'.'&'.Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY').': '.Text::_('JINVALID_TOKEN');
          //echo json_encode($result);
        //}
        //else
        //{
          $event_id = Factory::getApplication()->input->getInt('event_id');
          $model = $this->getModel();
        if (!$result = $model->deletecommentary($event_id)) {
            $result='0'.'&'.Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY').': '.$model->getError();
        }
        else
          {
            $result='1'.'&'.Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_COMMENTARY').'&'.$event_id;
        }
          echo json_encode($result);
         //}       
            // Close the application
          Factory::getApplication()->close();
        
            //jexit();
          //Factory::getApplication()->close();
    }
        
    
}    


?>
