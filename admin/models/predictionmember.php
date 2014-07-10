<?php



// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');


/**
 * sportsmanagementModelpredictionmember
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelpredictionmember extends JModelAdmin
{
	
/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_sportsmanagement.message.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
    
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'predictionmember', $prefix = 'sportsmanagementTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
    
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.predictionmember', 'predictionmember', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
              
        
		return $form;
	}
    
	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string	Script files
	 */
	public function getScript() 
	{
		return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
	}
    
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.predictionmember.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}
	
	
	
	
	
	
	
    
    
  /**
   * sportsmanagementModelpredictionmember::sendEmailtoMembers()
   * 
   * @param mixed $cid
   * @param mixed $prediction_id
   * @return void
   */
  function sendEmailtoMembers($cid,$prediction_id)
  {
    $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $config = JFactory::getConfig();
  $mailer =& JFactory::getMailer();
  
  //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config <br><pre>'.print_r($config,true).'</pre>'),'');
  
//  $meta_keys[] = $config->getValue( 'config.MetaKeys' );
//$your_name = $config->getValue( 'config.sitename' );

//add the sender Information.
$sender = array( 
    $config->getValue( 'config.mailfrom' ),
    $config->getValue( 'config.fromname' ) );

$mailer->setSender($sender); 

  foreach ( $cid as $key => $value )
    {
    $member_email = $this->getPredictionMemberEMailAdress( $value );
    
    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($member_email,true).'</pre>'),'');
    
    //add the recipient. $recipient = $user_email;
    $mailer->addRecipient($member_email); 
    //add the subject
     $subject = addslashes(
				sprintf(
				JText::_( "COM_SPORTSMANAGEMENT_EMAIL_PREDICTION_REMINDER_TIPS_RESULTS" ),
				'perdictionname' ) );
    $mailer->setSubject($subject);

//add body
$message = 'Tip-Results';
$mailer->setBody($message);
$send =& $mailer->Send();

if ( $send !== true ) {
    //echo 'Error sending email: ' . $send->message;
    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($send->message,true).'</pre>'),'Error');
} else {
    //echo 'Mail sent';
    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($send->message,true).'</pre>'),'');
}
   
				
    


//    JUtility::sendMail( '', '', $member_email, $subject, $message );

    }
  
  }



	/**
	 * sportsmanagementModelpredictionmember::getSystemAdminsEMailAdresses()
	 * 
	 * @return
	 */
	function getSystemAdminsEMailAdresses()
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
		$query =	'	SELECT u.email
						FROM #__users AS u
						WHERE	u.sendEmail = 1 AND
								u.block = 0 AND
								u.usertype = "Super Administrator"
						ORDER BY u.email';
//echo $query . '<br />';
		$this->_db->setQuery( $query );
		return $this->_db->loadColumn();
	}

	/**
	 * sportsmanagementModelpredictionmember::getPredictionGameAdminsEMailAdresses()
	 * 
	 * @param mixed $predictionGameID
	 * @return
	 */
	function getPredictionGameAdminsEMailAdresses( $predictionGameID )
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
		$query =	'	SELECT u.email
						FROM #__users AS u
						INNER JOIN #__sportsmanagement_prediction_admin AS pa ON	pa.prediction_id = ' . (int) $predictionGameID . ' AND
																			pa.user_id = u.id
						WHERE	u.sendEmail = 1 AND
								u.block = 0
						ORDER BY u.email';
