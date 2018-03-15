<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      jsmgcalendarimport.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jsmgcalendar
 */

defined('_JEXEC') or die();

/**
 * sportsmanagementControllerjsmgcalendarImport
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerjsmgcalendarImport extends JControllerLegacy 
{

/**
	 * Class Constructor
	 *
	 * @param	array	$config		An optional associative array of configuration settings.
	 * @return	void
	 * @since	1.5
	 */
	function __construct($config = array())
	{
	   // Initialise variables.
		$app = JFactory::getApplication();
		parent::__construct($config);

	//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTask<br><pre>'.print_r($this->getTask(),true).'</pre>'),'Notice');
	}
    
	/**
	 * sportsmanagementControllerjsmgcalendarImport::import()
	 * 
	 * @return void
	 */
	public function import() 
    {

$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        $model = $this->getModel('jsmgcalendarImport');
        $result = $model->import();
        
        if ( $result )
        {
            $msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_GOOGLE_EVENT');
        }
        else
        {
            $msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_NO_GOOGLECALENDAR_ID');
        }
        
//$link = $result;
        $link = 'index.php?option=com_sportsmanagement&view=jsmgcalendars';
//$link = 'index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=editlineup&id=8534039&team=551305&prefill=';
//http://www.fussballineuropa.de/administrator/index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=editlineup&id=8534039&team=551305&prefill=

		$this->setRedirect($link,$msg);

    
    }    
    
}