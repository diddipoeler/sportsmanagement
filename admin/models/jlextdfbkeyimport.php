<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jlextdfbkeyimport.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

//jimport( 'joomla.application.component.model' );
//require_once( JPATH_COMPONENT_SITE . DS. 'extensions' . DS. 'jlextdfbkey' . DS. 'admin' . DS. 'helpers' . DS . 'helper.php' );

$maxImportTime=480;

if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

/**
 * sportsmanagementModeljlextDfbkeyimport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeljlextDfbkeyimport extends JSMModelLegacy
{

/**
 * sportsmanagementModeljlextDfbkeyimport::_loadData()
 * 
 * @return void
 */
function _loadData()
	{
  
	}

/**
 * sportsmanagementModeljlextDfbkeyimport::_initData()
 * 
 * @return void
 */
function _initData()
	{

	}

/**
 * sportsmanagementModeljlextDfbkeyimport::getProjectType()
 * 
 * @param integer $project_id
 * @return
 */
function getProjectType($project_id = 0)
{
$this->jsmquery->clear();
$this->jsmquery->select('project_type');	
$this->jsmquery->from('#__sportsmanagement_project');
$this->jsmquery->where('id = ' . $project_id);	
try {
$this->jsmdb->setQuery( $this->jsmquery );
$project_type = $this->jsmdb->loadResult();
//JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' country <pre>'.print_r($country,true).'</pre>', 'warning');	
return $project_type;
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
    return false;
}
//return $country;
}
	

/**
 * sportsmanagementModeljlextDfbkeyimport::getCountry()
 * 
 * @param integer $project_id
 * @return
 */
function getCountry($project_id = 0)
{
$this->jsmquery->clear();
$this->jsmquery->select('l.country');	
$this->jsmquery->from('#__sportsmanagement_league as l');
$this->jsmquery->join('LEFT', '#__sportsmanagement_project as p on p.league_id = l.id');
$this->jsmquery->where('p.id = ' . $project_id);	
try {
$this->jsmdb->setQuery( $this->jsmquery );
$country = $this->jsmdb->loadResult();
//JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' country <pre>'.print_r($country,true).'</pre>', 'warning');	
return $country;
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
    return false;
}
//return $country;
}



	/**
	 * sportsmanagementModeljlextDfbkeyimport::getProjectteams()
	 * 
	 * @param integer $project_id
	 * @param integer $division_id
	 * @return
	 */
	function getProjectteams($project_id = 0, $division_id = 0)
	{
//		$option = JFactory::getApplication()->input->getCmd('option');
//		$app = JFactory::getApplication ();

JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' project_id <pre>'.print_r($project_id,true).'</pre>', 'warning');
JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' division_id <pre>'.print_r($division_id,true).'</pre>', 'warning');
		
		$this->jsmquery->clear();
    $this->jsmquery->select('pt.id AS value');
            $this->jsmquery->select('t.name AS text,t.notes');
            $this->jsmquery->from('#__sportsmanagement_team AS t');
            $this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
            $this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $this->jsmquery->where('pt.project_id = ' . $project_id);

		if ( $division_id )
		{
		$this->jsmquery->where('pt.division_id = ' . $division_id);	
		}
		$this->jsmdb->setQuery( $this->jsmquery );
		if ( !$result = $this->jsmdb->loadObjectList() )
		{
			//$this->setError( $this->_db->getErrorMsg() );
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->_db->getErrorMsg(),true).'</pre>'),'Error');
			return false;
		}
		else
		{
		$this->jsmdb->execute();
		$number = $this->jsmdb->getNumRows();		

JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' number <pre>'.print_r($number,true).'</pre>', 'warning');
JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' result <pre>'.print_r($result,true).'</pre>', 'warning');
			
		if ( $number > 0 )
		{
/*	
    if ($zahl % 2 != 0) {
echo "Die Zahl $zahl ist ungerade";
} else {
echo "Die Zahl $zahl ist gerade";
}
*/
    return $result;
    }
    else
    {
    return false;
    }
			
		}
	}

/**
 * sportsmanagementModeljlextDfbkeyimport::getDFBKey()
 * 
 * @param mixed $number
 * @param mixed $matchdays
 * @return
 */
