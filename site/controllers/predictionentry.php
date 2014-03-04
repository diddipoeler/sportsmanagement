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

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );


/**
 * sportsmanagementControllerPredictionEntry
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerPredictionEntry extends JController
{
	
	function __construct()
	{
		//$post	= JRequest::get('post');
		// Register Extra tasks
		//$this->registerTask( 'add',			'display' );
		//$this->registerTask( 'edit',		'display' );
		//$this->registerTask( 'apply',		'save' );
		//$this->registerTask( 'copy',		'copysave' );
		//$this->registerTask( 'apply',		'savepredictiongame' );
		parent::__construct();
	}
	

	function display( )
	{
		// Get the view name from the query string
		//$viewName = JRequest::getVar( 'view', 'editmatch' );
		//$viewName = JRequest::getVar( 'view' );
//echo '<br /><pre>~' . print_r( $viewname, true ) . '~</pre><br />';

		// Get the view
		//$view =& $this->getView( $viewName );

		$this->showprojectheading();
		$this->showbackbutton();
		$this->showfooter();
	}

	function register()
	{
		$option = JRequest::getCmd('option');
    
		$mainframe = JFactory::getApplication();
		$document = JFactory::getDocument();
    
    
    
    JRequest::checkToken() or jexit(JText::_('COM_SPORTSMANAGEMENT_PRED_INVALID_TOKEN_REFUSED'));
		
    $msg	= '';
		$link	= '';
		$post	= JRequest::get('post');

$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTask<br><pre>'.print_r($this->getTask(),true).'</pre>'),'');
$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');

		$predictionGameID	= JRequest::getVar('prediction_id',	'',	'post',	'int');
		$joomlaUserID		= JRequest::getVar('user_id',		'',	'post',	'int');
		$approved			= JRequest::getVar('approved',		0,	'',		'int');
		
		
		$model		= $this->getModel('Prediction');
        $mdlPredictionEntry = JModel::getInstance("PredictionEntry", "sportsmanagementModel");
		$user		= JFactory::getUser();
		$isMember	= $model->checkPredictionMembership();

		if ( ( $user->id != $joomlaUserID )  )
		{
			$msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_1');
			$link = JFactory::getURI()->toString();
		}
		else
		{
			if ($isMember)
			{
				$msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_4');
				$link = JFactory::getURI()->toString();
			}
			else
			{
				//$post['registerDate'] = JHTML::date(time(),'Y-m-d h:i:s');
                $post['registerDate'] = JHtml::date($input = 'now', 'Y-m-d h:i:s', false);
				//if (!$model->store($post,'PredictionEntry'))
                //$model = JTable::getInstance('PredictionEntry','sportsmanagementTable');
				if (!$mdlPredictionEntry->store($post))
				{
					$msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_5');
					$link = JFactory::getURI()->toString();
				}
				else
				{
					$cids = array();
					$cids[] = $mdlPredictionEntry->getDbo()->insertid();
					JArrayHelper::toInteger($cids);

					$msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_MSG_2');
					if ($model->sendMembershipConfirmation($cids))
					{
						$msg .= ' - ';
						$msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_MSG_3');
					}
					else
					{
						$msg .= ' - ';
						$msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_6');
					}
					$params = array(	'option' => 'com_sportsmanagement',
										'view' => 'predictionentry',
										'prediction_id' => $predictionGameID,
										's' => '1' );

					$query = sportsmanagementHelperRoute::buildQuery($params);
					$link = JRoute::_('index.php?' . $query, false);
				}
			}
		}

		echo '<br /><br />';
		echo '#' . $msg . '#<br />'; 
		$this->setRedirect($link,$msg);
	}

	function select()
	{
		JRequest::checkToken() or jexit(JText::_('JL_PRED_INVALID_TOKEN_REFUSED'));
		$pID	= JRequest::getVar('prediction_id',	'',		'post',	'int');
		$uID	= JRequest::getVar('uid',			null,	'post',	'int');
		if (empty($uID)){$uID=null;}
		$link = PredictionHelperRoute::getPredictionTippEntryRoute($pID,$uID);
		//echo '<br />' . $link . '<br />';
		$this->setRedirect($link);
	}

	function selectprojectround()
	{
		JRequest::checkToken() or jexit(JText::_('JL_PRED_INVALID_TOKEN_REFUSED'));
		$post	= JRequest::get('post');
		$pID	= JRequest::getVar('prediction_id',	null,	'post',	'int');
        
        // diddipoeler
		//$pjID	= JRequest::getVar('project_id',	null,	'post',	'int');
        $pjID	= JRequest::getVar('p',	null,	'post',	'int');
        
		$rID	= JRequest::getVar('r',				null,	'post',	'int');
		$uID	= JRequest::getVar('uid',			null,	'post',	'int');
		$link = PredictionHelperRoute::getPredictionTippEntryRoute($pID,$uID,$rID,$pjID);
		$this->setRedirect($link);
	}

  /**
	 * Proxy for getModel
	 *
	 * @param	string	$name	The model name. Optional.
	 * @param	string	$prefix	The class prefix. Optional.
	 *
	 * @return	object	The model.
	 * @since	1.6
	 */
	function getModel($name = 'predictionentry', $prefix = 'sportsmanagementModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
	
	function addtipp()
	{
		JRequest::checkToken() or jexit(JText::_('JL_PRED_ENTRY_INVALID_TOKEN_PREDICTIONS_NOT_SAVED'));
    $option = JRequest::getCmd('option');
    $optiontext = strtoupper(JRequest::getCmd('option').'_');
		$mainframe = JFactory::getApplication();
		$document = JFactory::getDocument();
		
		$msg	= '';
		$link	= '';

		$predictionGameID	= JRequest::getVar('prediction_id',	'','post','int');
		$joomlaUserID		= JRequest::getVar('user_id',		'','post','int');
        $memberID		= JRequest::getVar('memberID',		'','post','int');
		$round_id			= JRequest::getVar('round_id',		'','post','int');
		$pjID				= JRequest::getVar('pjID',			'','post','int');
		$set_r				= JRequest::getVar('set_r',			'','post','int');
		$set_pj				= JRequest::getVar('set_pj',		'','post','int');

		$model		= $this->getModel('Prediction');
		$user		= JFactory::getUser();
		$isMember	= $model->checkPredictionMembership();
		$allowedAdmin = $model->getAllowed();

		if ( ( ( $user->id != $joomlaUserID ) ) && ( !$allowedAdmin ) )
		{
			$msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_1');
			$link = JFactory::getURI()->toString();
		}
		else
		{
			if ( ( !$isMember ) && ( !$allowedAdmin ) )
			{
				$msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_2');
				$link = JFactory::getURI()->toString();
			}
			else
			{
				if ($pjID!=$set_pj)
				{
					$params = array	(	'option' => 'com_sportsmanagement',
										'view' => 'predictionentry',
										'prediction_id' => $predictionGameID,
										'pj' => $set_pj
									);

					$query = sportsmanagementHelperRoute::buildQuery($params);
					$link = JRoute::_('index.php?' . $query,false);
					$this->setRedirect($link);
				}

				if ( $round_id != $set_r )
				{
					$params = array	(	'option' => 'com_sportsmanagement',
										'view' => 'predictionentry',
										'prediction_id' => $predictionGameID,
										'r' => $set_r,
										'pj' => $pjID
									);

					$query = JoomleagueHelperRoute::buildQuery($params);
					$link = JRoute::_('index.php?' . $query,false);
					$this->setRedirect($link);
				}

				$model = $this->getModel('PredictionEntry');
                if ( !$model->savePredictions($allowedAdmin) )
				{
					$msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_ERROR_3');
					$link = JFactory::getURI()->toString();
				}
				else
				{
					$msg .= JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CONTROLLER_MSG_1');
					$link = JFactory::getURI()->toString();
				}
			}
		}
		
    //echo '<br />' . $link . '<br />';
		//echo '<br />' . $msg . '<br />';
		
		$this->setRedirect($link,$msg);
	}

}
?>