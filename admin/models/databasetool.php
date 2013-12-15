<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 

class sportsmanagementModeldatabasetool extends JModelAdmin
{

    var $_sport_types_events = array();
    var $_sport_types_position = array();
    var $_sport_types_position_parent = array();
    
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
		$form = $this->loadForm('com_sportsmanagement.databasetool', 'databasetool', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        /*        
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($option)->get('ph_icon',''));
        $form->setFieldAttribute('picture', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/agegroups');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        */
		return $form;
	}
    
    function getSportsManagementTables()
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $query="SHOW TABLES LIKE '%_".COM_SPORTSMANAGEMENT_TABLE."%'";
		$this->_db->setQuery($query);
		return $this->_db->loadResultArray();
    }
    
    function setSportsManagementTableQuery($table, $command)
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $query = strtoupper($command).' TABLE `'.$table.'`'; 
            $this->_db->setQuery($query);
            if (!$this->_db->query())
		{
			$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool getErrorMsg<br><pre>'.print_r($this->_db->getErrorMsg(),true).'</pre>'),'Error');
			return false;
		}
        
        
		return true;
    }
    
    function createSportTypeArray()
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $params = JComponentHelper::getParams( $option );
        $sporttypes = $params->get( 'cfg_sport_types' );
        $export = array();
        
        foreach ( $sporttypes as $key => $type )
        {
        unset ($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_E_GREEN_CARD';
		$temp->icon = 'images/'.$option.'/database/events/'.$type.'/green_card.png';
        $export[] = $temp;
        $this->_sport_types_events[$type] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_E_YELLOW_CARD';
		$temp->icon = 'images/'.$option.'/database/events/'.$type.'/yellow_card.png';
        $export[] = $temp;
        $this->_sport_types_events[$type] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_E_RED_CARD';
		$temp->icon = 'images/'.$option.'/database/events/'.$type.'/red_card.png';
        $export[] = $temp;
        $this->_sport_types_events[$type] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_E_PENALTY_GOAL';
		$temp->icon = 'images/'.$option.'/database/events/'.$type.'/penalty_goal.png';
        $export[] = $temp;
        $this->_sport_types_events[$type] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_E_INJURY';
		$temp->icon = 'images/'.$option.'/database/events/'.$type.'/injured.png';
        $export[] = $temp;
        $this->_sport_types_events[$type] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_E_GOAL';
        $temp->icon = 'images/'.$option.'/database/events/'.$type.'/goal.png';
        $export[] = $temp;
        $this->_sport_types_events[$type] = array_merge($export);
        
        unset ($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_F_PLAYERS';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '1';
        $export[] = $temp;
        $this->_sport_types_position[$type] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_F_TEAM_STAFF';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '2';
        $export[] = $temp;
        $this->_sport_types_position[$type] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_F_COACHES';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '2';
        $export[] = $temp;
        $this->_sport_types_position[$type] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_F_MEDICAL_STAFF';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '2';
        $export[] = $temp;
        $this->_sport_types_position[$type] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_F_REFEREES';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '3';
        $export[] = $temp;
        $this->_sport_types_position[$type] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_F_CLUB_STAFF';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '4';
        $export[] = $temp;
        $this->_sport_types_position[$type] = array_merge($export);
        
        unset ($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_P_GOALKEEPER';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '1';
        $export[] = $temp;
        $this->_sport_types_position_parent[strtoupper($option).'_F_PLAYERS'] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_P_DEFENDER';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '1';
        $export[] = $temp;
        $this->_sport_types_position_parent[strtoupper($option).'_F_PLAYERS'] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_P_MIDFIELDER';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '1';
        $export[] = $temp;
        $this->_sport_types_position_parent[strtoupper($option).'_F_PLAYERS'] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_P_FORWARD';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '1';
        $export[] = $temp;
        $this->_sport_types_position_parent[strtoupper($option).'_F_PLAYERS'] = array_merge($export);
        
        unset ($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_F_CLUB_MANAGER';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '4';
        $export[] = $temp;
        $this->_sport_types_position_parent[strtoupper($option).'_F_CLUB_STAFF'] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_F_CLUB_YOUTH_MANAGER';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '4';
        $export[] = $temp;
        $this->_sport_types_position_parent[strtoupper($option).'_F_CLUB_STAFF'] = array_merge($export);
        
        unset ($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_F_MAIN_REFEREE';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '3';
        $export[] = $temp;
        $this->_sport_types_position_parent[strtoupper($option).'_F_REFEREES'] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_F_CENTER_REFEREE';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '3';
        $export[] = $temp;
        $this->_sport_types_position_parent[strtoupper($option).'_F_REFEREES'] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_F_LINESMAN';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '3';
        $export[] = $temp;
        $this->_sport_types_position_parent[strtoupper($option).'_F_REFEREES'] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_F_THIRD_OFFICIAL';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '3';
        $export[] = $temp;
        $this->_sport_types_position_parent[strtoupper($option).'_F_REFEREES'] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_F_FOURTH_OFFICIAL';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '3';
        $export[] = $temp;
        $this->_sport_types_position_parent[strtoupper($option).'_F_REFEREES'] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_F_FIFTH_OFFICIAL';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '3';
        $export[] = $temp;
        $this->_sport_types_position_parent[strtoupper($option).'_F_REFEREES'] = array_merge($export);
        $temp = new stdClass();
        $temp->name = strtoupper($option).'_F_VIDEO_UMPIRE';
        $temp->switch = 'persontype';
        $temp->parent = '0';
        $temp->content = '3';
        $export[] = $temp;
        $this->_sport_types_position_parent[strtoupper($option).'_F_REFEREES'] = array_merge($export);
        
        
        
        
        }
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool createSportTypeArray _sport_types_events<br><pre>'.print_r($this->_sport_types_events,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool createSportTypeArray _sport_types_position<br><pre>'.print_r($this->_sport_types_position,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool createSportTypeArray _sport_types_position_parent<br><pre>'.print_r($this->_sport_types_position_parent,true).'</pre>'),'Notice');
        
    }    
    
    
    function insertCountries()
    {
    $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
    require_once( JPATH_ADMINISTRATOR.'/components/'.$option.'/'. 'helpers' . DS . 'jinstallationhelper.php' );    
    $db = JFactory::getDBO();
    $db_table = JPATH_ADMINISTRATOR.'/components/'.$option.'/sql/countries.sql';
// echo '<br>'.$db_table.'<br>';
// $fileContent = JFile::read($db_table);
// $sql_teil = explode(";",$fileContent);

    $result = JInstallationHelper::populateDatabase($db, $db_table, $errors);
    if ( $result )
    {
    $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_INSERT_ERROR'),'Error');     
    }   
    else
    {
    $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_INSERT_SUCCESS'),'');     
    } 
    //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool insertCountries result<br><pre>'.print_r($result,true).'</pre>'),'Notice');    
    }
    
    function insertSportType($type)
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT',strtoupper($type)),'Notice');
        
        self::createSportTypeArray();
        
        // Get a db connection.
        $db = JFactory::getDbo();
        // Create a new query object.
        $query = $db->getQuery(true);
        // Insert columns.
        $columns = array('name','icon');
        // Insert values.
        $values = array('\''.'COM_SPORTSMANAGEMENT_ST_'.strtoupper($type).'\'','\''.'com_sportsmanagement/database/placeholders/placeholder_21.png'.'\'');
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query);
        
        if (!$db->query())
		{
			
            $mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool insertSportType<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			$result = false;
		}
        else
        {
        $mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT_SUCCESS',strtoupper($type)),'Notice');
        $sports_type_id = $db->insertid();
        $sports_type_name = 'COM_SPORTSMANAGEMENT_ST_'.strtoupper($type);
        self::addStandardForSportType($sports_type_name, $sports_type_id, $type);
        }
        
        
        
    }
    
    
    function addStandardForSportType($name, $id, $type)
{
    $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Get a db connection.
        $db = JFactory::getDbo();
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool addStandardForSportType name<br><pre>'.print_r($name,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool addStandardForSportType id<br><pre>'.print_r($id,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool addStandardForSportType events<br><pre>'.print_r($this->_sport_types_events[$type],true).'</pre>'),'Notice');
        
	$events_player		= array();
	$events_staff		= array();
	$events_referees	= array();
	$events_clubstaff	= array();
	$PlayersPositions	= array();
	$StaffPositions		= array();
	$RefereePositions	= array();
	$ClubStaffPositions	= array();

	$result				= false;
	
    // insert events
    $i = 0;
    foreach ( $this->_sport_types_events[$type] as $event )
    {
        $query = self::build_SelectQuery('eventtypes',$event->name,$id); 
        $db->setQuery($query);
        
        if ( !$object = $db->loadObject() )
		{
        $query				= self::build_InsertQuery_Event('eventtype',$event->name,$event->icon,$id,2);
         $db->setQuery($query);
			$result				= $db->query();
			$events_player[$i]	= $db->insertid();
			$events_staff[$i]		= $db->insertid();
			$events_clubstaff[$i]	= $db->insertid();
			$events_referees[$i]	= $db->insertid();
            }
            else
		{
		  $events_player[$i]	= $object->id;
			$events_staff[$i]		= $object->id;
			$events_clubstaff[$i]	= $object->id;
			$events_referees[$i]	= $object->id;
			
		}
        $mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_EVENTS_INSERT_SUCCESS',$event->name),'Notice');
        $i++;
    }
    
    // standardpositionen einfügen
    $i = 0;
    $j = 0;
    foreach ( $this->_sport_types_position[$type] as $position )
    {
    $query = self::build_SelectQuery('position',$position->name,$id); 
    $db->setQuery($query);    
    if (!$dbresult=$db->loadObject())
			{
				$query					= self::build_InsertQuery_Position('position',$position->name,$position->switch,$position->parent,$position->content,$id,1); 
                $db->setQuery($query);
				$result					= $db->query();
				$ParentID				= $db->insertid();
				$PlayersPositions[$i]	= $db->insertid();
			}
			else
			{
				$ParentID				= $dbresult->id;
				$PlayersPositions[$i]	= $dbresult->id;
			}
    
    $mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_POSITION_INSERT_SUCCESS',$position->name),'Notice');
    
    // parent position
    if ( isset($this->_sport_types_position_parent[$position->name])  )
    {
        foreach ( $this->_sport_types_position_parent[$position->name] as $parent )
        {
            $query = self::build_SelectQuery('position',$parent->name,$id); 
            $db->setQuery($query);
            if (!$object=$db->loadObject())
				{
					$query					= self::build_InsertQuery_Position('position',$parent->name,$parent->switch,$ParentID,$parent->content,$id,2); 
                    $db->setQuery($query);
					$result					= $db->query();
					$PlayersPositions[$j]	= $db->insertid();
				}
				else
				{
					$PlayersPositions[$j]	= $object->id;
				}
            $j++;
            
        $mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_SUCCESS',$parent->name),'Notice');    
            
        }    
    }
    
    
    
    
    $i++;
    }
    
    
    }
    
    function build_SelectQuery($tablename,$param1,$st_id = 0)
{
	$query="SELECT * FROM #__".COM_SPORTSMANAGEMENT_TABLE."_".$tablename." WHERE name='".$param1."' and sports_type_id = ".$st_id." ";
	return $query;
}

function build_InsertQuery_Position($tablename,$param1,$param2,$param3,$param4,$sports_type_id,$order_count)
{
	$alias=JFilterOutput::stringURLSafe($param1);
	$query="INSERT INTO #__".COM_SPORTSMANAGEMENT_TABLE."_".$tablename." (`name`,`alias`,`".$param2."`,`parent_id`,`sports_type_id`,`published`,`ordering`) VALUES ('".$param1."','".$alias."','".$param4."','".$param3."','".$sports_type_id."','1','".$order_count."')";
	return $query;
}

function build_InsertQuery_Event($tablename,$param1,$param2,$sports_type_id,$order_count)
{
	$alias=JFilterOutput::stringURLSafe($param1);
	$query="INSERT INTO #__".COM_SPORTSMANAGEMENT_TABLE."_".$tablename." (`name`,`alias`,`icon`,`sports_type_id`,`published`,`ordering`) VALUES ('".$param1."','".$alias."','".$param2."','".$sports_type_id."','1','".$order_count."')";
	return $query;
}

function build_InsertQuery_PositionEventType($param1,$param2)
{
	$query="	INSERT INTO	#__".COM_SPORTSMANAGEMENT_TABLE."_position_eventtype
				(`position_id`,`eventtype_id`)
				VALUES
				('".$param1."','".$param2."')";
	return $query;
}
    

}
