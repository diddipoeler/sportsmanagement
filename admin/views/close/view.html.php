<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage close
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * This view is displayed after successfull saving of config data.
 * Use it to show a message informing about success or simply close a modal window.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_config
 */
class sportsmanagementViewClose extends sportsmanagementView
{
	
    /**
	 * Display the view
	 */
	function display($tpl = null)
	{
	   // Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsminfo = $this->jsmjinput->getCmd('info');
        $this->jsmdocument = JFactory::getDocument();
//        $this->jsmuser = JFactory::getUser(); 
//        $this->jsmdate = JFactory::getDate();


//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' isAdmin<br><pre>'.print_r($this->jsmapp->isAdmin(),true).'</pre>'),'');    
  
		// close a modal window
        JFactory::getDocument()->addScriptDeclaration('
        window.parent.location.href=window.parent.location.href;
			window.parent.SqueezeBox.close();
		// available msg types: success, error, notice
var msg = {
    error: [\'it is an error!<br />\', \'it is enother error!\'],
    success: [\'It works!!\']
};
Joomla.renderMessages( msg );
            
		');
        /*
		JFactory::getDocument()->addScriptDeclaration('
			window.parent.location.href=window.parent.location.href;
			window.parent.SqueezeBox.close();
            Joomla.renderMessages({\'info\': jmsgs });
		');
        */
        switch($this->jsminfo)
        {
            case 'truncate':
            $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' isAdmin<br><pre>'.print_r($this->jsmapp->isAdmin(),true).'</pre>'),'');
            break;
        }
        
        
	}
}
