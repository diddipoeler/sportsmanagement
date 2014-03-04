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

// Include dependancy of the main model form
jimport('joomla.application.component.modelform');
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
// Include dependancy of the dispatcher
jimport('joomla.event.dispatcher');

//require_once(JPATH_COMPONENT.DS.'models'.DS.'item.php');
require_once('prediction.php');


//class JoomleagueModelPredictionUsers extends JoomleagueModelPrediction
/**
 * sportsmanagementModelPredictionUser
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPredictionUser extends JModelForm
{
  var $predictionGameID = 0;
	var $predictionMemberID = 0;
	
	function __construct()
	{
		parent::__construct();
	}

	function savememberdata()
	{
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
		$picture			= JRequest::getVar('picture',		'','post','string',JREQUEST_ALLOWRAW);
		$aliasName			= JRequest::getVar('aliasName',		'','post','string',JREQUEST_ALLOWRAW);
    $group_id		= JRequest::getVar('group_id',		'','post','int');
		$pRegisterDate		= JRequest::getVar('registerDate',	'',	'post',	'date',JREQUEST_ALLOWRAW);
		$pRegisterTime		= JRequest::getVar('registerTime',	'',	'post',	'time',JREQUEST_ALLOWRAW);
		//echo '<br /><pre>~'.print_r($pRegisterDate,true).'~</pre><br />';
		//echo '<br /><pre>~'.print_r($pRegisterTime,true).'~</pre><br />';

		$dFavTeams='';foreach($fav_teams AS $key => $value){$dFavTeams.=$key.','.$value.';';}$dFavTeams=trim($dFavTeams,';');
		$dChampTeams='';foreach($champ_teams AS $key => $value){$dChampTeams.=$key.','.$value.';';}$dChampTeams=trim($dChampTeams,';');

		$registerDate = JoomleagueHelper::convertDate($pRegisterDate,0) . ' ' . $pRegisterTime . ':00';
		//echo '<br /><pre>~'.print_r($registerDate,true).'~</pre><br />';

		$query =	"	UPDATE	#__joomleague_prediction_member
							SET	registerDate='$registerDate',
								show_profile=$show_profile,
								group_id=$group_id,
								fav_team='$dFavTeams',
								champ_tipp='$dChampTeams',
								slogan='$slogan',
								aliasName='$aliasName',
								reminder=$reminder,
								receipt=$receipt,
								admintipp=$admintipp,
								picture='$picture'
						WHERE	id=$predictionMemberID";
		//echo $query . '<br />';
		$this->_db->setQuery($query);
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			$result = false;
			//echo '<br />ERROR~' . $query . '~<br />';
		}

		return $result;
	}

	function showMemberPicture($outputUserName, $user_id = 0)
	{
	global $mainframe, $option;
	$mainframe	=& JFactory::getApplication();
	$db =& JFactory::getDBO();
	$playerName = $outputUserName;
	$picture = '';
	
//	$mainframe->enqueueMessage(JText::_('username ->'.$outputUserName),'Notice');
//	$mainframe->enqueueMessage(JText::_('user_id ->'.$user_id),'Notice');
	
	
		if ($this->config['show_photo'])
		{
		// von welcher komponente soll das bild kommen
		// und ist die komponente installiert
		$query = "SELECT option
					FROM #__components
					WHERE option LIKE '" . $this->config['show_image_from'] . "'" ;
		$db->setQuery($query);
		$results = $db->loadResult();
		if ( !$results )
		{
    //$mainframe->enqueueMessage(JText::_('die komponente '.$this->config['show_image_from'].' ist f&uuml;r das profilbild nicht installiert'),'Error');
    }
		
//		$mainframe->enqueueMessage(JText::_('komponente ->'.$this->config['show_image_from']),'Notice');
		switch ( $this->config['show_image_from'] )
		{
    case 'com_sportsmanagement':
    $picture = $this->predictionMember->picture;
		
    break;
    
    case 'com_comprofiler':
    break;
    
    case 'com_kunena':
    $query = 'SELECT avatar
					FROM #__kunena_users
					WHERE userid = ' . (int)$user_id ;
		$db->setQuery($query);
		$results = $db->loadResult();
    //$mainframe->enqueueMessage(JText::_('com_kunena ->'.$results),'Notice');
    if ( $results )
    {
    $picture = 'media/kunena/avatars/'.$results;
    }
    
    
    break;
    
    case 'com_community':
    $query = 'SELECT avatar
					FROM #__community_users
					WHERE userid = ' . (int)$user_id ;
		$db->setQuery($query);
		$results = $db->loadResult();
    //$mainframe->enqueueMessage(JText::_('com_community ->'.$results),'Notice');
    if ( $results )
    {
    $picture = $results;
    }
    
    break;
    
    }
			//$imgTitle = JText::sprintf('JL_PRED_USERS_AVATAR_OF', $outputUserName, '');
			
			
			if ( !file_exists($picture) )
			{
				$picture = JoomleagueHelper::getDefaultPlaceholder("player");
			}
			//echo JHTML::image($picture, $imgTitle, array(' title' => $imgTitle));
			echo JoomleagueHelper::getPictureThumb($picture, $playerName,0,0);
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
	
	
	
	function memberPredictionData()
	{
		$dataObject = new stdClass();
		$dataObject->rankingAll		= 'X';
		$dataObject->lastTipp		= '';

		return $dataObject;
	}

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

	function getPredictionProjectTeams($project_id)
	{
		$query = '	SELECT	pt.id AS value,
							t.name AS text

					FROM #__joomleague_project_team AS pt
						LEFT JOIN #__joomleague_team AS t ON t.id=pt.team_id

					WHERE pt.project_id=' . (int)$project_id . '
					ORDER by text';

		//echo "<br />$query</br />";
		$this->_db->setQuery( $query );
		$results = $this->_db->loadObjectList();
		//echo '<br /><pre>~' . print_r($results,true) . '~</pre><br />';
		return $results;
	}
	
    /**
     * get data for pointschart
     * @return  
     */
		function getPointsChartData( )
		{
			$pgid	= $this->_db->Quote($this->predictionGameID);
			$uid	= $this->_db->Quote($this->predictionMemberID);



			$query = ' SELECT rounds.id, '
			     . ' rounds.roundcode AS roundcode, '
				   . ' rounds.name, '
				   . ' SUM(pr.points) AS points '
			     . ' FROM #__joomleague_round AS rounds '
			     . ' INNER JOIN #__joomleague_match AS matches ON rounds.id = matches.round_id '
			     . ' LEFT JOIN #__joomleague_prediction_result AS pr ON pr.match_id = matches.id '
           . ' LEFT JOIN #__joomleague_prediction_member AS prmem ON prmem.user_id = pr.user_id '
			     . ' WHERE pr.prediction_id = '.$pgid
				   . '  AND (matches.cancel IS NULL OR matches.cancel = 0)'
           . '  AND prmem.id = '.$uid			   
			     . ' GROUP BY rounds.roundcode'
			       ;
    		$this->_db->setQuery( $query );
    		$this->result = $this->_db->loadObjectList();
    		return $this->result;
		}	

    /**
     * get data for rankschart
     * @return  
     */
		function getRanksChartData( )
		{

		}		
}
?>