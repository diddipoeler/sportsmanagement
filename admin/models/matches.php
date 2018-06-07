<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      matches.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');



/**
 * sportsmanagementModelMatches
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelMatches extends JModelList
{
	var $_identifier = "matches";
    var $_rid = 0;
    var $_season_id = 0;
    
    /**
     * sportsmanagementModelMatches::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'mc.match_number',
                        'mc.match_date',
                        'mc.id',
                        'mc.ordering'
                        );
                parent::__construct($config);
                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($getDBConnection);
        }
        
    /**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Initialise variables.
		//$app = JFactory::getApplication('administrator');
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.division', 'filter_division', '');
		$this->setState('filter.division', $temp_user_request);

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
//        $value = JFactory::getApplication()->input->getUInt('limitstart', 0);
//		$this->setState('list.start', $value);
//        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
//		$this->setState('list.start', $value);
        $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $app->get('list_limit'), 'int');
		$this->setState('list.limit', $value);	
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('mc.match_date', 'asc');
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
	}

	/**
	 * sportsmanagementModelMatches::getListQuery()
	 * 
	 * @return
	 */
	protected function getListQuery()
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $this->_season_id	= $app->getUserState( "$option.season_id", '0' );
        //$search_division	= $this->getState('filter.division');
        
        $this->_rid = JFactory::getApplication()->input->getvar('rid', 0);
        $this->_projectteam = JFactory::getApplication()->input->getvar('projectteam', 0);
        //$app->enqueueMessage(JText::_('sportsmanagementViewMatches _projectteam<br><pre>'.print_r($this->_projectteam,true).'</pre>'),'');
        
        if ( !$this->_rid )
        {
            $this->_rid	= $app->getUserState( "$option.rid", '0' );
        }
        
	if ( $this->_projectteam )
	{
	$this->_rid = '';
	}
		
        // Create a new query object.		
	$db = sportsmanagementHelper::getDBConnection();
	$query = $db->getQuery(true);
        $subQueryPlayerHome= $db->getQuery(true);
        $subQueryStaffHome= $db->getQuery(true);
        $subQueryPlayerAway= $db->getQuery(true);
        $subQueryStaffAway= $db->getQuery(true);
        $subQuery1= $db->getQuery(true);
        $subQuery2= $db->getQuery(true);
        $subQuery3= $db->getQuery(true);
        $subQuery4= $db->getQuery(true);
        $subQuery5= $db->getQuery(true);
	// Select some fields
	$query->select('mc.*');
	// From the match table
	$query->from('#__sportsmanagement_match AS mc');
        // join player home
        $subQueryPlayerHome->select('tp.id');
        $subQueryPlayerHome->from('#__sportsmanagement_season_team_person_id AS tp ');
        $subQueryPlayerHome->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id and st.season_id = tp.season_id');
        $subQueryPlayerHome->join('LEFT','#__sportsmanagement_project_team AS pthome ON pthome.team_id = st.id');
        $subQueryPlayerHome->where('pthome.id = mc.projectteam1_id');
        $subQueryPlayerHome->where('tp.season_id = '.$this->_season_id);
        $subQueryPlayerHome->where('tp.persontype = 1'); 
        // count match homeplayers
        $subQuery1->select('count(mp.id)');
        $subQuery1->from('#__sportsmanagement_match_player AS mp  ');
        $subQuery1->where('mp.match_id = mc.id AND (came_in=0 OR came_in=1) AND mp.teamplayer_id in ('.$subQueryPlayerHome.')');
        $query->select('('.$subQuery1.') AS homeplayers_count');
        // join staff home
        $subQueryStaffHome->select('tp.id');
        $subQueryStaffHome->from('#__sportsmanagement_season_team_person_id AS tp ');
        $subQueryStaffHome->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id and st.season_id = tp.season_id');
        $subQueryStaffHome->join('LEFT','#__sportsmanagement_project_team AS pthome ON pthome.team_id = st.id');
        $subQueryStaffHome->where('pthome.id = mc.projectteam1_id');
        $subQueryStaffHome->where('tp.season_id = '.$this->_season_id);
        $subQueryStaffHome->where('tp.persontype = 2'); 
        // count match homestaff
        $subQuery2->select('count(ms.id)');
        $subQuery2->from('#__sportsmanagement_match_staff AS ms  ');
        $subQuery2->where('ms.match_id = mc.id AND ms.team_staff_id in ('.$subQueryStaffHome.')');
        $query->select('('.$subQuery2.') AS homestaff_count');
        // join player away
        $subQueryPlayerAway->select('tp.id');
        $subQueryPlayerAway->from('#__sportsmanagement_season_team_person_id AS tp ');
        $subQueryPlayerAway->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id and st.season_id = tp.season_id');
        $subQueryPlayerAway->join('LEFT','#__sportsmanagement_project_team AS pthome ON pthome.team_id = st.id');
        $subQueryPlayerAway->where('pthome.id = mc.projectteam2_id');
        $subQueryPlayerAway->where('tp.season_id = '.$this->_season_id);
        $subQueryPlayerAway->where('tp.persontype = 1'); 
        // count match awayplayers
        $subQuery3->select('count(mp.id)');
        $subQuery3->from('#__sportsmanagement_match_player AS mp  ');
        $subQuery3->where('mp.match_id = mc.id AND (came_in=0 OR came_in=1) AND mp.teamplayer_id in ('.$subQueryPlayerAway.')');
        $query->select('('.$subQuery3.') AS awayplayers_count');
