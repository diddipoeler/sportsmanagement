<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
class sportsmanagementModelPredictionUsers extends JModelLegacy
{
    
    static $config = null;

	/**
	 * sportsmanagementModelPredictionUsers::__construct()
	 * 
	 * @return void
	 */
	function __construct()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');

        $prediction = new sportsmanagementModelPrediction();  

        sportsmanagementModelPrediction::$roundID = $jinput->getVar('r','0');
       sportsmanagementModelPrediction::$pjID = $jinput->getVar('pj','0');
       sportsmanagementModelPrediction::$from = $jinput->getVar('from',$jinput->getVar('r','0'));
       sportsmanagementModelPrediction::$to = $jinput->getVar('to',$jinput->getVar('r','0'));
       
        sportsmanagementModelPrediction::$predictionGameID = $jinput->getVar('prediction_id','0');
        
        sportsmanagementModelPrediction::$predictionMemberID = $jinput->getInt('uid',0);
        sportsmanagementModelPrediction::$joomlaUserID = $jinput->getInt('juid',0);
        
        sportsmanagementModelPrediction::$pggroup = $jinput->getInt('pggroup',0);
        sportsmanagementModelPrediction::$pggrouprank = $jinput->getInt('pggrouprank',0);
        
        sportsmanagementModelPrediction::$isNewMember = $jinput->getInt('s',0);
        sportsmanagementModelPrediction::$tippEntryDone = $jinput->getInt('eok',0);
        
        sportsmanagementModelPrediction::$type = $jinput->getInt('type',0);
        sportsmanagementModelPrediction::$page = $jinput->getInt('page',1);
                
	   //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' predictionGameID<br><pre>'.print_r($this->predictionGameID,true).'</pre>'),'');
       
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
    $option = JFactory::getApplication()->input->getCmd('option');    
    $app = JFactory::getApplication();
    $jinput = $app->input;
    // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        
        $result	= true;
        $post = $jinput->post->getArray();

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');

		$predictionGameID = $post['prediction_id'];
		$joomlaUserID = $post['user_id'];
		$predictionMemberID	= $post['member_id'];
		$show_profile = $post['show_profile'];
		$fav_teams = $post['fav_team'];
        if ( isset($post['champ_tipp']) )
        {
		$champ_teams = $post['champ_tipp'];
        }
		$slogan	= $post['slogan'];
		$reminder = $post['reminder'];
		$receipt = $post['receipt'];
		$admintipp = $post['admintipp'];
        $group_id = $post['group_id'];
		$picture = $post['picture'];
		$aliasName = $post['aliasName'];
		$pRegisterDate = $post['registerDate'];
		$pRegisterTime = $post['registerTime'];

		$dFavTeams = '';
        foreach( $fav_teams AS $key => $value)
        {
            $dFavTeams .= $key.','.$value.';';
            }
            $dFavTeams = trim($dFavTeams,';');
		$dChampTeams = '';
        foreach( $champ_teams AS $key => $value)
        {
            $dChampTeams .= $key.','.$value.';';
            }
            $dChampTeams = trim($dChampTeams,';');

		$registerDate = sportsmanagementHelper::convertDate($pRegisterDate,0) . ' ' . $pRegisterTime . ':00';
	
        // Must be a valid primary key value.
        $object = new stdClass();
        $object->id = $predictionMemberID;
        $object->registerDate = $registerDate;
		$object->show_profile = $show_profile;
        $object->group_id = $group_id;
		$object->fav_team = $dFavTeams;
        if( $dChampTeams )
        {
		$object->champ_tipp = $dChampTeams;
        }
		$object->slogan = $slogan;
		$object->aliasName = $aliasName;
		$object->reminder = $reminder;
		$object->receipt = $receipt;
		$object->admintipp = $admintipp;
		$object->picture = $picture;

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' object<br><pre>'.print_r($object,true).'</pre>'),'');

        // Update their details in the table using id as the primary key.
        $resultquery = sportsmanagementHelper::getDBConnection()->updateObject('#__sportsmanagement_prediction_member', $object, 'id');
        
		if (!$resultquery)
		{
            $app->enqueueMessage(JText::_(__METHOD__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			$result = false;
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
	static function showMemberPicture($outputUserName, $user_id = 0)
	{

	$app = JFactory::getApplication();
	$db = sportsmanagementHelper::getDBConnection();
    $query = $db->getQuery(true);
	$playerName = $outputUserName;
	$picture = '';
	
	//$app->enqueueMessage(JText::_('username ->'.$outputUserName),'Notice');
	//$app->enqueueMessage(JText::_('user_id ->'.$user_id),'Notice');

	
	if (self::$config['show_photo'])
	{
	/**
	 * von welcher komponente soll das bild kommen
	 * und ist die komponente installiert
	 */
    
    // Select some fields
    $query->select('element');
    $query->from('#__extensions');
    $query->where("element LIKE '" . self::$config['show_image_from'] . "' ");

	$db->setQuery($query);
	$results = $db->loadResult();
	if ( !$results )
	{
    $app->enqueueMessage(JText::_('Die Komponente '.self::$config['show_image_from'].' ist f&uuml;r das Profilbild nicht installiert'),'Error');
    }
    
    // Select some fields
    $query->select('avatar');
    $query->where('userid = ' . (int)$user_id );

	switch ( self::$config['show_image_from'] )
	{
    case 'com_sportsmanagement':
    case 'prediction':
    $picture = sportsmanagementModelPrediction::$_predictionMember->picture;
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
    
    switch ( self::$config['show_image_from'] )
	{
	   case 'com_community':
       case 'com_cbe':
       if ( $db->setQuery($query) )
       {
	   $results = $db->loadResult();
       if ( $results )
       {
       $picture = $results;
       }
       }
       else
       {
       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorNum(),true).'</pre>'),'Error');
       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error'); 
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
		//$app->enqueueMessage(JText::_('user bild ->'.$picture.' ist nicht vorhanden'),'Error');
        $picture = sportsmanagementHelper::getDefaultPlaceholder("player");
        //$app->enqueueMessage(JText::_('nehme standard ->'.$picture),'Notice');
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
	static function memberPredictionData()
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
	static function getPredictionProjectTeams($project_id)
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
	   $document	= JFactory::getDocument();
    
    // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        // Select some fields
        $query->select('pt.id AS value,t.name AS text');
        $query->from('#__sportsmanagement_project_team AS pt');
        $query->join('INNER','#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id');
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
		static function getPointsChartData( )
		{
		  // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
		  $document	= JFactory::getDocument();
    
    // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        
			$pgid	= $db->Quote(sportsmanagementModelPrediction::$predictionGameID);
			$uid	= $db->Quote(sportsmanagementModelPrediction::$predictionMemberID);

// Select some fields
        $query->select('rounds.id,rounds.roundcode AS roundcode,rounds.name');
        $query->select('SUM(pr.points) AS points');
        $query->from('#__sportsmanagement_round AS rounds');
        $query->join('INNER', '#__sportsmanagement_match AS matches ON rounds.id = matches.round_id');
        $query->join('LEFT', '#__sportsmanagement_prediction_result AS pr ON pr.match_id = matches.id');
        $query->join('LEFT', '#__sportsmanagement_prediction_member AS prmem ON prmem.user_id = pr.user_id');
        
        $query->where('pr.prediction_id = '.$pgid);
        $query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
        $query->where('prmem.id = '.$uid);
        $query->group('rounds.roundcode');

    		$db->setQuery( $query );
    		$result = $db->loadObjectList();
    		return $result;
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