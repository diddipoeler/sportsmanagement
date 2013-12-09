<?PHP

class sportsmanagementControllerajaxcalls extends JControllerLegacy
{

function removeCommentary()
    {
        
        //$response = self::getAjaxResponse();
        //$result = $response;
        
        // Check for request forgeries
        //JRequest::checkToken('get') or jexit('JINVALID_TOKEN');
		//JSession::checkToken() or die('JINVALID_TOKEN');
        JRequest::checkToken() or jexit('JINVALID_TOKEN');
        
        // Check for request forgeries
        //JRequest::checkToken( 'get' ) or jexit( 'JINVALID_TOKEN' );
        
 //       if (!JSession::checkToken('post')) 
//        {
			//$result='0'.'&'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY').': '.JText::_('JINVALID_TOKEN');
		//echo json_encode($result);
        //}
//else
//{
		$event_id = JRequest::getInt('event_id');
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