/**
 * join staff away
 */        
        $subQueryStaffAway->select('tp.id');
        $subQueryStaffAway->from('#__sportsmanagement_season_team_person_id AS tp ');
        $subQueryStaffAway->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id and st.season_id = tp.season_id');
        $subQueryStaffAway->join('LEFT','#__sportsmanagement_project_team AS pthome ON pthome.team_id = st.id');
        $subQueryStaffAway->where('pthome.id = mc.projectteam2_id');
        $subQueryStaffAway->where('tp.season_id = '.$this->_season_id);
        $subQueryStaffAway->where('tp.persontype = 2'); 
        // count match awaystaff
        $subQuery4->select('count(ms.id)');
        $subQuery4->from('#__sportsmanagement_match_staff AS ms  ');
        $subQuery4->where('ms.match_id = mc.id AND ms.team_staff_id in ('.$subQueryStaffAway.')');
        $query->select('('.$subQuery4.') AS awaystaff_count');

        // count match referee
        $subQuery5->select('count(mr.id)');
        $subQuery5->from('#__sportsmanagement_match_referee AS mr ');
        $subQuery5->where('mr.match_id = mc.id');
        $query->select('('.$subQuery5.') AS referees_count');
        
        // Join over the users for the checked out user.
	$query->select('u.name AS editor');
	$query->join('LEFT', '#__users AS u on mc.checked_out = u.id');
        $query->join('LEFT','#__sportsmanagement_project_team AS pthome ON pthome.id = mc.projectteam1_id');
        $query->join('LEFT','#__sportsmanagement_project_team AS ptaway ON ptaway.id = mc.projectteam2_id');
        $query->join('LEFT','#__sportsmanagement_team AS t1 ON t1.id = pthome.id');
        $query->join('LEFT','#__sportsmanagement_team AS t2 ON t2.id = ptaway.id');
        $query->join('LEFT','#__sportsmanagement_round AS r ON r.id = mc.round_id ');
        $query->select('divaway.id as divawayid');
        $query->join('LEFT','#__sportsmanagement_division AS divaway ON divaway.id = ptaway.division_id');
        $query->select('divhome.id as divhomeid'); 
        $query->join('LEFT','#__sportsmanagement_division AS divhome ON divhome.id = pthome.division_id');
        
	if ( $this->_rid )
	{
        $query->where(' mc.round_id = ' . $this->_rid);
	}
	if ( $this->_projectteam )
	{
	$query->where('( mc.projectteam1_id = ' . $this->_projectteam .' OR mc.projectteam2_id = '.$this->_projectteam.' )' );
	}
		
        if ($this->getState('filter.division'))
		{
        $query->where(' divhome.id = '.$this->_db->Quote($this->getState('filter.division')));
        }
		
        
        $query->order($db->escape($this->getState('list.ordering', 'mc.match_date')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
 
 if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }
         

		return $query;
        
	}



  /**
   * sportsmanagementModelMatches::checkMatchPicturePath()
   * 
   * @param mixed $match_id
   * @return
   */
  function checkMatchPicturePath($match_id)
  {
  $dest = JPATH_ROOT.'/images/com_sportsmanagement/database/matchreport/'.$match_id;
  $folder = 'matchreport/'.$match_id;
  $this->setState('folder', $folder);
  if(JFolder::exists($dest)) {
  }
  else
  {
  JFolder::create($dest);
  }
  
  }
  
  /**
   * sportsmanagementModelMatches::getMatchesCount()
   * 
   * @param mixed $project_id
   * @return
   */
  function getMatchesCount($project_id)
  {
  $db = sportsmanagementHelper::getDBConnection();
$query = $db->getQuery(true);

$query->select('count(m.id)');
$query->from('#__sportsmanagement_match as m');
$query->join('INNER','#__sportsmanagement_round as r ON r.id = m.round_id');
$query->where('r.project_id = '.$project_id);
$db->setQuery($query);
		return $db->loadResult();



  }


	/**
	 * sportsmanagementModelMatches::getMatchesByRound()
	 * 
	 * @param mixed $roundId
	 * @return
	 */
	function getMatchesByRound($roundId)
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');    
    // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        
		$query = 'SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match WHERE round_id='.$roundId;
		$db->setQuery($query);
		//echo($this->_db->getQuery());
		$result = $db->loadObjectList();
		if ($result === FALSE)
		{
			JError::raiseError(0, $db->getErrorMsg());
			return false;
		}
		return $result;
	}

}
?>
