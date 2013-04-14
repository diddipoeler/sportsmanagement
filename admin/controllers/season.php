<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * HelloWorld Controller
 */
class sportsmanagementControllerseason extends JControllerForm
{

/*
function saveorder()
{
	$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
	// Initialize variables
	$db	=& JFactory::getDBO();
	$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
	$total	= count( $cid );
	$order= JRequest::getVar( 'order', array(0), 'post', 'array' );
	JArrayHelper::toInteger($order, array(0));
 
	$row =& JTable::getInstance('Season', 'sportsmanagementTable');
 
	// update ordering values
	for( $i=0; $i < $total; $i++ ) {
		$row->load( (int) $cid[$i] );
 
		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				JError::raiseError(500, 
                                     $db->getErrorMsg() );
			}
		}
	}
 
	$row->reorder();
 
	$msg 	= 'New ordering saved';
	$this->setRedirect('index.php?option='.$option, $msg);
}
*/

}
