<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      predictionmembers.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

/**
 * sportsmanagementControllerpredictionmembers
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerpredictionmembers extends JControllerAdmin
{
    
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerLegacy
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
        
        // Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;


	}    
    
    /**
     * sportsmanagementControllerpredictionmembers::save_memberlist()
     * 
     * @return void
     */
    function save_memberlist()
    {
    	
        // Check for request forgeries
		JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

        $model = $this->getModel();
       $msg = $model->save_memberlist();
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);    
        
        
    }
    
    /**
     * sportsmanagementControllerpredictionmembers::editlist()
     * 
     * @return void
     */
    function editlist()
	{
	   $msg		= '';
       $link = 'index.php?option=com_sportsmanagement&view=predictionmembers&layout=editlist';
		//echo $msg;
		$this->setRedirect( $link, $msg );
       
    }
       
	/**
	 * sportsmanagementControllerpredictionmembers::sendReminder()
	 * 
	 * @return void
	 */
	function reminder()
	{
/**
 * This will send an email to all members of the prediction game with reminder option enabled. Are you sure?
 */
        $post = $this->jsmjinput->post->getArray();
        $cid = $this->jsmjinput->getVar('cid', null, 'post', 'array');
		$pgmid = JFactory::getApplication()->input->getVar( 'prediction_id', 0, 'post', 'INT' );

		if ( $pgmid == 0 )
		{
			JError::raiseWarning( 500, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_SELECT_ERROR' ) );
		}
		$msg		= '';
		$d			= ' - ';

		$model = $this->getModel( 'predictionmember' );
    $model->sendEmailtoMembers($cid,$pgmid);

		$link = 'index.php?option=com_sportsmanagement&view=predictionmembers';
		//echo $msg;
		$this->setRedirect( $link, $msg );
	}
    
    
    
    /**
     * sportsmanagementControllerpredictionmembers::publish()
     * 
     * @return void
     */
    function publish()
	{
		$cids = JFactory::getApplication()->input->getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger( $cids );
		$predictionGameID	= JFactory::getApplication()->input->getVar( 'prediction_id', '', 'post', 'int' );

		if ( count( $cids ) < 1 )
		{
			JError::raiseError( 500, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_SEL_MEMBER_APPR' ) );
		}

		$model = $this->getModel( 'predictionmember' );
		if( !$model->publishpredmembers( $cids, 1, $predictionGameID ) )
		{
			echo "<script> alert( '" . $model->getError(true) . "' ); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_sportsmanagement&view=predictionmembers' );
	}
    
    
    /**
     * sportsmanagementControllerpredictionmembers::unpublish()
     * 
     * @return void
     */
    function unpublish()
	{
		$cids = JFactory::getApplication()->input->getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger( $cids );
		$predictionGameID	= JFactory::getApplication()->input->getVar( 'prediction_id', '', 'post', 'int' );

		if ( count( $cids ) < 1 )
		{
			JError::raiseError( 500, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_SEL_MEMBER_REJECT' ) );
		}

		$model = $this->getModel( 'predictionmember' );
		if ( !$model->publishpredmembers( $cids, 0, $predictionGameID ) )
		{
			echo "<script> alert( '" . $model->getError(true)  ."' ); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_sportsmanagement&view=predictionmembers' );
	}
    
    
    /**
     * sportsmanagementControllerpredictionmembers::remove()
     * 
     * @return void
     */
    function remove()
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
    
		$d		= ' - ';
		$msg	= '';
		$cid	= JFactory::getApplication()->input->getVar('cid',array(),'post','array');
		JArrayHelper::toInteger($cid);
		$prediction_id	= JFactory::getApplication()->input->getInt('prediction_id',(-1),'post');
		//echo '<pre>'; print_r($cid); echo '</pre>';

		if (count($cid) < 1)
		{
			JError::raiseError(500,JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_DEL_ITEM'));
		}

		$model = $this->getModel('predictionmember');

		if (!$model->deletePredictionResults($cid,$prediction_id))
		{
			$msg .= $d . JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_DEL_MSG');
		}
		$msg .= $d . JText::_('COM_SPORTSMANAGEMENTADMIN_PMEMBER_CTRL_DEL_PRESULTS');

		if (!$model->deletePredictionMembers($cid))
		{
			$msg .= JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_DEL_PMEMBERS_MSG');
		}

		$msg .= $d . JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_CTRL_DEL_PMEMBERS');

		$link = 'index.php?option=com_sportsmanagement&view=predictionmembers';
		//echo $msg;
		$this->setRedirect($link,$msg);
	}
    
  

    
    /**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'predictionmember', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}