function getDFBKey($number,$matchdays)
	{
	$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication ();
	$document	= JFactory::getDocument();
  
$project_id = $app->getUserState( "$option.pid", '0' );
	//$project_id = $app->getUserState( $option . 'project' );
	
	// gibt es zum land der liga schlüssel ?
    $country = $this->getCountry($project_id);
    $this->jsmquery->clear();
//$query = "SELECT l.country
//from #__".COM_SPORTSMANAGEMENT_TABLE."_league as l
//inner join #__".COM_SPORTSMANAGEMENT_TABLE."_project as p
//on p.league_id = l.id
//where p.id = '$project_id'
//";
//
//$this->_db->setQuery( $query );
//$country = $this->_db->loadResult();

	if ( $number % 2 == 0 )
	{
  }
  else
  {
  $number = $number + 1;
  }

$this->jsmquery->select('*');
$this->jsmquery->from('#__sportsmanagement_dfbkey');
$this->jsmquery->where('schluessel = ' . (int) $number );
$this->jsmquery->where('country LIKE '.$this->jsmdb->Quote(''.$country.'') );
	
	if ( $matchdays == 'ALL' )
	{
//  $query = "select *
//  from #__".COM_SPORTSMANAGEMENT_TABLE."_dfbkey
//  where schluessel = " . (int) $number . " 
//  and country like '".$country."' group by spieltag ";
  $this->jsmquery->group('spieltag');
  }
  elseif ( $matchdays == 'FIRST' )
  {
  $this->jsmquery->where('spieltag = 1');
//  $query = "select *
//  from #__".COM_SPORTSMANAGEMENT_TABLE."_dfbkey
//  where schluessel = " . (int) $number . "
//  and country like '".$country."' 
//  and spieltag = 1 ";
  }
	
	try{
	$this->jsmdb->setQuery( $this->jsmquery );
    $result = $this->jsmdb->loadObjectList();
//JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' result <pre>'.print_r($result ,true).'</pre>', 'warning');		
    return $result;
	} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
    return false;
}
//		if ( !$result = $this->_db->loadObjectList() )
//		{
//			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
//			return false;
//		}
//	  else
//	  {
//    return $result;
//
//    }
	
	}


  
  /**
   * sportsmanagementModeljlextDfbkeyimport::getMatchdays()
   * 
   * @param integer $project_id
   * @return
   */
  function getMatchdays($project_id = 0)
	{
	//$option = JFactory::getApplication()->input->getCmd('option');
//		$app = JFactory::getApplication ();
	
    $this->jsmquery->clear();
    $this->jsmquery->select('*');
$this->jsmquery->from('#__sportsmanagement_round');
$this->jsmquery->where('project_id = ' . (int)$project_id);

//	$query = 'select *
//  from #__'.COM_SPORTSMANAGEMENT_TABLE.'_round
//  where project_id = ' . (int) $projectid . '';
	
    try{
	$this->jsmdb->setQuery( $this->jsmquery );
    $result = $this->jsmdb->loadObjectList();
//JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' result <pre>'.print_r($result ,true).'</pre>', 'warning');			    
    return $result;
    } catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
    return false;
}
		//if ( !$result = $this->_db->loadObjectList() )
//		{
//			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
//			return false;
//		}
//	  else
//	  {
//    return $result;
//
//    }

	}
	
	
	/**
	 * sportsmanagementModeljlextDfbkeyimport::getMatches()
	 * 
	 * @param integer $project_id
	 * @param integer $division_id
	 * @return
	 */
	function getMatches($project_id = 0, $division_id = 0)
	{
//	   $option = JFactory::getApplication()->input->getCmd('option');
//		$app = JFactory::getApplication ();
//    $db = sportsmanagementHelper::getDBConnection();

$this->jsmquery->clear();
$this->jsmquery->select('*');
$this->jsmquery->from('#__sportsmanagement_round');
$this->jsmquery->where('project_id = ' . (int)$project_id);
    
// $query = 'select *
//  from #__'.COM_SPORTSMANAGEMENT_TABLE.'_round
//  where project_id = ' . (int) $projectid . '';

try{	
	$this->jsmdb->setQuery( $this->jsmquery );
  
   if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
		$result = $this->jsmdb->loadColumn();
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
		$result = $this->jsmdb->loadResultArray();
}
$rounds = implode(",",$result);
$this->jsmquery->clear();
$this->jsmquery->select('count(*)');
$this->jsmquery->from('#__sportsmanagement_match');
$this->jsmquery->where('round_id in (' . $rounds . ')' );
$this->jsmdb->setQuery( $this->jsmquery );
$count = $this->jsmdb->loadResult();
//JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' count <pre>'.print_r($count ,true).'</pre>', 'warning');			
return $count;

} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
    return false;
}



	//$result = $this->_db->loadResultArray();