//echo $query . '<br />';
		$this->_db->setQuery( $query );
		return $this->_db->loadColumn();
	}

	/**
	 * sportsmanagementModelpredictionmember::getPredictionMembersEMailAdresses()
	 * 
	 * @param mixed $cids
	 * @return
	 */
	function getPredictionMembersEMailAdresses( $cids )
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
		//echo '<br /><pre>~' . print_r( $cids, true ) . '~</pre><br />';
		$query =	'	SELECT user_id
						FROM #__sportsmanagement_prediction_member
						WHERE	id IN (' . $cids . ')';
		//echo $query . '<br />';
		$this->_db->setQuery( $query );
		if ( !$cids = $this->_db->loadColumn() ) { return false; }
		//echo '<br /><pre>~' . print_r( $cids, true ) . '~</pre><br />';

		JArrayHelper::toInteger( $cids );
		$cids = implode( ',', $cids );
		$query =	'	SELECT u.email
						FROM #__users AS u
						WHERE	
								u.block = 0 AND
								u.id IN (' . $cids . ')
						ORDER BY u.email';
		//echo $query . '<br />';
		$this->_db->setQuery( $query );
		return $this->_db->loadColumn();
	}

	/**
	 * sportsmanagementModelpredictionmember::getPredictionMemberEMailAdress()
	 * 
	 * @param mixed $predictionMemberID
	 * @return
	 */
	function getPredictionMemberEMailAdress( $predictionMemberID )
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
		
    //echo '<br />predictionMemberID<pre>~' . print_r( $predictionMemberID, true ) . '~</pre><br />';
		
    $query =	'	SELECT user_id
						FROM #__sportsmanagement_prediction_member
						WHERE	id = ' . $predictionMemberID;
		
    echo $query . '<br />';
		
    $this->_db->setQuery( $query );
		if ( !$user_id = $this->_db->loadResult() ) { return false; }
		
    echo '<br />user_id<pre>~' . print_r( $user_id, true ) . '~</pre><br />';

		$query =	'	SELECT u.email
						FROM #__users AS u
						WHERE	
								u.block = 0 AND
								u.id = ' . $user_id . '
						ORDER BY u.email';
		
    echo $query . '<br />';
		
    $this->_db->setQuery( $query );
		return $this->_db->loadResult();
	}
    
    /**
     * sportsmanagementModelpredictionmember::getPredictionGroups()
     * 
     * @return
     */
    function getPredictionGroups()
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
        $query = 'SELECT id, name as text FROM #__sportsmanagement_prediction_groups ORDER BY name ASC ';
		$this->_db->setQuery($query);
		if (!$result = $this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return array();
		}
		return $result;
    }

	/**
	 * Method to (un)publish / (un)approve a prediction member
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5.0a
	 */
	function publish( $cid = array(), $publish = 1, $predictionGameID )
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
		$user =& JFactory::getUser();
		if ( count( $cid ) )
		{
			$cids = implode( ',', $cid );

			$query =	'	UPDATE #__sportsmanagement_prediction_member
							SET approved = ' . (int) $publish . '
							WHERE id IN ( ' . $cids . ' )
							AND ( checked_out = 0 OR ( checked_out = ' . (int) $user->get( 'id' ) . ' ) )';

			$this->_db->setQuery( $query );
            
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' publish<br><pre>'.print_r($publish,true).'</pre>'),'');
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' predictionGameID<br><pre>'.print_r($predictionGameID,true).'</pre>'),'');
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query,true).'</pre>'),'');
            
			if ( !$this->_db->query() )
			{
				//$this->setError( $this->_db->getErrorMsg() );
                $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->_db->getErrorMsg(),true).'</pre>'),'Error');
				return false;
			}

			// create and send mail about approving member here

			$systemAdminsMails = $this->getSystemAdminsEMailAdresses();
			//echo '<br /><pre>~' . print_r( $systemAdminsMails, true ) . '~</pre><br />';

			$predictionGameAdminsMails = $this->getPredictionGameAdminsEMailAdresses( $predictionGameID );
			//echo '<br /><pre>~' . print_r( $predictionGameAdminsMails, true ) . '~</pre><br />';

			$predictionGameMembersMails = $this->getPredictionMembersEMailAdresses( $cids );
			//echo '<br /><pre>~' . print_r( $predictionGameMembersMails, true ) . '~</pre><br />';

			foreach ( $cid as $predictionMemberID )
			{
				//echo '<br /><pre>~' . print_r( $predictionMemberID, true ) . '~</pre><br />';

				$predictionGameMemberMail = $this->getPredictionMemberEMailAdress( $predictionMemberID );
				//echo '<br /><pre>~' . print_r( $predictionGameMemberMail, true ) . '~</pre><br />';

				if ( count( $predictionGameMemberMail ) > 0 )
				{
					//Fetch the mail object
					$mailer =& JFactory::getMailer();

					//Set a sender
					$config =& JFactory::getConfig();
					$sender = array( $config->getValue( 'config.mailfrom' ), $config->getValue( 'config.fromname' ) );
					//echo '<br /><pre>~' . print_r( $sender, true ) . '~</pre><br />';
					$mailer->setSender( $sender );

					//set Member as recipient
					$lastMailAdress = '';
					$recipient = array();
					foreach ( $predictionGameMemberMail AS $predictionGameMember_EMail )
					{
						if ( $lastMailAdress != $predictionGameMember_EMail )
						{
							$recipient[] = $predictionGameMember_EMail;
							$lastMailAdress = $predictionGameMember_EMail;
						}
					}
					//echo '<br />recipient<pre>~' . print_r( $recipient, true ) . '~</pre><br />';
					$mailer->addRecipient( $recipient );
					//unset( $recipient );

					//set system admins as BCC recipients
					$lastMailAdress = '';
					$recipientAdmins = array();
					foreach ( $systemAdminsMails AS $systemAdminMail )
					{
						if ( $lastMailAdress != $systemAdminMail )
						{
							$recipientAdmins[] = $systemAdminMail;
							$lastMailAdress = $systemAdminMail;
						}
					}
					$lastMailAdress = '';
					//echo '<br />recipientAdmins<pre>~' . print_r( $recipientAdmins, true ) . '~</pre><br />';

					//set predictiongame admins as BCC recipients
					foreach ( $predictionGameAdminsMails AS $predictionGameAdminMail )
					{
						if ( $lastMailAdress != $predictionGameAdminMail )
						{
							$recipientAdmins[] = $predictionGameAdminMail;
							$lastMailAdress = $predictionGameAdminMail;
						}
					}
					//echo '<br />recipientAdmins<pre>~' . print_r( $recipientAdmins, true ) . '~</pre><br />';
					$mailer->addBCC( $recipientAdmins );
					unset( $recipientAdmins );

					//Create the mail
					//$body = "Your body string\nin double quotes if you want to parse the \nnewlines etc";
					if ( $publish == 1 )
					{
						$mailer->setSubject( JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_APPROVED') );
						$body = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_REQ_APPROVED');
					}
					else
					{
						$mailer->setSubject( JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_REJECTED') );
						$body = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_APPROVEMENT_REJECTED');
					}
					$mailer->setBody( $body );
					echo '<br /><pre>~' . print_r( $mailer, true ) . '~</pre><br />';

					// Optional file attached
					//$mailer->addAttachment(PATH_COMPONENT.DS.'assets'.DS.'document.pdf');
					//echo '<br /><pre>~' . print_r( $mailer, true ) . '~</pre><br />';

					//Sending the mail
					$send =& $mailer->Send();
					if ( $send !== true )
					{
						echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_ERROR_SEND') . print_r( $recipient, true ) . '<br />';
						echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_ERROR_MSG') . $send->message;
					}
					else
					{
						echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_MAIL_SENT');
					}
					echo '<br /><br />';
				}
				else
				{
					// joomla_user is blocked or has set sendEmail to off
					// can't send email
					//return false;
				}
			}
		}

		return true;
	}

	

	/**
	 * sportsmanagementModelpredictionmember::deletePredictionMembers()
	 * 
	 * @param mixed $cid
	 * @return
	 */
	function deletePredictionMembers( $cid = array() )
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
		if ( count( $cid ) )
		{
			JArrayHelper::toInteger( $cid );
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__sportsmanagement_prediction_member WHERE id IN (' . $cids . ')';
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() )
			{
				//$this->setError( $this->_db->getErrorMsg() );
                $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->_db->getErrorMsg(),true).'</pre>'),'Error');
				return false;
			}
		}
		return true;
	}

	

	/**
	 * sportsmanagementModelpredictionmember::deletePredictionResults()
	 * 
	 * @param mixed $cid
	 * @param integer $prediction_id
	 * @return
	 */
	function deletePredictionResults($cid=array(),$prediction_id=0)
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
		if (count($cid))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode(',',$cid);
			$query = 'SELECT user_id FROM #__sportsmanagement_prediction_member WHERE id IN (' . $cids . ') AND prediction_id = ' . $prediction_id;
			//echo $query . '<br />';
			$this->_db->setQuery($query);
			$this->_db->query();

			if (!$result = $this->_db->loadColumn())
			{
				return true;
			}
			//echo '<pre>'; print_r($result); echo '</pre>';

			JArrayHelper::toInteger($result);
			$cids = implode(',',$result);
			$query = 'DELETE FROM #__sportsmanagement_prediction_result WHERE user_id IN (' . $cids . ') AND prediction_id = ' . $prediction_id;
			//echo $query . '<br />'; return true;
			$this->_db->setQuery($query);
			if (!$this->_db->query())
			{
				//$this->setError($this->_db->getErrorMsg());
                $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->_db->getErrorMsg(),true).'</pre>'),'Error');
				return false;
			}
		}
		return true;
	}

}
?>