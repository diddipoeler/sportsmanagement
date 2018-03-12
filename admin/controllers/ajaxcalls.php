<?PHP
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      ajaxcalls.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

/**
 * sportsmanagementControllerajaxcalls
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerajaxcalls extends JControllerLegacy
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
        JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));
        
        // Check for request forgeries
        //JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));
        
 //       if (!JSession::checkToken('post')) 
//        {
			//$result='0'.'&'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY').': '.JText::_('JINVALID_TOKEN');
		//echo json_encode($result);
        //}
//else
//{
		$event_id = JFactory::getApplication()->input->getInt('event_id');
		$model = $this->getModel();
		if (!$result = $model->deletecommentary($event_id))
		{
			$result='0'.'&'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY').': '.$model->getError();
		}
		else
		{
			$result='1'.'&'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_COMMENTARY').'&'.$event_id;
		}
		echo json_encode($result);
 //}       
        // Close the application
		JFactory::getApplication()->close();
        
        //jexit();
		//JFactory::getApplication()->close();
    }
        
    
}    


?>