/*	
	$rounds = implode(",",$result);
	$query = 'select count(*)
  from #__'.COM_SPORTSMANAGEMENT_TABLE.'_match
  where round_id in (' . $rounds . ')';
	
	$this->_db->setQuery( $query );
	
	$count = $this->_db->loadResult();
return $count;
*/


  }
	
    
    
	
	/**
	 * sportsmanagementModeljlextDfbkeyimport::getSchedule()
	 * 
	 * @param mixed $post
	 * @param integer $project_id
	 * @param integer $division_id
	 * @return
	 */
	function getSchedule($post = array(), $project_id = 0, $division_id = 0 )
	{
	$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication ();

/*	
echo '<pre>';
print_r($post);
echo '</pre>';
*/
 
 //echo 'getSchedule project_id -> '.$projectid.'<br>';
 
//$lfdnummer = 1;
foreach($post as $key => $element)
{
if (substr($key,0,10)=="chooseteam")
{
$tempteams=explode ("_",$key);
$chooseteam[$tempteams[1]]['projectteamid'] = $element;

$this->jsmquery->clear();
$this->jsmquery->select('team.name');
$this->jsmquery->from('#__sportsmanagement_team as team');
$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = team.id');
$this->jsmquery->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
$this->jsmquery->where('pt.id = ' . (int)$element);
if ( $division_id )
{
$this->jsmquery->where('pt.division_id = ' . $division_id);	
}
/*
$query = 'select team.name
  from #__'.COM_SPORTSMANAGEMENT_TABLE.'_team as team
  inner join #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team as pteam
  on team.id = pteam.team_id
  where pteam.id = ' . (int) $element . ' ';
*/
  $this->jsmdb->setQuery( $this->jsmquery );
  $chooseteam[$tempteams[1]]['teamname'] = $this->jsmdb->loadResult();
  
//$lfdnummer++;
}

}

//JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' chooseteam <pre>'.print_r($chooseteam ,true).'</pre>', 'warning');		
		
/*
echo '<pre>';
print_r($chooseteam);
echo '</pre>';
*/

$number = count($chooseteam);

//echo 'numbers '.$number.'<br>';

if ( $number % 2 == 0 )
	{
  }
  else
  {
  $number = $number + 1;
  }

$this->jsmquery->clear();
$this->jsmquery->select('dfb.*,jr.id, jr.round_date_first');
$this->jsmquery->from('#__sportsmanagement_dfbkey as dfb');
$this->jsmquery->join('INNER', '#__sportsmanagement_round as jr on dfb.spieltag = jr.roundcode');
$this->jsmquery->where('dfb.schluessel = ' . (int)$number);
$this->jsmquery->where('jr.project_id = ' . (int)$project_id);
$this->jsmquery->order('dfb.spielnummer');
/*
$query = 'select dfb.*,jr.id, jr.round_date_first
  from #__'.COM_SPORTSMANAGEMENT_TABLE.'_dfbkey as dfb
  inner join #__'.COM_SPORTSMANAGEMENT_TABLE.'_round as jr
  on dfb.spieltag = jr.roundcode
  where dfb.schluessel = ' . (int) $number . 
  ' and jr.project_id = '. $projectid .' order by dfb.spielnummer ';
*/
  $this->jsmdb->setQuery( $this->jsmquery );
  $dfbresult = $this->jsmdb->loadObjectList();

/*  
echo '<pre>';
print_r($dfbresult);
echo '</pre>';
*/

//JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' dfbresult <pre>'.print_r($dfbresult ,true).'</pre>', 'warning');		

$result = array();

foreach($dfbresult as $row) 
{

$teile = explode(",", $row->paarung);

if ( $chooseteam[$teile[0]]['projectteamid'] != 0 && $chooseteam[$teile[1]]['projectteamid'] != 0 )
{
$temp = new stdClass();
$temp->spieltag = $row->spieltag;
$temp->round_id = $row->id;
$temp->spielnummer = $row->spielnummer;
$temp->match_date = $row->round_date_first;
$temp->division_id = $division_id;	
$temp->projectteam1_id = $chooseteam[$teile[0]]['projectteamid'];
$temp->projectteam2_id = $chooseteam[$teile[1]]['projectteamid'];
$temp->projectteam1_name = $chooseteam[$teile[0]]['teamname'];
$temp->projectteam2_name = $chooseteam[$teile[1]]['teamname'];

$result[] = $temp;
$result = array_merge($result);

}

}

$this->savedfb = $result ;
//  JFactory::getApplication()->input->setVar( 'savedfb', $result,'post' );
//    JFactory::getApplication()->input->set( $result,'post' );
	return $result;
	}
	
    
    
	/**
	 * sportsmanagementModeljlextDfbkeyimport::checkTable()
	 * 
	 * @return void
	 */
	function checkTable()
  {
  //$app = JFactory::getApplication();
    $option = JFactory::getApplication()->input->getCmd('option');
    require_once( JPATH_ADMINISTRATOR.'/components/'.$option.'/'. 'helpers' . DS . 'jinstallationhelper.php' );    
    //$db = sportsmanagementHelper::getDBConnection();
    $db_table = JPATH_ADMINISTRATOR.'/components/'.$option.'/sql/dfbkeys.sql';

$this->jsmquery->clear();
$this->jsmquery->select('count(*) AS count');
$this->jsmquery->from('#__sportsmanagement_dfbkey');

		$this->jsmdb->setQuery($this->jsmquery);
		$result = $this->jsmdb->loadResult();
        
        if ( !$result )
        {
        $result = JInstallationHelper::populateDatabase($this->jsmdb, $db_table, $errors);    
            
        }
        


  }
  
  
}

?>
