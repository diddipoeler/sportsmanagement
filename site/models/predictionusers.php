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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'prediction.php' );


/**
 * sportsmanagementModelPredictionUsers
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPredictionUsers extends JModel
{

	/**
	 * sportsmanagementModelPredictionUsers::__construct()
	 * 
	 * @return void
	 */
	function __construct()
	{
	   $option = JRequest::getCmd('option');    
    $mainframe = JFactory::getApplication();
    
    $this->predictionGameID		= JRequest::getInt('prediction_id',		0);
		$this->predictionMemberID	= JRequest::getInt('uid',	0);
		$this->joomlaUserID			= JRequest::getInt('juid',	0);
		$this->roundID				= JRequest::getInt('r',		0);
        $this->pggroup				= JRequest::getInt('pggroup',		0);
        $this->pggrouprank			= JRequest::getInt('pggrouprank',		0);
		$this->pjID					= JRequest::getInt('p',		0);
		$this->isNewMember			= JRequest::getInt('s',		0);
		$this->tippEntryDone		= JRequest::getInt('eok',	0);

		$this->from  				= JRequest::getInt('from',	$this->roundID);
		$this->to	 				= JRequest::getInt('to',	$this->roundID);
		$this->type  				= JRequest::getInt('type',	0);

		$this->page  				= JRequest::getInt('page',	1);
        
        //$prediction = JModel::getInstance("Prediction","sportsmanagementModel");
        $prediction = new sportsmanagementModelPrediction();  
        //$prediction->predictionGameID = $this->predictionGameID	;
        sportsmanagementModelPrediction::$predictionGameID = $this->predictionGameID;
        
        sportsmanagementModelPrediction::$predictionMemberID = $this->predictionMemberID;
        sportsmanagementModelPrediction::$joomlaUserID = $this->joomlaUserID;
        sportsmanagementModelPrediction::$roundID = $this->roundID;
        sportsmanagementModelPrediction::$pggroup = $this->pggroup;
        sportsmanagementModelPrediction::$pggrouprank = $this->pggrouprank;
        sportsmanagementModelPrediction::$pjID = $this->pjID;
        sportsmanagementModelPrediction::$isNewMember = $this->isNewMember;
        sportsmanagementModelPrediction::$tippEntryDone = $this->tippEntryDone;
        sportsmanagementModelPrediction::$from = $this->from;
        sportsmanagementModelPrediction::$to = $this->to;
        sportsmanagementModelPrediction::$type = $this->type;
        sportsmanagementModelPrediction::$page = $this->page;
        
	   //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' predictionGameID<br><pre>'.print_r($this->predictionGameID,true).'</pre>'),'');
       
		parent::__construct();
	}

	/**
	 * sportsmanagementModelPredictionUsers::savememberdata()
	 * 
	 * @return
	 */
	function savememberdata()
	{
		$document	= JFactory::getDocument();
    $option = JRequest::getCmd('option');    
    $mainframe = JFactory::getApplication();
    // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
        $result	= true;
		//$post	= JRequest::get('post');
		//echo '<br /><pre>~'.print_r($post,true).'~</pre><br />';

		$predictionGameID	= JRequest::getVar('prediction_id',	'','post','int');
		$joomlaUserID		= JRequest::getVar('user_id',		'','post','int');
		$predictionMemberID	= JRequest::getVar('member_id',		'','post','int');
		$show_profile		= JRequest::getVar('show_profile',	'','post','int');
		$fav_teams			= JRequest::getVar('fav_team',		'','post','array');
		$champ_teams		= JRequest::getVar('champ_tipp',	'','post','array');
		$slogan				= JRequest::getVar('slogan',		'','post','string',JREQUEST_ALLOWRAW);
		$reminder			= JRequest::getVar('reminder',		'','post','int');
		$receipt			= JRequest::getVar('receipt',		'','post','int');
		$admintipp			= JRequest::getVar('admintipp',		'','post','int');
        $group_id		= JRequest::getVar('group_id',		'','post','int');
		$picture			= JRequest::getVar('picture',		'','post','string',JREQUEST_ALLOWRAW);
		$aliasName			= JRequest::getVar('aliasName',		'','post','string',JREQUEST_ALLOWRAW);

		$pRegisterDate		= JRequest::getVar('registerDate',	'',	'post',	'date',JREQUEST_ALLOWRAW);
		$pRegisterTime		= JRequest::getVar('registerTime',	'',	'post',	'time',JREQUEST_ALLOWRAW);

		$dFavTeams='';foreach($fav_teams AS $key => $value){$dFavTeams.=$key.','.$value.';';}$dFavTeams=trim($dFavTeams,';');
		$dChampTeams='';foreach($champ_teams AS $key => $value){$dChampTeams.=$key.','.$value.';';}$dChampTeams=trim($dChampTeams,';');

		$registerDate = sportsmanagementHelper::convertDate($pRegisterDate,0) . ' ' . $pRegisterTime . ':00';
	
        // Must be a valid primary key value.
        $object = new stdClass();
        $object->id = $predictionMemberID;
        $object->registerDate = $registerDate;
		$object->show_profile = $show_profile;
        $object->group_id = $group_id;
		$object->fav_team = $dFavTeams;
		$object->champ_tipp = $dChampTeams;
		$object->slogan = $slogan;
		$object->aliasName = $aliasName;
		$object->reminder = $reminder;
		$object->receipt = $receipt;
		$object->admintipp = $admintipp;
		$object->picture = $picture;

        // Update their details in the table using id as the primary key.
        $resultquery = JFactory::getDbo()->updateObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_member', $object, 'id');
        
		if (!$resultquery)
		{
			//$this->setError($this->_db->getErrorMsg());
            $mainframe->enqueueMessage(JText::_(__METHOD__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			$result = false;
			//echo '<br />ERROR~' . $query . '~<br />';
		}

		return $result;
	}

	/**
	 * sportsmanagementModelPredictionUsers::showMemberPicture()
	 * 
	 * @param mixed $outputUserName
	 * @param integer $user_id
	 * @return void
	 */
	function showMemberPicture($outputUserName, $user_id = 0)
	{

	$mainframe	= JFactory::getApplication();
	$db = JFactory::getDBO();
    $query = $db->getQuery(true);
	$playerName = $outputUserName;
	$picture = '';
	
	//$mainframe->enqueueMessage(JText::_('username ->'.$outputUserName),'Notice');
	//$mainframe->enqueueMessage(JText::_('user_id ->'.$user_id),'Notice');

	
	if ($this->config['show_photo'])
	{
	// von welcher komponente soll das bild kommen
	// und ist die komponente installiert
    
    // Select some fields
    $query->select('element');
    $query->from('#__extensions');
    $query->where("element LIKE '" . $this->config['show_image_from'] . "' pt.project_id = " . (int)$project_id );

	$db->setQuery($query);
	$results = $db->loadResult();
	if ( !$results )
	{
    $mainframe->enqueueMessage(JText::_('Die Komponente '.$this->config['show_image_from'].' ist f&uuml;r das Profilbild nicht installiert'),'Error');
    }
    
    // Select some fields
    $query->select('avatar');
    $query->where('userid = ' . (int)$user_id );

	switch ( $this->config['show_image_from'] )
	{
    case 'com_sportsmanagement':
    case 'prediction':
    $picture = $this->predictionMember->picture;
    break;
    
    case 'com_cbe':
    $picture = 'components/com_cbe/assets/user.png';
    $query->from('#__cbe_users');
    break;
    
    case 'com_comprofiler':
    $query->clear('where');
    $query->from('#__comprofiler');
    $query->where('user_id = ' . (int)$user_id );
    break;
    
    case 'com_kunena':
    $query->from('#__kunena_users');
    $picture = 'media/kunena/avatars/resized/size200/nophoto.jpg';
    break;
    
    case 'com_community':
    $query->from('#__community_users');
    break;
    
    }
    
    switch ( $this->config['show_image_from'] )
	{
	   case 'com_community':
       case 'com_cbe':
       $db->setQuery($query);
	   $results = $db->loadResult();
       if ( $results )
       {
       $picture = $results;
       }
       break;
       case 'com_kunena':
       $db->setQuery($query);
	   $results = $db->loadResult();
       if ( $results )
       {
       $picture = 'media/kunena/avatars/'.$results;
       }
       break;
       case 'com_comprofiler':
       $db->setQuery($query);
	   $results = $db->loadResult();
       if ( $results )
       {
       $picture = 'images/comprofiler/'.$results;
       }
	   break;
    }   
			
	if ( !file_exists($picture) )
	{
		//$mainframe->enqueueMessage(JText::_('user bild ->'.$picture.' ist nicht vorhanden'),'Error');
        $picture = sportsmanagementHelper::getDefaultPlaceholder("player");
        //$mainframe->enqueueMessage(JText::_('nehme standard ->'.$picture),'Notice');
	}
	//echo JHTML::image($picture, $imgTitle, array(' title' => $imgTitle));
	echo sportsmanagementHelper::getPictureThumb($picture, $playerName,0,0);
	}
	else
	{
		echo '&nbsp;';
	}
    
	}
  
  /**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.7
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$app = JFactory::getApplication('site');
    // Get the form.
		$form = $this->loadForm('com_sportsmanagement.'.$this->name, $this->name,
				array('load_data' => $loadData) );
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.7
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.'.$this->name.'.data', array());
		if (empty($data))
		{
			$data = $this->getData();
		}
		return $data;
	}
	
	/**
	 * sportsmanagementModelPredictionUsers::memberPredictionData()
	 * 
	 * @return
	 */
	function memberPredictionData()
	{
		$dataObject = new stdClass();
		$dataObject->rankingAll		= 'X';
		$dataObject->lastTipp		= '';

		return $dataObject;
	}

	/**
	 * sportsmanagementModelPredictionUsers::getChampTippAllowed()
	 * 
	 * @return
	 */
	function getChampTippAllowed()
	{
		$allowed = false;
		$user = & JFactory::getUser();

		if ($user->id > 0)
		{
			$auth = & JFactory::getACL();
			$aro_group = $auth->getAroGroup($user->id);

			if ((strtolower($aro_group->name) == 'super administrator') || (strtolower($aro_group->name) == 'administrator'))
			{
				$allowed = true;
			}
		}
		return $allowed;
	}

	/**
	 * sportsmanagementModelPredictionUsers::getPredictionProjectTeams()
	 * 
	 * @param mixed $project_id
	 * @return
	 */
	function getPredictionProjectTeams($project_id)
	{
	   $document	= JFactory::getDocument();
    $option = JRequest::getCmd('option');    
    $mainframe = JFactory::getApplication();
    // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        // Select some fields
        $query->select('pt.id AS value,t.name AS text');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st.team_id');
        $query->where('pt.project_id = ' . (int)$project_id );
        $query->order('text');

		$db->setQuery( $query );
		$results = $db->loadObjectList();

		return $results;
	}
	
    
		/**
		 * sportsmanagementModelPredictionUsers::getPointsChartData()
		 * 
		 * @return
		 */
		function getPointsChartData( )
		{
		  $document	= JFactory::getDocument();
    $option = JRequest::getCmd('option');    
    $mainframe = JFactory::getApplication();
    // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
			$pgid	= $db->Quote(sportsmanagementModelPrediction::$predictionGameID);
			$uid	= $db->Quote(sportsmanagementModelPrediction::$predictionMemberID);

// Select some fields
        $query->select('rounds.id,rounds.roundcode AS roundcode,rounds.name');
        $query->select('SUM(pr.points) AS points');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rounds');
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS matches ON rounds.id = matches.round_id');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_result AS pr ON pr.match_id = matches.id');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_member AS prmem ON prmem.user_id = pr.user_id');
        
        $query->where('pr.prediction_id = '.$pgid);
        $query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
        $query->where('prmem.id = '.$uid);
        $query->group('rounds.roundcode');

    		$db->setQuery( $query );
    		$this->result = $db->loadObjectList();
    		return $this->result;
		}	

    
		/**
		 * sportsmanagementModelPredictionUsers::getRanksChartData()
		 * 
		 * @return void
		 */
		function getRanksChartData( )
		{

		}		
}
?>