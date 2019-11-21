<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jlxmlimport.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage jlxmlimport
 * 
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Filter\OutputFilter;

$option = Factory::getApplication()->input->getCmd('option');
$maxImportTime = ComponentHelper::getParams($option)->get('max_import_time',0);
if (empty($maxImportTime))
{
	$maxImportTime = 480;
}
if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

$maxImportMemory = ComponentHelper::getParams($option)->get('max_import_memory',0);
if (empty($maxImportMemory))
{
	$maxImportMemory = '150M';
}
if ((int)ini_get('memory_limit') < (int)$maxImportMemory){@ini_set('memory_limit',$maxImportMemory);}
require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.$option.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'databasetool.php');

/**
 * sportsmanagementModelJLXMLImport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementModelJLXMLImport extends BaseDatabaseModel
{
	var $_datas = array();
	var $_success_text = array();
	var $_league_id = 0;
	var $_season_id = 0;
    var $_project_id = 0;
	var $_sportstype_id = 0;
    var $_agegroup_id = 0;
	var $_template_id = 0;
	var $master_template = 0;
	var $timezone = 0;
	var $import_version = '';
	var $storeFailedColor = 'red';
	var $storeSuccessColor = 'green';
	var $existingInDbColor = 'orange';
    var $existingStaff = 'blue';
    var $show_debug_info = false;
    var $_league_new_country = '';
    var $_import_project_id = 0;
    
	/**
	 * sportsmanagementModelJLXMLImport::_getXml()
	 * 
	 * @return
	 */
	private function _getXml()
	{
	   $app = Factory::getApplication();
    $option = Factory::getApplication()->input->getCmd('option');
    
		if (File::exists(JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'sportsmanagement_import.jlg'))
		{
			
            if(version_compare(JVERSION,'3.0.0','ge'))
            {
            
            if (function_exists('simplexml_load_file'))
			{
				return @simplexml_load_file(JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'sportsmanagement_import.jlg','SimpleXMLElement',LIBXML_NOCDATA);
			}
             
            }
            else
            {
            
            if (function_exists('simplexml_load_file'))
			{
				return @simplexml_load_file(JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'sportsmanagement_import.jlg','SimpleXMLElement',LIBXML_NOCDATA);
			}
			else
			{
				Factory::getApplication()->enqueueMessage(Text::_('<a href="http://php.net/manual/en/book.simplexml.php" target="_blank">SimpleXML</a> does not exist on your system!'), 'error');
			}
            
            }
		}
		else
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR', 'Missing import file'), 'error');
		}
	}
    
    
    /**
     * sportsmanagementModelJLXMLImport::getDataUpdateImportID()
     * 
     * @return
     */
    public function getDataUpdateImportID()
    {
    $app = Factory::getApplication();
    $option = Factory::getApplication()->input->getCmd('option');
    $project_id = $app->getUserState( "$option.pid", '0' ); 
   
    if ( $project_id )
    {
    $model = BaseDatabaseModel::getInstance('project', 'sportsmanagementmodel');
    $update_project = $model->getProject($project_id);  
    return $update_project->import_project_id;  
    }
    else
    {
        return false;
    }
    
      
    }
    
    
    
    /**
     * sportsmanagementModelJLXMLImport::_importDataForSave()
     * 
     * @param mixed $datas
     * @param string $table
     * @return
     */
    public function _importDataForSave($datas=array(),$table='')
    {
    $p_returndata = new stdClass();             
    if ( $table )
    {    
    $columnArr = Factory::getDbo()->getTableColumns("#__sportsmanagement_".$table);    
    
foreach ($datas as $import => $value )
{
switch ($import)
{
case 'id':
break;
default:
if (array_key_exists($import, $columnArr)) {
$p_returndata->$import = $this->_getDataFromObject($datas,$import);
}
break;    
}   
}    
        
        
        
    }    
    return $p_returndata;    
    }
        
    /**
     * sportsmanagementModelJLXMLImport::getDataUpdate()
     * 
     * @return
     */
    public function getDataUpdate()
    {
        $my_text='';
        foreach ( $this->_datas['match'] as $key => $value )
        {
            $query=' SELECT	m.*,
							CASE m.time_present
							when NULL then NULL
							else DATE_FORMAT(m.time_present, "%H:%i")
							END AS time_present,
							t1.name AS hometeam, t1.id AS t1id,
							t2.name as awayteam, t2.id AS t2id,
							pt1.project_id,
							m.extended as matchextended
						FROM #__sportsmanagement_match AS m
						INNER JOIN #__sportsmanagement_project_team AS pt1 ON pt1.id=m.projectteam1_id
						INNER JOIN #__sportsmanagement_team AS t1 ON t1.id=pt1.team_id
						INNER JOIN #__sportsmanagement_project_team AS pt2 ON pt2.id=m.projectteam2_id
						INNER JOIN #__sportsmanagement_team AS t2 ON t2.id=pt2.team_id
						WHERE m.import_match_id = '.(int) $value->id;
			Factory::getDbo()->setQuery($query);
			$match_data = Factory::getDbo()->loadObject();
            $my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= Text::sprintf(	'Update Match: %1$s / Match: %2$s - %3$s / Result: %4$s - %5$s',
									'</span><strong>'.$match_data->id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
									"</span><strong>$match_data->hometeam</strong>",
									"<strong>$match_data->awayteam</strong>",
									"<strong>$value->team1_result</strong>",
									"<strong>$value->team2_result</strong>"
                                    );
					$my_text .= '<br />';
                    
     $update_match = Table::getInstance('Match','sportsmanagementTable');
     $match_id = (int) $match_data->id;
     $update_match->load($match_id);
     if ( $value->team1_result )
     {
     $update_match->team1_result = (int) $value->team1_result;
     $update_match->team2_result = (int) $value->team2_result;
     }
     
     if ( !$update_match->store() )
	{
	   $my_text .= '<span style="color:'.$this->storeFailedColor. '"> nicht gesichert '.' - '.$match_data->match_date.'</span>';
       $my_text .= '<br />';
	}
	else
	{
	   $my_text .= '<span style="color:'.$this->storeSuccessColor.'"> gesichert '.' - '.$match_data->match_date.'</span>';
       $my_text .= '<br />';
	}
            
        }
        $this->_success_text['Update match data:'] = $my_text;
        return $this->_success_text;
    }

	
	/**
	 * sportsmanagementModelJLXMLImport::getData()
	 * 
	 * @param mixed $post
	 * @return
	 */
	public function getData($post = array() )
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
		if ( isset($post['filter_season']) )
		{
        $this->_season_id = $post['filter_season'];
		}
		else
		{
$this->_season_id = 0;			
		}

  //$post = Factory::getApplication()->input->post->getArray(array());
  //$country = $post['country'];		
//        $this->_agegroup_id = $post['agegroup_id'];
//       Factory::getApplication()->enqueueMessage('altersgruppe '.$this->_agegroup_id, 'error');
       
        $result = NULL;
        
        $this->_import_project_id = $app->getUserState($option.'projectidimport'); ;
        $this->pl_import = $app->getUserState($option.'pltree'); ;
        
        $importelanska = $app->getUserState($option.'importelanska');		
		$country = $app->getUserState($option.'country');
		$agegroup = $app->getUserState($option.'agegroup');
        $season_id = $app->getUserState($option.'seasons');

        libxml_use_internal_errors(true);
		if ( !$xmlData = $this->_getXml() )
		{
			$errorFound = false;
            Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR', 'Load of the importfile failed:'), 'error');
			foreach(libxml_get_errors() as $error)
			{
                Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR', $error->message), 'error');
				$errorFound = true;
			}
			if (!$errorFound)
            {
                Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR', 'Unknown error :-('), 'error');
            }
		}
        
		$i=0;
		$j=0;
		$k=0;
		$l=0;
		$m=0;
		$n=0;
		$o=0;
		$p=0;
		$q=0;
		$r=0;
		$s=0;
		$t=0;
		$u=0;
		$v=0;
		$w=0;
		$x=0;
		$y=0;
		$z=0;
		$mp=0;
		$ms=0;
		$mr=0;
		$me=0;
		$pe=0;
		$et=0;
		$ps=0;
		$mss=0;
		$mst=0;
		$tto=0;
		$ttn=0;
		$ttm=0;
		$tt=0;
      
        // ist die xmldatei gelesen machen wir weiter
		if ((isset($xmlData->record)) && (is_object($xmlData->record)))
		{
			foreach ($xmlData->record as $value)
			{
				// collect the project data of a .jlg file of JoomLeague <1.5x
				if ($xmlData->record[$i]['object'] == 'JoomLeagueVersion')
				{
					$this->_datas['exportversion'] = $xmlData->record[$i];
				}

				// collect the project data of a .jlg file of JoomLeague <1.5x
				if ($xmlData->record[$i]['object'] == 'JoomLeague')
				{
					$this->_datas['project'] = $xmlData->record[$i];
					$this->import_version = 'OLD';
                    //$this->import_version='NEW';
                    Factory::getApplication()->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_RENDERING_093'), '');
				}

				// collect the project data of a .jlg file of JoomLeague 1.5x
				if ($xmlData->record[$i]['object'] == 'JoomLeague15')
				{
					$this->_datas['project'] = $xmlData->record[$i];
					$this->import_version = 'NEW';
                    Factory::getApplication()->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_RENDERING_15'), '');
				}

				// collect the project data of a .jlg file of JoomLeague 1.5x
				if ($xmlData->record[$i]['object'] == 'JoomLeague20')
				{
					$this->_datas['project'] = $xmlData->record[$i];
					$this->import_version = 'NEW';
                    Factory::getApplication()->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_RENDERING_20'), '');
				}

				// collect the division data
				if ($xmlData->record[$i]['object']=='LeagueDivision')
				{
					$this->_datas['division'][$j]=$xmlData->record[$i];
					$j++;
				}

				// collect the club data
				if ($xmlData->record[$i]['object']=='Club')
				{
					$this->_datas['club'][$k]=$xmlData->record[$i];
					$k++;
				}

				// collect the team data
				if ($xmlData->record[$i]['object']=='JL_Team')
				{
					$this->_datas['team'][$l]=$xmlData->record[$i];
					$l++;
				}

				// collect the projectteam data
				if ($xmlData->record[$i]['object']=='ProjectTeam')
				{
					$this->_datas['projectteam'][$m]=$xmlData->record[$i];
					$m++;
				}

				// collect the projectteam data of old export file / Here TeamTool instead of projectteam
				if ($xmlData->record[$i]['object']=='TeamTool')
				{
				    // für jl version 0.93
					$this->_datas['teamtool'][$m]=$xmlData->record[$i];
                    //$this->_datas['projectteam'][$m]=$xmlData->record[$i];
					$m++;
				}

				// collect the round data
				if ($xmlData->record[$i]['object']=='Round')
				{
					$this->_datas['round'][$n]=$xmlData->record[$i];
					$n++;
				}

				// collect the match data
				if ($xmlData->record[$i]['object']=='Match')
				{
					$this->_datas['match'][$o]=$xmlData->record[$i];
					$o++;
				}

				// collect the playgrounds data
				if ($xmlData->record[$i]['object']=='Playground')
				{
					$this->_datas['playground'][$p]=$xmlData->record[$i];
					$p++;
				}

				// collect the template data
				if ($xmlData->record[$i]['object']=='Template')
				{
					$this->_datas['template'][$q]=$xmlData->record[$i];
					$q++;
				}

				// collect the events data
				if ($xmlData->record[$i]['object']=='EventType')
				{
					$this->_datas['event'][$et]=$xmlData->record[$i];
					$et++;
				}

				// collect the positions data
				if ($xmlData->record[$i]['object']=='Position')
				{
					$this->_datas['position'][$t]=$xmlData->record[$i];
					$t++;
				}

				// collect the positions data
				if ($xmlData->record[$i]['object']=='ParentPosition')
				{
					$this->_datas['parentposition'][$z]=$xmlData->record[$i];
					$z++;
				}

				// collect the League data
				if ($xmlData->record[$i]['object']=='League')
				{
					$this->_datas['league']=$xmlData->record[$i];
				}

				// collect the Season data
				if ($xmlData->record[$i]['object']=='Season')
				{
					$this->_datas['season']=$xmlData->record[$i];
				}

				// collect the SportsType data
				if ($xmlData->record[$i]['object']=='SportsType')
				{
					$this->_datas['sportstype']=$xmlData->record[$i];
				}

				// collect the projectreferee data
				if ($xmlData->record[$i]['object']=='ProjectReferee')
				{
					$this->_datas['projectreferee'][$w]=$xmlData->record[$i];
					$w++;
				}

				// collect the projectposition data
				if ($xmlData->record[$i]['object']=='ProjectPosition')
				{
					$this->_datas['projectposition'][$x]=$xmlData->record[$i];
					$x++;
				}

				// collect the person data
				if ($xmlData->record[$i]['object']=='Person')
				{
					$this->_datas['person'][$y]=$xmlData->record[$i];
					$y++;
				}

				// collect the TeamPlayer data
				if ($xmlData->record[$i]['object']=='TeamPlayer')
				{
					$this->_datas['teamplayer'][$v]=$xmlData->record[$i];
					$v++;
				}

				// collect the TeamStaff data
				if ($xmlData->record[$i]['object']=='TeamStaff')
				{
					$this->_datas['teamstaff'][$u]=$xmlData->record[$i];
					$u++;
				}

				// collect the TeamTraining data
				if ($xmlData->record[$i]['object']=='TeamTraining')
				{
					$this->_datas['teamtraining'][$tt]=$xmlData->record[$i];
					$tt++;
				}

				// collect the MatchPlayer data
				if ($xmlData->record[$i]['object']=='MatchPlayer')
				{
					$this->_datas['matchplayer'][$mp]=$xmlData->record[$i];
					$mp++;
				}

				// collect the MatchStaff data
				if ($xmlData->record[$i]['object']=='MatchStaff')
				{
					$this->_datas['matchstaff'][$ms]=$xmlData->record[$i];
					$ms++;
				}

				// collect the MatchReferee data
				if ($xmlData->record[$i]['object']=='MatchReferee')
				{
					$this->_datas['matchreferee'][$mr]=$xmlData->record[$i];
					$mr++;
				}

				// collect the MatchEvent data
				if ($xmlData->record[$i]['object']=='MatchEvent')
				{
					$this->_datas['matchevent'][$me]=$xmlData->record[$i];
					$me++;
				}

				// collect the PositionEventType data
				if ($xmlData->record[$i]['object']=='PositionEventType')
				{
					$this->_datas['positioneventtype'][$pe]=$xmlData->record[$i];
					$pe++;
				}

				// collect the Statistic data
				if ($xmlData->record[$i]['object']=='Statistic')
				{
					$this->_datas['statistic'][$s]=$xmlData->record[$i];
					$s++;
				}

				// collect the PositionStatistic data
				if ($xmlData->record[$i]['object']=='PositionStatistic')
				{
					$this->_datas['positionstatistic'][$ps]=$xmlData->record[$i];
					$ps++;
				}

				// collect the MatchStaffStatistic data
				if ($xmlData->record[$i]['object']=='MatchStaffStatistic')
				{
					$this->_datas['matchstaffstatistic'][$mss]=$xmlData->record[$i];
					$mss++;
				}

				// collect the MatchStatistic data
				if ($xmlData->record[$i]['object']=='MatchStatistic')
				{
					$this->_datas['matchstatistic'][$mst]=$xmlData->record[$i];
					$mst++;
				}

/**
 * collect the Treeto data
 */
				if ($xmlData->record[$i]['object']=='Treeto')
				{
					$this->_datas['treeto'][$tto]=$xmlData->record[$i];
					$tto++;
				}

/**
 * collect the TreetoNode data
 */
				if ($xmlData->record[$i]['object']=='TreetoNode')
				{
					$this->_datas['treetonode'][$ttn]=$xmlData->record[$i];
					$ttn++;
				}

/**
 * collect the TreetoMatch data
 */
				if ($xmlData->record[$i]['object']=='TreetoMatch')
				{
					$this->_datas['treetomatch'][$ttm]=$xmlData->record[$i];
					$ttm++;
				}

				$i++;
			}

/** ############################ anpassungen anfang ########################################################### */           
            /** bilderpfade anpassen */
            if ( isset($this->_datas['person']) )
            {
            foreach ($this->_datas['person'] as $temppicture)
            {
                $temppicture->picture = str_replace('com_joomleague', $option, $temppicture->picture);
                $temppicture->picture = str_replace('media', 'images', $temppicture->picture);
                if (preg_match("/placeholders/i", $temppicture->picture) || empty($temppicture->picture) ) 
                {
                      $temppicture->picture = ComponentHelper::getParams($option)->get('ph_player','');
                }
            }    
            }
            
            if ( isset($this->_datas['teamplayer']) )
            {
            foreach ($this->_datas['teamplayer'] as $temppicture)
            {
                $temppicture->picture = str_replace('com_joomleague', $option, $temppicture->picture);
                $temppicture->picture = str_replace('media', 'images', $temppicture->picture);
                if (preg_match("/placeholders/i", $temppicture->picture) || empty($temppicture->picture) ) 
                {
                      $temppicture->picture = ComponentHelper::getParams($option)->get('ph_player','');
                }
            }    
            }
           
            if ( isset($this->_datas['teamstaff']) )
            {
            foreach ($this->_datas['teamstaff'] as $temppicture)
            {
                $temppicture->picture = str_replace('com_joomleague', $option, $temppicture->picture);
                $temppicture->picture = str_replace('media', 'images', $temppicture->picture);
                if (preg_match("/placeholders/i", $temppicture->picture) || empty($temppicture->picture) ) 
                {
                      $temppicture->picture = ComponentHelper::getParams($option)->get('ph_player','');
                }
            }    
            }
            
            if ( isset($this->_datas['projectreferee']) )
            {
            foreach ($this->_datas['projectreferee'] as $temppicture)
            {
                $temppicture->picture = str_replace('com_joomleague', $option, $temppicture->picture);
                $temppicture->picture = str_replace('media', 'images', $temppicture->picture);
                if (preg_match("/placeholders/i", $temppicture->picture) || empty($temppicture->picture) ) 
                {
                      $temppicture->picture = ComponentHelper::getParams($option)->get('ph_player','');
                }
            }    
            }
            
            if ( isset($this->_datas['team']) )
            {
            foreach ($this->_datas['team'] as $temppicture)
            {
                $temppicture->picture = str_replace('com_joomleague', $option, $temppicture->picture);
                $temppicture->picture = str_replace('media', 'images', $temppicture->picture);
                if ( preg_match("/placeholders/i", $temppicture->picture) || empty($temppicture->picture) ) 
                {
                      $temppicture->picture = ComponentHelper::getParams($option)->get('ph_team','');
                }
            }    
            }
            
            if ( isset($this->_datas['projectteam']) )
            {
            foreach ($this->_datas['projectteam'] as $temppicture)
            {
                $temppicture->picture = str_replace('com_joomleague', $option, $temppicture->picture);
                $temppicture->picture = str_replace('media', 'images', $temppicture->picture);
                if (preg_match("/placeholders/i", $temppicture->picture) || empty($temppicture->picture) ) 
                {
                      $temppicture->picture = ComponentHelper::getParams($option)->get('ph_team','');
                }
            }    
            }
            
            if ( isset($this->_datas['club']) )
            {
            foreach ($this->_datas['club'] as $temppicture)
            {
                $temppicture->logo_big = str_replace('com_joomleague', $option, $temppicture->logo_big);
                $temppicture->logo_big = str_replace('media', 'images', $temppicture->logo_big);
                
                $temppicture->logo_middle = str_replace('com_joomleague', $option, $temppicture->logo_middle);
                $temppicture->logo_middle = str_replace('media', 'images', $temppicture->logo_middle);
                
                $temppicture->logo_small = str_replace('com_joomleague', $option, $temppicture->logo_small);
                $temppicture->logo_small = str_replace('media', 'images', $temppicture->logo_small);
                if (preg_match("/placeholders/i", $temppicture->logo_big) || empty($temppicture->logo_big) ) 
                {
                      $temppicture->logo_big = ComponentHelper::getParams($option)->get('ph_logo_big','');
                }
                if (preg_match("/placeholders/i", $temppicture->logo_middle) || empty($temppicture->logo_middle) ) 
                {
                      $temppicture->logo_middle = ComponentHelper::getParams($option)->get('ph_logo_medium','');
                }
                if (preg_match("/placeholders/i", $temppicture->logo_small) || empty($temppicture->logo_small) ) 
                {
                      $temppicture->logo_small = ComponentHelper::getParams($option)->get('ph_logo_small','');
                }
                
            }    
            }
    
            if ( isset($this->_datas['league']) )
            {
            $this->_league_new_country = (string) $this->_datas['league']->country;
            }
            
/**
 * textelemente bereinigen
 */
            if ( isset($this->_datas['sportstype']) )
            {
            $this->_datas['sportstype']->name = str_replace('COM_JOOMLEAGUE', strtoupper($option), $this->_datas['sportstype']->name);
          
/**
 * ereignisse um die textelemente bereinigen
 */
            $temp = explode("_",$this->_datas['sportstype']->name);
            $sport_type_name = array_pop($temp);
            }
            else
            {
            $sport_type_name = '';    
            }
            
            if ( isset($this->_datas['event']) )
            {
            foreach ($this->_datas['event'] as $event)
            {
                $event->name = str_replace('COM_JOOMLEAGUE', strtoupper($option).'_'.$sport_type_name, $event->name);
            }
            }
            
/**
 * klartexte in textvariable umwandeln.
 */
            $query = "SELECT name,alias FROM #__sportsmanagement_position WHERE name LIKE '%".$sport_type_name."%'";
            Factory::getDbo()->setQuery($query);
		    sportsmanagementModeldatabasetool::runJoomlaQuery();
		    if (Factory::getDbo()->getAffectedRows())
		    {
			$result = Factory::getDbo()->loadObjectList();
		    }
            
            if ( isset($this->_datas['position']) )
            {
            foreach ($this->_datas['position'] as $position)
            {
                $position->name = str_replace('COM_JOOMLEAGUE', strtoupper($option).'_'.$sport_type_name, $position->name);
                if ( $result )
                {
                    foreach ( $result as $pos )
                    {
                        if ( $position->name == Text::_($pos->name) )
                        {
                            $position->name = $pos->name;
                            $position->alias = $pos->alias;
                        }
                    }
                }
            }
            }
            
            if ( isset($this->_datas['parentposition']) )
            {
            foreach ($this->_datas['parentposition'] as $position)
            {
                $position->name = str_replace('COM_JOOMLEAGUE', strtoupper($option).'_'.$sport_type_name, $position->name);
                if ( $result )
                {
                    foreach ( $result as $pos )
                    {
                        if ( $position->name == Text::_($pos->name) )
                        {
                            $position->name = $pos->name;
                            $position->alias = $pos->alias;
                        }
                    }
                }
            }
            }
           
            if ( isset($this->_datas['person']) )
            {
/**
 * jetzt werden die positionen in den personen überprüft.
 */
            foreach ( $this->_datas['person'] as $person )
            {
                $pos_error = true;
                if ( isset($this->_datas['position']) )
                {
                foreach ( $this->_datas['position'] as $position )
                {
                    if ( $person->position_id == $position->id )
                    {
                        $pos_error = false;
                    }
                }
                }
                
                if ( isset($this->_datas['parentposition']) )
                {    
                foreach ( $this->_datas['parentposition'] as $parentposition )
                {
                    if ( $person->position_id == $parentposition->id )
                    {
                        $pos_error = false;
                    }
                }
                }
                
                if ( $pos_error )
                {
                    //$app->enqueueMessage(Text::sprintf('Spieler %1$s %2$s hat fehlende Importposition-ID ( %3$s )',$person->firstname,$person->lastname,$person->position_id ),'Error');
                } 
                        
            }
            }
            
/**
 * länder bei den spielorten vervollständigen
 * bilderpfad ändern
 */
            if ( isset($this->_datas['playground']) )
            {
            foreach ($this->_datas['playground'] as $playground )
            {
                if ( $playground->country == '' || empty($playground->country) )
                {
                    $playground->country = 'DEU';
                }
                $playground->picture = str_replace('com_joomleague', $option, $playground->picture);
                $playground->picture = str_replace('media', 'images', $playground->picture);
                if (preg_match("/placeholders/i", $playground->picture) || empty($playground->picture) ) 
                {
                      $playground->picture = ComponentHelper::getParams($option)->get('ph_stadium','');
                }
            }    
            }
// ############################ anpassungen ende ###########################################################            

			if (isset($this->_datas['teamtool']) && is_array($this->_datas['teamtool']) && count($this->_datas['teamtool']) > 0)
			{
				$i=0;
				$m=0;
				foreach ($xmlData->record as $value)
				{
					if ($xmlData->record[$i]['object']=='TeamTool')
					{
						$this->_datas['projectteam'][$m]=$xmlData->record[$i];
						$m++;
					}
					$i++;
				}
			}

			return $this->_datas;
		}
		else
		{
		 if ( $importelanska )
		{
		$xmlData = $this->_getXml();
		$arrayelanska = json_decode(json_encode((array)$xmlData), TRUE);

$mdl = BaseDatabaseModel::getInstance('seasons', 'sportsmanagementModel');
$seasons_name = $mdl->getSeasonName($season_id); 
 
if ( array_key_exists('all_seasons', $arrayelanska['dataroot']) ) 
{        
$object = new stdClass();
$object->id = 1;	
$object->name = 'Soccer';			 
$this->_datas['sportstype'] = $object;    
$object = new stdClass();
$object->id = $season_id;	
$object->name = $seasons_name;			 
$this->_datas['season'] = $object;
for($a=0; $a < sizeof($arrayelanska['dataroot']['all_seasons']);$a++)
{        
if ( $arrayelanska['dataroot']['all_seasons'][$a]['season'] == $seasons_name )
{
//echo '<pre>'.print_r($arrayelanska['dataroot']['all_seasons'][$a],true).'</pre>';  
if ( !isset($this->_datas['league']) )
{
$object = new stdClass();
$object->id = $arrayelanska['dataroot']['all_seasons'][$a]['league_id'];	
$object->name = $arrayelanska['dataroot']['all_seasons'][$a]['league'];
$object->country = $country;
$object->agegroup_id = $agegroup;
$object->short_name = $arrayelanska['dataroot']['all_seasons'][$a]['league'];	
$object->middle_name = $arrayelanska['dataroot']['all_seasons'][$a]['league'];
$this->_datas['league'] = $object;
}
if ( !isset($this->_datas['project']) )
{
$object = new stdClass();
$object->id = 1;	
$object->name = $arrayelanska['dataroot']['all_seasons'][$a]['league'].' '.$arrayelanska['dataroot']['all_seasons'][$a]['season'];	
$object->agegroup_id = $agegroup;
$object->master_template = 0;
$object->league_id = $arrayelanska['dataroot']['all_seasons'][$a]['league_id'];
$object->season_id = $season_id;
$object->sports_type_id = 1;
$this->_datas['project'] = $object;
}
/** heim */
$object = new stdClass();
$object->id = $arrayelanska['dataroot']['all_seasons'][$a]['home_id'];	
$object->name = $arrayelanska['dataroot']['all_seasons'][$a]['home'];
$object->country = $country;
$object->standard_playground = 0;
$object->logo_big = ComponentHelper::getParams($option)->get('ph_logo_big','');
$object->logo_middle = ComponentHelper::getParams($option)->get('ph_logo_medium','');
$object->logo_small = ComponentHelper::getParams($option)->get('ph_logo_small','');
$this->_datas['club'][$object->id] = $object;
$object = new stdClass();
$object->id = $arrayelanska['dataroot']['all_seasons'][$a]['home_id'];	
$object->club_id = $arrayelanska['dataroot']['all_seasons'][$a]['home_id'];	
$object->name = $arrayelanska['dataroot']['all_seasons'][$a]['home'];	
$object->short_name = $arrayelanska['dataroot']['all_seasons'][$a]['home'];	
$object->middle_name = $arrayelanska['dataroot']['all_seasons'][$a]['home'];	
$object->info = '';
$object->agegroup_id = $agegroup;
$object->picture = ComponentHelper::getParams($option)->get('ph_team','');
$this->_datas['team'][$object->id] = $object;	
$object->id = $arrayelanska['dataroot']['all_seasons'][$a]['home_id'];	
$object->team_id = $arrayelanska['dataroot']['all_seasons'][$a]['home_id'];
$object->picture = ComponentHelper::getParams($option)->get('ph_team','');	
$this->_datas['projectteam'][$object->id] = $object;
/** gast */
$object = new stdClass();
$object->id = $arrayelanska['dataroot']['all_seasons'][$a]['away_id'];	
$object->name = $arrayelanska['dataroot']['all_seasons'][$a]['away'];
$object->country = $country;
$object->standard_playground = 0;
$object->logo_big = ComponentHelper::getParams($option)->get('ph_logo_big','');
$object->logo_middle = ComponentHelper::getParams($option)->get('ph_logo_medium','');
$object->logo_small = ComponentHelper::getParams($option)->get('ph_logo_small','');
$this->_datas['club'][$object->id] = $object;
$object = new stdClass();
$object->id = $arrayelanska['dataroot']['all_seasons'][$a]['away_id'];	
$object->club_id = $arrayelanska['dataroot']['all_seasons'][$a]['away_id'];	
$object->name = $arrayelanska['dataroot']['all_seasons'][$a]['away'];	
$object->short_name = $arrayelanska['dataroot']['all_seasons'][$a]['away'];	
$object->middle_name = $arrayelanska['dataroot']['all_seasons'][$a]['away'];	
$object->info = '';
$object->agegroup_id = $agegroup;
$object->picture = ComponentHelper::getParams($option)->get('ph_team','');
$this->_datas['team'][$object->id] = $object;	
$object->id = $arrayelanska['dataroot']['all_seasons'][$a]['away_id'];	
$object->team_id = $arrayelanska['dataroot']['all_seasons'][$a]['away_id'];
$object->picture = ComponentHelper::getParams($option)->get('ph_team','');	
$this->_datas['projectteam'][$object->id] = $object;

$object = new stdClass();
$object->id = $arrayelanska['dataroot']['all_seasons'][$a]['round'];
$object->name = $arrayelanska['dataroot']['all_seasons'][$a]['round'].'.Krog';
$this->_datas['round'][$object->id] = $object;

$object = new stdClass();
$object->id = $arrayelanska['dataroot']['all_seasons'][$a]['match_id'];
$object->round_id = $arrayelanska['dataroot']['all_seasons'][$a]['round'];	
$object->match_number = $arrayelanska['dataroot']['all_seasons'][$a]['ID'];	
$object->projectteam1_id = $arrayelanska['dataroot']['all_seasons'][$a]['home_id'];	
$object->projectteam2_id = $arrayelanska['dataroot']['all_seasons'][$a]['away_id'];	
$object->team1_result = $arrayelanska['dataroot']['all_seasons'][$a]['home_goals'];
$object->team2_result = $arrayelanska['dataroot']['all_seasons'][$a]['away_goals'];
$object->crowd = $arrayelanska['dataroot']['all_seasons'][$a]['spectators'];
$object->playground_id = 0;
$teile = explode("T", $arrayelanska['dataroot']['all_seasons'][$a]['match_date']);
$object->match_date = $teile[0];
$this->_datas['match'][$object->id] = $object;

  
}    

}        	
$object = new stdClass();
$object->version = '2.4.00';	
$object->exportRoutine = '2010-09-23 15:00:00';
$object->exportDate = '2010-09-23';
$object->exportTime = '2010-09-23';
$object->exportSystem = '1. Èlanska liga MNZ Maribor';			 
$this->_datas['exportversion'] = $object;
//echo 'season id <pre>'.print_r($season_id,true).'</pre>';
			 
             
}             
else
{      
    /*
$object = new stdClass();
$object->id = 1;	
$object->name = 'Soccer';			 
$this->_datas['sportstype'] = $object;			 
$object = new stdClass();
$object->id = 1;	
$object->name = $arrayelanska['liga'];
$object->country = $country;
$object->agegroup_id = $agegroup;
$object->short_name = $arrayelanska['liga'];	
$object->middle_name = $arrayelanska['liga'];	
$this->_datas['league'] = $object;
$object = new stdClass();
$object->id = 1;	
$object->name = $arrayelanska['sifra_lige'];
$this->_datas['season'] = $object;
$object = new stdClass();
$object->id = 1;	
$object->name = $arrayelanska['liga'];	
$object->agegroup_id = $agegroup;
$object->master_template = 0;

$object->league_id = 1;
$object->season_id = 1;
$object->sports_type_id = 1;

$this->_datas['project'] = $object;
$object = new stdClass();
$object->version = '2.4.00';	
$object->exportRoutine = '2010-09-23 15:00:00';
$object->exportDate = '2010-09-23';
$object->exportTime = '2010-09-23';
$object->exportSystem = '1. Èlanska liga MNZ Maribor';			 
$this->_datas['exportversion'] = $object;
			 
for($a=0; $a < sizeof($arrayelanska['zapisniki']['zapisnik']);$a++)
{
$object = new stdClass();
$object->id = $arrayelanska['zapisniki']['zapisnik'][$a]['sifra_kluba_domaci'];	
$object->name = $arrayelanska['zapisniki']['zapisnik'][$a]['domaci'];
$object->country = $country;
$this->_datas['club'][$object->id] = $object;
$object = new stdClass();
$object->id = $arrayelanska['zapisniki']['zapisnik'][$a]['sifra_kluba_domaci'];	
$object->club_id = $arrayelanska['zapisniki']['zapisnik'][$a]['sifra_kluba_domaci'];	
$object->name = $arrayelanska['zapisniki']['zapisnik'][$a]['domaci'];	
$object->short_name = $arrayelanska['zapisniki']['zapisnik'][$a]['domaci'];	
$object->middle_name = $arrayelanska['zapisniki']['zapisnik'][$a]['domaci'];	
$object->info = '';
$object->agegroup_id = $agegroup;
$this->_datas['team'][$object->id] = $object;	
$object->id = $arrayelanska['zapisniki']['zapisnik'][$a]['sifra_kluba_domaci'];	
$object->team_id = $arrayelanska['zapisniki']['zapisnik'][$a]['sifra_kluba_domaci'];	
$this->_datas['projectteam'][$object->id] = $object;	
	
$object = new stdClass();
$object->id = $arrayelanska['zapisniki']['zapisnik'][$a]['sifra_kluba_gosti'];	
$object->name = $arrayelanska['zapisniki']['zapisnik'][$a]['gosti'];
$object->country = $country;	
$this->_datas['club'][$object->id] = $object;
$object = new stdClass();
$object->id = $arrayelanska['zapisniki']['zapisnik'][$a]['sifra_kluba_gosti'];	
$object->club_id = $arrayelanska['zapisniki']['zapisnik'][$a]['sifra_kluba_gosti'];	
$object->name = $arrayelanska['zapisniki']['zapisnik'][$a]['gosti'];	
$object->short_name = $arrayelanska['zapisniki']['zapisnik'][$a]['gosti'];	
$object->middle_name = $arrayelanska['zapisniki']['zapisnik'][$a]['gosti'];	
$object->info = '';	
$object->agegroup_id = $agegroup;
$this->_datas['team'][$object->id] = $object;
$object->id = $arrayelanska['zapisniki']['zapisnik'][$a]['sifra_kluba_gosti'];	
$object->team_id = $arrayelanska['zapisniki']['zapisnik'][$a]['sifra_kluba_gosti'];	
$this->_datas['projectteam'][$object->id] = $object;
	
$object = new stdClass();
$object->id = $arrayelanska['zapisniki']['zapisnik'][$a]['krog'];
$object->name = $arrayelanska['zapisniki']['zapisnik'][$a]['krog'].'.Krog';
$this->_datas['round'][$object->id] = $object;

$object = new stdClass();
$object->id = $arrayelanska['zapisniki']['zapisnik'][$a]['sifra_zapisnika'];
$object->round_id = $arrayelanska['zapisniki']['zapisnik'][$a]['krog'];	
$object->match_number = $arrayelanska['zapisniki']['zapisnik'][$a]['tekma_id'];	
$object->projectteam1_id = $arrayelanska['zapisniki']['zapisnik'][$a]['sifra_kluba_domaci'];	
$object->projectteam2_id = $arrayelanska['zapisniki']['zapisnik'][$a]['sifra_kluba_gosti'];	
$teile = explode(":", $arrayelanska['zapisniki']['zapisnik'][$a]['rezultat']);	
$object->team1_result = trim($teile[0]);
$object->team2_result = trim($teile[1]);
$this->_datas['match'][$object->id] = $object;

}
*/
}

$this->import_version = 'NEW';
return $this->_datas;
		}
			else
			{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR', 'Something is wrong inside the import file'), 'error');
			return false;
			}
		}
	}

	/**
	 * sportsmanagementModelJLXMLImport::getUserList()
	 * 
	 * @param bool $is_admin
	 * @return
	 */
	public function getUserList($is_admin=false)
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       $query->select('id, username');
       $query->from('#__users');
       
		if ($is_admin==true)
		{
		  $query->where("( usertype LIKE 'Super Administrator' OR usertype LIKE 'Administrator') ");
		}
        $query->order('username ASC');
		Factory::getDbo()->setQuery($query);
		return Factory::getDbo()->loadObjectList();
	}

	/**
	 * sportsmanagementModelJLXMLImport::getTemplateList()
	 * 
	 * @return
	 */
	public function getTemplateList()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       $query->select('id as value, name as text');
       $query->from('#__sportsmanagement_project');
       $query->where('master_template = 0');  
       $query->order('name ASC');
       
		Factory::getDbo()->setQuery($query);
		return Factory::getDbo()->loadObjectList();
	}


	/**
	 * sportsmanagementModelJLXMLImport::getNewClubList()
	 * 
	 * @return
	 */
	public function getNewClubList()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       $query->select('id, name, country');
       $query->from('#__sportsmanagement_club');
       $query->order('name ASC');
       
		Factory::getDbo()->setQuery($query);
		return Factory::getDbo()->loadObjectList();
	}

	/**
	 * sportsmanagementModelJLXMLImport::getNewClubListSelect()
	 * 
	 * @return
	 */
	public function getNewClubListSelect()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       $query->select('id AS value, name AS text, country');
       $query->from('#__sportsmanagement_club');
       $query->order('name');

		Factory::getDbo()->setQuery($query);
		if ($results=Factory::getDbo()->loadObjectList())
		{
			return $results;
		}
		return false;
	}

	/**
	 * sportsmanagementModelJLXMLImport::getClubAndTeamList()
	 * 
	 * @return
	 */
	public function getClubAndTeamList()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       $query->select('c.id, c.name AS club_name, t.name AS team_name, c.country');
       $query->from('#__sportsmanagement_club AS c');
       $query->join('INNER', '#__sportsmanagement_team AS t ON t.club_id=c.id');
       $query->order('c.name, t.name ASC');
       
		Factory::getDbo()->setQuery($query);
		return Factory::getDbo()->loadObjectList();
	}

	/**
	 * sportsmanagementModelJLXMLImport::getClubAndTeamListSelect()
	 * 
	 * @return
	 */
	public function getClubAndTeamListSelect()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       $query->select('t.id AS value, CONCAT(c.name, " - ", t.name , " (", t.info , ")" ) AS text, t.club_id, c.name AS club_name, t.name AS team_name, c.country');
       $query->from('#__sportsmanagement_club AS c');
       $query->join('INNER', '#__sportsmanagement_team AS t ON t.club_id=c.id');
       $query->order('c.name, t.name');
       
		Factory::getDbo()->setQuery($query);
		if ($results=Factory::getDbo()->loadObjectList())
		{
			return $results;
		}
		return false;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_deleteImportFile()
	 * Should be called as the last function in importData() to delete
	 * @return
	 */
	private function _deleteImportFile()
	{
		$importFileName = JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'sportsmanagement_import.jlg';
		if (File::exists($importFileName))
        {
            File::delete($importFileName);
        }
		return true;
	}

	/**
	 * _getDataFromObject
	 *
	 * Get data from object
	 *
	 * @param object $obj object where we find the key
	 * @param string $key key what we find in the object
	 *
	 * @return void
	 */
	private function _getDataFromObject(&$obj,$key)
	{
		if (is_object($obj))
		{
			$t_array=get_object_vars($obj);

			if (array_key_exists($key,$t_array))
			{
				return $t_array[$key];
			}
			return false;
		}
		return false;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_getPersonFromTeamStaff()
	 * 
	 * @param mixed $teamstaff_id
	 * @return
	 */
	private function _getPersonFromTeamStaff($teamstaff_id)
	{
	   $query = Factory::getDbo()->getQuery(true);
       // Select some fields
        $query->select('ppl.firstname,ppl.lastname');
		// From the table
		$query->from('#__sportsmanagement_person as ppl');
        $query->join('INNER', '#__sportsmanagement_team_staff AS r on r.person_id = ppl.id');
        $query->where('r.id = '.(int)$teamstaff_id);  
        
		Factory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if (Factory::getDbo()->getAffectedRows())
		{
			$result = Factory::getDbo()->loadObject();
			return $result;
		}
		return (object)array("firstname" => "", "lastname" => "");
	}

	/**
	 * sportsmanagementModelJLXMLImport::_getPersonFromTeamPlayer()
	 * 
	 * @param mixed $teamplayer_id
	 * @return
	 */
	private function _getPersonFromTeamPlayer($teamplayer_id)
	{
	   $query = Factory::getDbo()->getQuery(true);
       // Select some fields
        $query->select('ppl.firstname,ppl.lastname');
		// From the table
		$query->from('#__sportsmanagement_person as ppl');
        $query->join('INNER', '#__sportsmanagement_team_player AS r on r.person_id = ppl.id');
        $query->where('r.id = '.(int)$teamplayer_id);   
               
		Factory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if (Factory::getDbo()->getAffectedRows())
		{
			$result = Factory::getDbo()->loadObject();
			return $result;
		}
		return (object)array("firstname" => "", "lastname" => "");
	}

	/**
	 * sportsmanagementModelJLXMLImport::_getPersonFromProjectReferee()
	 * 
	 * @param mixed $project_referee_id
	 * @return
	 */
	private function _getPersonFromProjectReferee($project_referee_id)
	{
	   $query = Factory::getDbo()->getQuery(true);
       // Select some fields
        $query->select('ppl.firstname,ppl.lastname');
		// From the table
		$query->from('#__sportsmanagement_person as ppl');
        $query->join('INNER', '#__sportsmanagement_project_referee AS pr on pr.person_id = ppl.id');
        $query->where('pr.id = '.(int)$project_referee_id);   
               
		Factory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if (Factory::getDbo()->getAffectedRows())
		{
			$result = Factory::getDbo()->loadObject();
			return $result;
		}
		return (object)array("firstname" => "", "lastname" => "");
	}

	/**
	 * sportsmanagementModelJLXMLImport::_getPersonName()
	 * 
	 * @param mixed $person_id
	 * @return
	 */
	private function _getPersonName($person_id)
	{
		$query = Factory::getDbo()->getQuery(true);
        // Select some fields
        $query->select('lastname,firstname');
		// From the seasons table
		$query->from('#__sportsmanagement_person');
        $query->where('id = '.(int)$person_id);
       
		Factory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if (Factory::getDbo()->getAffectedRows())
		{
			$result=Factory::getDbo()->loadObject();
			return $result;
		}
		return (object)array("firstname" => "", "lastname" => "");
	}

	/**
	 * sportsmanagementModelJLXMLImport::_getClubName()
	 * 
	 * @param mixed $club_id
	 * @return
	 */
	private function _getClubName($club_id)
	{
	   // Create a new query object.		
		//$db = sportsmanagementHelper::getDBConnection();
        $query = Factory::getDbo()->getQuery(true);
       // Select some fields
        $query->select('name');
		// From the seasons table
		$query->from('#__sportsmanagement_club');
        $query->where('id = '.(int)$club_id);
		Factory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if (Factory::getDbo()->getAffectedRows())
		{
			$result = Factory::getDbo()->loadResult();
			return $result;
		}
		return '#Error in _getClubName#';
	}

	/**
	 * sportsmanagementModelJLXMLImport::_getTeamName()
	 * 
	 * @param mixed $team_id
	 * @return
	 */
	private function _getTeamName($team_id)
	{
	   // Create a new query object.		
		//$db = sportsmanagementHelper::getDBConnection();
        $query = Factory::getDbo()->getQuery(true);
        // Select some fields
        $query->select('t.name');
		// From the table
		$query->from('#__sportsmanagement_team as t');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
        $query->join('INNER', '#__sportsmanagement_project_team AS pt on pt.team_id = st.id');  
        $query->where('pt.id = '.(int)$team_id);    
		
        Factory::getDbo()->setQuery($query);
        sportsmanagementModeldatabasetool::runJoomlaQuery();

		if ($object = Factory::getDbo()->loadObject())
		{
			return $object->name;
		}
		return __FUNCTION__.' '.__LINE__.'#Error in _getTeamName# team_id -> '.$team_id.'<br>';
	}

	/**
	 * sportsmanagementModelJLXMLImport::_getTeamName2()
	 * 
	 * @param mixed $team_id
	 * @return
	 */
	private function _getTeamName2($team_id=0,$season_team_id=0)
	{
        $query = Factory::getDbo()->getQuery(true);
        $query->select('t.name');
	$query->from('#__sportsmanagement_team as t');
	if ( $season_team_id )
	{
	$query->join('INNER',' #__sportsmanagement_season_team_id st ON st.team_id = t.id ');	
	$query->where('st.id = '.(int)$season_team_id);
	}
	else
	{
        $query->where('t.id = '.(int)$team_id);
	}
	Factory::getDbo()->setQuery($query);
	sportsmanagementModeldatabasetool::runJoomlaQuery();
        $result = Factory::getDbo()->loadResult();
	if ($result)
	{
		return $result;
	}
        else
        {
		return __FUNCTION__.' '.__LINE__.'#Error in _getTeamName2# team_id -> '.$team_id.'<br>';
        }
	}

	/**
	 * sportsmanagementModelJLXMLImport::_getPlaygroundRecord()
	 * 
	 * @param mixed $id
	 * @return
	 */
	private function _getPlaygroundRecord($id)
	{
	   $query = Factory::getDbo()->getQuery(true);
       
       // Select some fields
        $query->select('*');
		// From the table
		$query->from('#__sportsmanagement_playground');
        $query->where('id = '.(int)$id);
        
		Factory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if ( $object = Factory::getDbo()->loadObject())
        {
            return $object;
        }
		return null;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_updatePlaygroundRecord()
	 * 
	 * @param mixed $club_id
	 * @param mixed $playground_id
	 * @return
	 */
	private function _updatePlaygroundRecord($club_id,$playground_id)
	{
	   // Create an object for the record we are going to update.
        $object = new stdClass();
        // Must be a valid primary key value.
        $object->id = $playground_id;
        $object->club_id = $club_id;
        // Update their details in the table using id as the primary key.
        $result = Factory::getDbo()->updateObject('#__sportsmanagement_playground', $object, 'id');
        
		return $result;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_getRoundName()
	 * 
	 * @param mixed $round_id
	 * @return
	 */
	private function _getRoundName($round_id)
	{
	   $app = Factory::getApplication();
       // Create a new query object.		
		//$db = sportsmanagementHelper::getDBConnection();
		$query = Factory::getDbo()->getQuery(true);
        $query->select('name');
        $query->from('#__sportsmanagement_round ');
        $query->where('id = '.(int)$round_id);
        		
        Factory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if (Factory::getDbo()->getAffectedRows())
		{
			$result = Factory::getDbo()->loadResult();
			return $result;
		}
		return null;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_getProjectPositionName()
	 * 
	 * @param mixed $project_position_id
	 * @return
	 */
	private function _getProjectPositionName($project_position_id)
	{
	   $app = Factory::getApplication();
       // Create a new query object.		
		//$db = sportsmanagementHelper::getDBConnection();
		$query = Factory::getDbo()->getQuery(true);
        $query->select('name');
        $query->from('#__sportsmanagement_project_position AS ppos ');
        $query->join('INNER','#__sportsmanagement_position AS pos ON pos.id = ppos.position_id');
        $query->where('ppos.id = '.(int)$project_position_id);
        
		Factory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if ($object = Factory::getDbo()->loadResult())
        {
            return $object;
        }
        
		return null;
	}

	/**
	 * _getCountryByOldid
	 *
	 * Get ISO-Code for countries to convert in old jlg import file
	 *
	 * @param object $obj object where we find the key
	 * @param string $key key what we find in the object
	 *
	 * @access public
	 * @since  1.5
	 *
	 * @return void
	 */
	public function getCountryByOldid()
	{
		$country['0']='';
		$country['1']='AFG';
		$country['2']='ALB';
		$country['3']='DZA';
		$country['4']='ASM';
		$country['5']='AND';
		$country['6']='AGO';
		$country['7']='AIA';
		$country['8']='ATA';
		$country['9']='ATG';
		$country['10']='ARG';
		$country['11']='ARM';
		$country['12']='ABW';
		$country['13']='AUS';
		$country['14']='AUT';
		$country['15']='AZE';
		$country['16']='BHS';
		$country['17']='BHR';
		$country['18']='BGD';
		$country['19']='BRB';
		$country['20']='BLR';
		$country['21']='BEL';
		$country['22']='BLZ';
		$country['23']='BEN';
		$country['24']='BMU';
		$country['25']='BTN';
		$country['26']='BOL';
		$country['27']='BIH';
		$country['28']='BWA';
		$country['29']='BVT';
		$country['30']='BRA';
		$country['31']='IOT';
		$country['32']='BRN';
		$country['33']='BGR';
		$country['34']='BFA';
		$country['35']='BDI';
		$country['36']='KHM';
		$country['37']='CMR';
		$country['38']='CAN';
		$country['39']='CPV';
		$country['40']='CYM';
		$country['41']='CAF';
		$country['42']='TCD';
		$country['43']='CHL';
		$country['44']='CHN';
		$country['45']='CXR';
		$country['46']='CCK';
		$country['47']='COL';
		$country['48']='COM';
		$country['49']='COG';
		$country['50']='COK';
		$country['51']='CRI';
		$country['52']='CIV';
		$country['53']='HRV';
		$country['54']='CUB';
		$country['55']='CYP';
		$country['56']='CZE';
		$country['57']='DNK';
		$country['58']='DJI';
		$country['59']='DMA';
		$country['60']='DOM';
		$country['61']='TMP';
		$country['62']='ECU';
		$country['63']='EGY';
		$country['64']='SLV';
		$country['65']='GNQ';
		$country['66']='ERI';
		$country['67']='EST';
		$country['68']='ETH';
		$country['69']='FLK';
		$country['70']='FRO';
		$country['71']='FJI';
		$country['72']='FIN';
		$country['73']='FRA';
		$country['74']='FXX';
		$country['75']='GUF';
		$country['76']='PYF';
		$country['77']='ATF';
		$country['78']='GAB';
		$country['79']='GMB';
		$country['80']='GEO';
		$country['81']='DEU';
		$country['82']='GHA';
		$country['83']='GIB';
		$country['84']='GRC';
		$country['85']='GRL';
		$country['86']='GRD';
		$country['87']='GLP';
		$country['88']='GUM';
		$country['89']='GTM';
		$country['90']='GIN';
		$country['91']='GNB';
		$country['92']='GUY';
		$country['93']='HTI';
		$country['94']='HMD';
		$country['95']='HND';
		$country['96']='HKG';
		$country['97']='HUN';
		$country['98']='ISL';
		$country['99']='IND';
		$country['100']='IDN';
		$country['101']='IRN';
		$country['102']='IRQ';
		$country['103']='IRL';
		$country['104']='ISR';
		$country['105']='ITA';
		$country['106']='JAM';
		$country['107']='JPN';
		$country['108']='JOR';
		$country['109']='KAZ';
		$country['110']='KEN';
		$country['111']='KIR';
		$country['112']='PRK';
		$country['113']='KOR';
		$country['114']='KWT';
		$country['115']='KGZ';
		$country['116']='LAO';
		$country['117']='LVA';
		$country['118']='LBN';
		$country['119']='LSO';
		$country['120']='LBR';
		$country['121']='LBY';
		$country['122']='LIE';
		$country['123']='LTU';
		$country['124']='LUX';
		$country['125']='MAC';
		$country['126']='MKD';
		$country['127']='MDG';
		$country['128']='MWI';
		$country['129']='MYS';
		$country['130']='MDV';
		$country['131']='MLI';
		$country['132']='MLT';
		$country['133']='MHL';
		$country['134']='MTQ';
		$country['135']='MRT';
		$country['136']='MUS';
		$country['137']='MYT';
		$country['138']='MEX';
		$country['139']='FSM';
		$country['140']='MDA';
		$country['141']='MCO';
		$country['142']='MNG';
		$country['143']='MSR';
		$country['144']='MAR';
		$country['145']='MOZ';
		$country['146']='MMR';
		$country['147']='NAM';
		$country['148']='NRU';
		$country['149']='NPL';
		$country['150']='NLD';
		$country['151']='ANT';
		$country['152']='NCL';
		$country['153']='NZL';
		$country['154']='NIC';
		$country['155']='NER';
		$country['156']='NGA';
		$country['157']='NIU';
		$country['158']='NFK';
		$country['159']='MNP';
		$country['160']='NOR';
		$country['161']='OMN';
		$country['162']='PAK';
		$country['163']='PLW';
		$country['164']='PAN';
		$country['165']='PNG';
		$country['166']='PRY';
		$country['167']='PER';
		$country['168']='PHL';
		$country['169']='PCN';
		$country['170']='POL';
		$country['171']='PRT';
		$country['172']='PRI';
		$country['173']='QAT';
		$country['174']='REU';
		$country['175']='ROM';
		$country['176']='RUS';
		$country['177']='RWA';
		$country['178']='KNA';
		$country['179']='LCA';
		$country['180']='VCT';
		$country['181']='WSM';
		$country['182']='SMR';
		$country['183']='STP';
		$country['184']='SAU';
		$country['185']='SEN';
		$country['186']='SYC';
		$country['187']='SLE';
		$country['188']='SGP';
		$country['189']='SVK';
		$country['190']='SVN';
		$country['191']='SLB';
		$country['192']='SOM';
		$country['193']='ZAF';
		$country['194']='SGS';
		$country['195']='ESP';
		$country['196']='LKA';
		$country['197']='SHN';
		$country['198']='SPM';
		$country['199']='SDN';
		$country['200']='SUR';
		$country['201']='SJM';
		$country['202']='SWZ';
		$country['203']='SWE';
		$country['204']='CHE';
		$country['205']='SYR';
		$country['206']='TWN';
		$country['207']='TJK';
		$country['208']='TZA';
		$country['209']='THA';
		$country['210']='TGO';
		$country['211']='TKL';
		$country['212']='TON';
		$country['213']='TTO';
		$country['214']='TUN';
		$country['215']='TUR';
		$country['216']='TKM';
		$country['217']='TCA';
		$country['218']='TUV';
		$country['219']='UGA';
		$country['220']='UKR';
		$country['221']='ARE';
		$country['222']='GBR';
		$country['223']='USA';
		$country['224']='UMI';
		$country['225']='URY';
		$country['226']='UZB';
		$country['227']='VUT';
		$country['228']='VAT';
		$country['229']='VEN';
		$country['230']='VNM';
		$country['231']='VGB';
		$country['232']='VIR';
		$country['233']='WLF';
		$country['234']='ESH';
		$country['235']='YEM';
		$country['238']='ZMB';
		$country['239']='ZWE';
		$country['240']='ENG';
		$country['241']='SCO';
		$country['242']='WAL';
		$country['243']='ALA';
		$country['244']='NEI';
		$country['245']='MNE';
		$country['246']='SRB';
		return $country;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_checkProject()
	 * 
	 * @return
	 */
	private function _checkProject()
	{
	   $app = Factory::getApplication();
       $db = Factory::getDbo();
       //$db = sportsmanagementHelper::getDBConnection();
       $query = $db->getQuery(true);

        $query->clear();
          // Select some fields
        $query->select('id');
		// From the table
		$query->from('#__sportsmanagement_project');
        $query->where('name LIKE '.$db->Quote(''.addslashes(stripslashes($this->_name)).''));
			$db->setQuery($query);
       
                sportsmanagementModeldatabasetool::runJoomlaQuery();

		if ($db->getNumRows() > 0)
        {
            return false;
        }
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_getObjectName()
	 * 
	 * @param mixed $tableName
	 * @param mixed $id
	 * @param string $usedFieldName
	 * @return
	 */
	private function _getObjectName($tableName,$id,$usedFieldName='')
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       
		$fieldName=($usedFieldName=='') ? 'name' : $usedFieldName;
        $query->clear();
          // Select some fields
        $query->select($fieldName);
		// From the table
		$query->from('#__sportsmanagement_'.$tableName);
        $query->where('id = '.$id);
		Factory::getDbo()->setQuery($query);
		if ($result=Factory::getDbo()->loadResult())
        {
            return $result;
        }
		return Text::sprintf('Item with ID [%1$s] not found inside [#__sportsmanagement_%2$s]',$id,$tableName);
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importSportsType()
	 * 
	 * @return
	 */
	private function _importSportsType()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       
		$my_text = '';
if( !isset($this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]) ) {
$this->_success_text = array(Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0') => "");	
}		
		if (!empty($this->_sportstype_new))
		{
		  $query->clear();
        $query->select('id');
		$query->from('#__sportsmanagement_sports_type');
        $query->where('name LIKE '.Factory::getDbo()->Quote(''.addslashes(stripslashes($this->_sportstype_new)).''));
			Factory::getDbo()->setQuery($query);

                sportsmanagementModeldatabasetool::runJoomlaQuery();

			if ($sportstypeObject=Factory::getDbo()->loadObject())
			{
				$this->_sportstype_id = $sportstypeObject->id;
				$my_text .= '<span style="color:orange">';
				$my_text .= Text::sprintf('Using existing sportstype data: %1$s',"</span><strong>$this->_sportstype_new</strong>");
				$my_text .= '<br />';
			}
			else
			{
                $p_sportstype = new stdClass();
				$p_sportstype->name = trim($this->_sportstype_new);

try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_sports_type', $p_league);
$insertID = Factory::getDbo()->insertid();
$this->_sportstype_id = $insertID;
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf('Created new sportstype data: %1$s',"</span><strong>$this->_sportstype_new</strong>");
$my_text .= '<br />';
}
catch (Exception $e){
$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
$my_text .= Text::sprintf('Sportstypename: %1$s',Text::_($this->_sportstype_new)).'<br />';
$my_text .= __LINE__.' '.$e->getMessage().'<br />';
}                
                
                
                
			}
		}
		else
		{
			$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
			$my_text .= Text::sprintf('Using existing sportstype data: %1$s',
						'</span><strong>'.Text::_($this->_getObjectName('sports_type',$this->_sportstype_id)).'</strong>');
			$my_text .= '<br />';
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importLeague()
	 * 
	 * @return
	 */
	private function _importLeague()
	{
	   $app = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
       $query = Factory::getDbo()->getQuery(true);
       
		$my_text = '';
if( !isset($this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]) ) {
    $this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
}		
		if ( !empty($this->_league_new) )
		{
		  $query->clear();
        $query->select('id,name,country');
		$query->from('#__sportsmanagement_league');
        $query->where('name LIKE '.Factory::getDbo()->Quote(''.addslashes(stripslashes($this->_league_new)).''));
			Factory::getDbo()->setQuery($query);
            sportsmanagementModeldatabasetool::runJoomlaQuery();
                
			if ( $leagueObject = Factory::getDbo()->loadObject() )
			{
				$this->_league_id = $leagueObject->id;
				$my_text .= '<span style="color:orange">';
				$my_text .= Text::sprintf(Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_1'),"</span><strong>$this->_league_new</strong>");
				$my_text .= '<br />';
			}
			else
			{
                $p_league = new stdClass();
		$p_league->name = substr(trim($this->_league_new),0,74);
		$p_league->alias = substr(OutputFilter::stringURLSafe($this->_league_new),0,74);
                $p_league->short_name = substr(OutputFilter::stringURLSafe($this->_league_new),0,14);
                $p_league->middle_name = substr(OutputFilter::stringURLSafe($this->_league_new),0,24);
		$p_league->country = $this->_league_new_country;
                $p_league->sports_type_id = $this->_sportstype_id;
		$p_league->agegroup_id = $this->_agegroup_id;
		$p_league->picture = ComponentHelper::getParams($option)->get('ph_logo_big','');
                
try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_league', $p_league);
$insertID = Factory::getDbo()->insertid();
$this->_league_id = $insertID;
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf('Created new league data: %1$s',"</span><strong>$this->_league_new</strong>");
$my_text .= '<br />';                    
}
catch (Exception $e){
$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
$my_text .= Text::sprintf('Leaguenname: %1$s',$this->_league_new).'<br />';
$my_text .= __LINE__.' '.$e->getMessage().'<br />';
}
				
			}
		}
		else
		{
			$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
			$my_text .= Text::sprintf(Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_1'),
						'</span><strong>'.$this->_getObjectName('league',$this->_league_id).'</strong>');
			$my_text .= '<br />';
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importSeason()
	 * 
	 * @return
	 */
	private function _importSeason()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       
		$my_text = '';
if( !isset($this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]) ) {
    $this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
}		
		if (!empty($this->_season_new))
		{
		  $query->clear();
        $query->select('id');
		$query->from('#__sportsmanagement_season');
        $query->where('name LIKE '.Factory::getDbo()->Quote(''.addslashes(stripslashes($this->_season_new)).''));
			Factory::getDbo()->setQuery($query);
                sportsmanagementModeldatabasetool::runJoomlaQuery();

			if ( $seasonObject=Factory::getDbo()->loadObject() )
			{
				$this->_season_id=$seasonObject->id;
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= Text::sprintf('Using existing season data: %1$s',"</span><strong>$this->_season_new</strong>");
				$my_text .= '<br />';
			}
			else
			{
				
                $p_season = new stdClass();
				$p_season->name = trim($this->_season_new);
				$p_season->alias = OutputFilter::stringURLSafe($this->_season_new);

try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_season', $p_season);
$insertID = Factory::getDbo()->insertid();
$this->_season_id = $insertID;
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf('Created new season data: %1$s',"</span><strong>$this->_season_new</strong>");
$my_text .= '<br />';                   
}
catch (Exception $e){
$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
$my_text .= __LINE__.' '.Text::sprintf('Seasonname: %1$s',$this->_season_new).'<br />';
}                
                
			}
		}
		else
		{
			$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
			$my_text .= Text::sprintf(	'Using existing season data: %1$s',
										'</span><strong>'.$this->_getObjectName('season',$this->_season_id).'</strong>');
			$my_text .= '<br />';
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importEvents()
	 * 
	 * @return
	 */
	private function _importEvents()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);

		$my_text = '';
		if (!isset($this->_datas['event']) || count($this->_datas['event'])==0){return true;}
		if ((!isset($this->_neweventsid) || count($this->_neweventsid)==0) &&
			(!isset($this->_dbeventsid) || count($this->_dbeventsid)==0)){return true;}
		if (!empty($this->_dbeventsid))
		{
			foreach ($this->_dbeventsid AS $key => $id)
			{
				$oldID = $this->_getDataFromObject($this->_datas['event'][$key],'id');
				$this->_convertEventID[$oldID] = $id;
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= Text::sprintf('Using existing event data: %1$s',
							'</span><strong>'.Text::_($this->_getObjectName('eventtype',$id)).'</strong>');
				$my_text .= '<br />';
			}
		}

		if (!empty($this->_neweventsid))
		{
			foreach ($this->_neweventsid AS $key => $id)
			{
                $p_eventtype = new stdClass();
				$import_event = $this->_datas['event'][$key];
				$oldID = $this->_getDataFromObject($import_event,'id');
                
foreach ($import_event as $import => $value )
{
switch ($import)
{
case 'id':
break;
case 'name':
$p_eventtype->name = trim($this->_neweventsname[$key]);
break;
default:
$p_eventtype->$import = $this->_getDataFromObject($import_event,$import);    
break;    
}   
}                
$p_eventtype->sports_type_id = $this->_sportstype_id;				
$p_eventtype->alias = OutputFilter::stringURLSafe($this->_getDataFromObject($p_eventtype,'name'));
                
                $query->clear();
        $query->select('id,name');
		$query->from('#__sportsmanagement_eventtype');
        $query->where('name LIKE '.Factory::getDbo()->Quote(''.addslashes(stripslashes($p_eventtype->name)).''));
        
				Factory::getDbo()->setQuery($query); 
                sportsmanagementModeldatabasetool::runJoomlaQuery();
				if ($object=Factory::getDbo()->loadObject())
				{
					$this->_convertEventID[$oldID] = $object->id;
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= Text::sprintf('Using existing eventtype data: %1$s','</span><strong>'.Text::_($object->name).'</strong>');
					$my_text .= '<br />';
				}
				else
				{
                    
try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_eventtype', $p_eventtype);
$insertID = Factory::getDbo()->insertid();
$this->_convertEventID[$oldID] = $insertID;
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf('Created new eventtype data: %1$s','</span><strong>'.Text::_($p_eventtype->name).'</strong>');
$my_text .= '<br />';                  
}
catch (Exception $e){
$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
$my_text .= __LINE__.'error on event import: ';
$my_text .= $oldID.'<br />';
$my_text .= $e->getMessage().'<br />';
}	
                    
				}
			}
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}


	/**
	 * sportsmanagementModelJLXMLImport::_importStatistics()
	 * 
	 * @return
	 */
	private function _importStatistics()
	{

		$my_text='';
		if (!isset($this->_datas['statistic']) || count($this->_datas['statistic'])==0){return true;}
		if ((!isset($this->_newstatisticsid) || count($this->_newstatisticsid)==0) &&
			(!isset($this->_dbstatisticsid) || count($this->_dbstatisticsid)==0)){return true;}

		if (!empty($this->_dbstatisticsid))
		{
			foreach ($this->_dbstatisticsid AS $key => $id)
			{
				$oldID=$this->_getDataFromObject($this->_datas['statistic'][$key],'id');
				$this->_convertStatisticID[$oldID]=$id;
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= Text::sprintf(	'Using existing statistic data: %1$s',
											'</span><strong>'.Text::_($this->_getObjectName('statistic',$id)).'</strong>');
				$my_text .= '<br />';
			}
		}

		if (!empty($this->_newstatisticsid))
		{
			foreach ($this->_newstatisticsid AS $key => $id)
			{
				
                $mdl = BaseDatabaseModel::getInstance("statistic", "sportsmanagementModel");
                $p_statistic = $mdl->getTable();
                
				$import_statistic=$this->_datas['statistic'][$key];
				$oldID=$this->_getDataFromObject($import_statistic,'id');
				$alias=$this->_getDataFromObject($import_statistic,'alias');
				$p_statistic->set('name',trim($this->_newstatisticsname[$key]));
				$p_statistic->set('short',$this->_getDataFromObject($import_statistic,'short'));
				$p_statistic->set('icon',$this->_getDataFromObject($import_statistic,'icon'));
				$p_statistic->set('class',$this->_getDataFromObject($import_statistic,'class'));
				$p_statistic->set('calculated',$this->_getDataFromObject($import_statistic,'calculated'));
				$p_statistic->set('params',$this->_getDataFromObject($import_statistic,'params'));
				$p_statistic->set('baseparams',$this->_getDataFromObject($import_statistic,'baseparams'));
				$p_statistic->set('note',$this->_getDataFromObject($import_statistic,'note'));
				if ((isset($alias)) && (trim($alias)!=''))
				{
					$p_statistic->set('alias',$alias);
				}
				else
				{
					$p_statistic->set('alias',OutputFilter::stringURLSafe($this->_getDataFromObject($p_statistic,'name')));
				}
				$query="SELECT * FROM #__".COM_SPORTSMANAGEMENT_TABLE."_statistic WHERE name='".addslashes(stripslashes($p_statistic->name))."' AND class='".addslashes(stripslashes($p_statistic->class))."'";
				Factory::getDbo()->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery();
				if ($object=Factory::getDbo()->loadObject())
				{
					$this->_convertStatisticID[$oldID]=$object->id;
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= Text::sprintf('Using existing statistic data: %1$s','</span><strong>'.Text::_($object->name).'</strong>');
					$my_text .= '<br />';
				}
				else
				{
					if ($p_statistic->store()===false)
					{
						$my_text .= 'error on statistic import: ';
						$my_text .= $oldID;
						$this->_success_text['Importing general statistic data:']=$my_text;
                        //sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, Factory::getDbo()->getErrorMsg(), __LINE__);
					}
					else
					{
						$insertID=Factory::getDbo()->insertid();
						$this->_convertStatisticID[$oldID]=$insertID;
						$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= Text::sprintf('Created new statistic data: %1$s','</span><strong>'.Text::_($p_statistic->name).'</strong>');
						$my_text .= '<br />';
					}
				}
			}
		}
//$this->dump_variable("this->_convertStatisticID", $this->_convertStatisticID);
		$this->_success_text['Importing statistic data:']=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importParentPositions()
	 * 
	 * @return
	 */
	private function _importParentPositions()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       
		$my_text = '';
		if (!isset($this->_datas['parentposition']) || count($this->_datas['parentposition'])==0){return true;}
		if ((!isset($this->_newparentpositionsid) || count($this->_newparentpositionsid)==0) &&
			(!isset($this->_dbparentpositionsid) || count($this->_dbparentpositionsid)==0)){return true;}

		if (!empty($this->_dbparentpositionsid))
		{
			foreach ($this->_dbparentpositionsid AS $key => $id)
			{
				$oldID = $this->_getDataFromObject($this->_datas['parentposition'][$key],'id');
				$this->_convertParentPositionID[$oldID]=$id;
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= Text::sprintf(	'Using existing parentposition data: %1$s',
											'</span><strong>'.Text::_($this->_getObjectName('position',$id)).'</strong>');
				$my_text .= '<br />';
			}
		}

		if (!empty($this->_newparentpositionsid))
		{
			foreach ($this->_newparentpositionsid AS $key => $id)
			{
                $p_position = new stdClass();
				$import_position = $this->_datas['parentposition'][$key];
				$oldID = $this->_getDataFromObject($import_position,'id');
				$p_position->name = trim($this->_newparentpositionsname[$key]);
				$p_position->parent_id = 0;
				$p_position->persontype = $this->_getDataFromObject($import_position,'persontype') ? $this->_getDataFromObject($import_position,'persontype') : 1;
				$p_position->sports_type_id = $this->_sportstype_id;
				$p_position->published = 1;
				$p_position->alias = OutputFilter::stringURLSafe($this->_getDataFromObject($p_position,'name'));
				
                $query->clear();
        $query->select('id,name');
		$query->from('#__sportsmanagement_position');
        $query->where('name LIKE '.Factory::getDbo()->Quote(''.addslashes(stripslashes($p_position->name)).''));
        $query->where('parent_id = 0');
        
                Factory::getDbo()->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery();
				if (Factory::getDbo()->getAffectedRows())
				{
				    $row = Table::getInstance('position', 'sportsmanagementTable');
					$row->load(Factory::getDbo()->loadResult());
					$this->_convertParentPositionID[$oldID] = $row->id;
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= Text::sprintf('Using existing parent-position data: %1$s','</span><strong>'.Text::_($row->name).'</strong>');
					$my_text .= '<br />';
				}
				else
				{
try
{
$result = Factory::getDbo()->insertObject('#__sportsmanagement_position', $p_position);	
$insertID = Factory::getDbo()->insertid();
$this->_convertParentPositionID[$oldID] = $insertID;
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf('Created new parent-position data: %1$s','</span><strong>'.Text::_($p_position->name).'</strong>');
$my_text .= '<br />';
}
catch (Exception $e)
{
$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
$my_text .= __LINE__.' error on parent-position import: ';
$my_text .= $oldID;
$my_text .= $e->getMessage().'<br />';                        
}                    
                    
				}
			}
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importPositions()
	 * 
	 * @return
	 */
	private function _importPositions()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       
		$my_text = '';
if( !isset($this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]) ) {
    $this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
}		
		if (!isset($this->_datas['position']) || count($this->_datas['position'])==0){return true;}
		if ((!isset($this->_newpositionsid) || count($this->_newpositionsid)==0) &&
			(!isset($this->_dbpositionsid) || count($this->_dbpositionsid)==0)){return true;}

		if (!empty($this->_dbpositionsid))
		{
			foreach ($this->_dbpositionsid AS $key => $id)
			{
				$oldID = $this->_getDataFromObject($this->_datas['position'][$key],'id');
				$this->_convertPositionID[$oldID] = $id;
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_1',
											'</span><strong>'.Text::_($this->_getObjectName('position',$id)).'</strong>');
				$my_text .= '<br />';
			}
		}

		if (!empty($this->_newpositionsid))
		{
			foreach ($this->_newpositionsid AS $key => $id)
			{
                $p_position = new stdClass();
				$import_position = $this->_datas['position'][$key];
				$oldID = $this->_getDataFromObject($import_position,'id');
				$p_position->name = trim($this->_newpositionsname[$key]);
				$oldParentPositionID = $this->_getDataFromObject($import_position,'parent_id');
				if (isset($this->_convertPositionID[$oldParentPositionID]))
				{
					$p_position->parent_id = $this->_convertPositionID[$oldParentPositionID];
				} else {
					$p_position->parent_id = 0;
				}
				$p_position->persontype = $this->_getDataFromObject($import_position,'persontype');
				$p_position->sports_type_id = $this->_sportstype_id;
				$p_position->published = 1;
				$p_position->alias = OutputFilter::stringURLSafe($this->_getDataFromObject($p_position,'name'));
				
                $query->clear();
        $query->select('id,name');
		$query->from('#__sportsmanagement_position');
        $query->where('name LIKE '.Factory::getDbo()->Quote(''.addslashes(stripslashes($p_position->name)).''));
        $query->where('parent_id = '.$p_position->parent_id);
				
                Factory::getDbo()->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery();
				if (Factory::getDbo()->getAffectedRows())
				{
					$row = Table::getInstance('position', 'sportsmanagementTable');
                    $row->load(Factory::getDbo()->loadResult());
					if ( isset($this->_convertPositionID[$oldID]) )
                    {
                    if ( $this->_convertPositionID[$oldID] != $row->id )
					{
						$this->_convertPositionID[$oldID] = $row->id;
						$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
						$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_1','</span><strong>'.Text::_($row->name).'</strong>');
						$my_text .= '<br />';
					}
                    }
				}
				else
				{

try
{
$result = Factory::getDbo()->insertObject('#__sportsmanagement_position', $p_position);	
$insertID = Factory::getDbo()->insertid();
$this->_convertPositionID[$oldID] = $insertID;
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_2','</span><strong>'.Text::_($p_position->name).'</strong>');
$my_text .= '<br />';
}
catch (Exception $e)
{
$my_text .= ' error on position import: ';
$my_text .= $oldID;
$my_text .= __LINE__.' '.$e->getMessage().'<br />';
}                    
                    
				}
			}
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importPositionEventType()
	 * 
	 * @return
	 */
	private function _importPositionEventType()
	{
		$my_text='';
		if (!isset($this->_datas['positioneventtype']) || count($this->_datas['positioneventtype'])==0){return true;}

		if (!isset($this->_datas['event']) || count($this->_datas['event'])==0){return true;}
		if ((!isset($this->_neweventsid) || count($this->_neweventsid)==0) &&
			(!isset($this->_dbeventsid) || count($this->_dbeventsid)==0)){return true;}
		if (!isset($this->_datas['position']) || count($this->_datas['position'])==0){return true;}
		if ((!isset($this->_newpositionsid) || count($this->_newpositionsid)==0) &&
			(!isset($this->_dbpositionsid) || count($this->_dbpositionsid)==0)){return true;}

		foreach ($this->_datas['positioneventtype'] as $key => $positioneventtype)
		{
			$import_positioneventtype=$this->_datas['positioneventtype'][$key];
			$oldID=$this->_getDataFromObject($import_positioneventtype,'id');
			
            $mdl = BaseDatabaseModel::getInstance("positioneventtype", "sportsmanagementModel");
            $p_positioneventtype = $mdl->getTable();
                
			$oldEventID=$this->_getDataFromObject($import_positioneventtype,'eventtype_id');
			$oldPositionID=$this->_getDataFromObject($import_positioneventtype,'position_id');
			if (!isset($this->_convertEventID[$oldEventID]) ||
				!isset($this->_convertPositionID[$oldPositionID]))
			{
				$my_text .= '<span style="color:red">';
				$my_text .= Text::sprintf(	'Skipping import of PositionEventtype-ID %1$s. Old-EventID: %2$s - Old-PositionID: %3$s',
								"</span><strong>$oldID</strong><span style='color:red'>",
								"</span><strong>$oldEventID</strong><span style='color:red'>",
								"</span><strong>$oldPositionID</strong>").'<br />';
				continue;
			}
			$p_positioneventtype->set('position_id',$this->_convertPositionID[$oldPositionID]);
			$p_positioneventtype->set('eventtype_id',$this->_convertEventID[$oldEventID]);
			$query ="SELECT id
							FROM #__".COM_SPORTSMANAGEMENT_TABLE."_position_eventtype
							WHERE	position_id='$p_positioneventtype->position_id' AND
									eventtype_id='$p_positioneventtype->eventtype_id'";
			Factory::getDbo()->setQuery($query);
			sportsmanagementModeldatabasetool::runJoomlaQuery();
			if ($object=Factory::getDbo()->loadObject())
			{
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= Text::sprintf(	'Using existing positioneventtype data - Position: %1$s - Event: %2$s',
								'</span><strong>'.Text::_($this->_getObjectName('position',$p_positioneventtype->position_id)).'</strong><span style="color:'.$this->existingInDbColor.'">',
								'</span><strong>'.Text::_($this->_getObjectName('eventtype',$p_positioneventtype->eventtype_id)).'</strong>');
				$my_text .= '<br />';
			}
			else
			{
				if ($p_positioneventtype->store()===false)
				{
					$my_text .= 'error on PositionEventType import: ';
					$my_text .= '#'.$oldID.'#';
					$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
				}
				else
				{
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= Text::sprintf(	'Created new positioneventtype data. Position: %1$s - Event: %2$s',
									'</span><strong>'.Text::_($this->_getObjectName('position',$p_positioneventtype->position_id)).'</strong><span style="color:'.$this->storeSuccessColor.'">',
									'</span><strong>'.Text::_($this->_getObjectName('eventtype',$p_positioneventtype->eventtype_id)).'</strong>');
					$my_text .= '<br />';
				}
			}
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importPlayground()
	 * 
	 * @return
	 */
	private function _importPlayground()
	{
	   $app = Factory::getApplication();
	   $query = Factory::getDbo()->getQuery(true);
       $option = Factory::getApplication()->input->getCmd('option');
		$my_text = '';
		if (!isset($this->_datas['playground']) || count($this->_datas['playground'])==0){return true;}
		if ((!isset($this->_newplaygroundid) || count($this->_newplaygroundid)==0) &&
			(!isset($this->_dbplaygroundsid) || count($this->_dbplaygroundsid)==0)){return true;}

		if (!empty($this->_dbplaygroundsid))
		{
			foreach ($this->_dbplaygroundsid AS $key => $id)
			{
				$oldID = $this->_getDataFromObject($this->_datas['playground'][$key],'id');
				$this->_convertPlaygroundID[$oldID] = $id;
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= Text::sprintf(	'Using existing playground data: %1$s - %2$s',
											'</span><strong>'.$this->_getObjectName('playground',$id).'</strong>',
                                            ''.$id.''
                                            );
				$my_text .= '<br />';
			}
		}
		if (!empty($this->_newplaygroundid))
		{
			foreach ($this->_newplaygroundid AS $key => $id)
			{
                //$p_playground = new stdClass();
				$import_playground = $this->_datas['playground'][$key];
$p_playground = $this->_importDataForSave($import_playground,'playground');				
//foreach ($import_playground as $import => $value )
//{
//switch ($import)
//{
//case 'id':
//break;
//default:
//$p_playground->$import = $this->_getDataFromObject($import_playground,$import);    
//break;    
//}   
//}
				
$oldID = $this->_getDataFromObject($import_playground,'id');
$p_playground->alias = substr(OutputFilter::stringURLSafe($this->_getDataFromObject($p_playground,'name')),0,74);
$p_playground->name = substr(trim($this->_newplaygroundname[$key]),0,74);
$p_playground->short_name = substr(trim($this->_newplaygroundname[$key]),0,14);
$p_playground->picture = $p_playground->picture ? $p_playground->picture : ComponentHelper::getParams($option)->get('ph_stadium','');
                
    /** geo coding */
    $address_parts = array();
    $addressdata = $this->_getDataFromObject($import_playground,'address');
    $city = $this->_getDataFromObject($import_playground,'city');
    $zipcode = $this->_getDataFromObject($import_playground,'zipcode');
    $country = $this->_getDataFromObject($import_playground,'country');
    if (!empty($addressdata))
    {
	$address_parts[] = $addressdata;
	}
	
	if (!empty($city))
	{
		if (!empty($zipcode))
		{
			$address_parts[] = $zipcode. ' ' .$city;
		}
		else
		{
			$address_parts[] = $city;
		}
	}
	if (!empty($country))
	{
		$address_parts[] = JSMCountries::getShortCountryName($country);
	}
	$address = implode(', ', $address_parts);
	$coords = sportsmanagementHelper::resolveLocation($address);
    $p_playground->latitude = $coords['latitude'];
    $p_playground->longitude = $coords['longitude'];        
				
				if ( $this->_importType != 'playgrounds' )	// force club_id to be set to default if only playgrounds are imported
				{
					{
						$p_playground->club_id = $this->_getDataFromObject($import_playground,'club_id');
					}
				}
                
$query->clear();
$query->select('id,name,country');
$query->from('#__sportsmanagement_playground');
$query->where('name LIKE '.Factory::getDbo()->Quote(''.addslashes(stripslashes($p_playground->name)).''));
Factory::getDbo()->setQuery($query);
 
sportsmanagementModeldatabasetool::runJoomlaQuery();
if ($object = Factory::getDbo()->loadObject())
{
$this->_convertPlaygroundID[$oldID] = $object->id;
$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
$my_text .= Text::sprintf('Using existing playground data: %1$s',"</span><strong>$object->name</strong>");
$my_text .= '<br />';
}
else
{
try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_playground', $p_playground);
$insertID = Factory::getDbo()->insertid();
$this->_convertPlaygroundID[$oldID] = $insertID;
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf('Created new playground data: %1$s (%2$s)',"</span><strong>$p_playground->name</strong>","<strong>$p_playground->country</strong>");
$my_text .= Text::sprintf('Standard picture: %1$s (%2$s)',"</span><strong>Picture</strong>","<strong>$p_playground->picture</strong>");
$my_text .= '<br />';                   
}
catch (Exception $e){
$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
$my_text .= __LINE__.' '.Text::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
$my_text .= Text::sprintf('Playgroundname: %1$s Shortname [%2$s]',$p_playground->name,$p_playground->short_name).'<br />';
$my_text .= __LINE__.' '.$e->getMessage().'<br />';
}					

				}
			}
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importClubs()
	 * 
	 * @return
	 */
	private function _importClubs()
	{
	   $app = Factory::getApplication();
	   $query = Factory::getDbo()->getQuery(true);

		$my_text = '';
if( !isset($this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]) ) {
    $this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
}		

		if (!isset($this->_datas['club']) || count($this->_datas['club'])==0){return true;}
		if ((!isset($this->_newclubsid) || count($this->_newclubsid)==0) &&
			(!isset($this->_dbclubsid) || count($this->_dbclubsid)==0)){return true;}

		if (!empty($this->_dbclubsid))
		{
		  $query->clear();
        $query->select('id,name,standard_playground,country');
		$query->from('#__sportsmanagement_club');
        $query->group('id');
        
			Factory::getDbo()->setQuery($query);
            sportsmanagementModeldatabasetool::runJoomlaQuery();
			$dbClubs = Factory::getDbo()->loadObjectList('id');

			foreach ($this->_dbclubsid AS $key => $id)
			{
				if (empty($this->_newclubs[$key]))
				{
                    $oldID = $this->_getDataFromObject($this->_datas['team'][$key],'club_id');
					$this->_convertClubID[$oldID] = $id;
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= Text::sprintf(	'COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_1',
												'</span><strong>'.$dbClubs[$id]->name.'</strong>',
												''.$dbClubs[$id]->id.''
												);
					$my_text .= '<br />';
				}
			}
		}
//To Be fixed: Falls Verein neu angelegt wird, muss auch das Team neu angelegt werden.
		if (!empty($this->_newclubsid))
		{
			foreach ($this->_newclubsid AS $key => $id)
			{
//                $p_club = new stdClass();
                
				foreach ($this->_datas['club'] AS $dClub)
				{
					if ( $dClub->id == $id )
					{
						$import_club = $dClub;
						break;
					}
				}

				$oldID = $this->_getDataFromObject($import_club,'id');
$p_club = $this->_importDataForSave($import_club,'club');
//foreach ($import_club as $import => $value )
//{
//switch ($import)
//{
//case 'id':
//break;
//default:
//$p_club->$import = $this->_getDataFromObject($import_club,$import);    
//break;    
//}   
//}
				$p_club->name = $this->_newclubs[$key];
				$p_club->admin = $this->_joomleague_admin;
				$p_club->country = $this->_newclubscountry[$key];

    /** geo coding */
    $address_parts = array();
    $addressdata = $this->_getDataFromObject($import_club,'address');
    $state = $this->_getDataFromObject($import_club,'state');
    $location = $this->_getDataFromObject($import_club,'location');
    $zipcode = $this->_getDataFromObject($import_club,'zipcode');
    $country = $this->_getDataFromObject($import_club,'country');
    if (!empty($addressdata))
    {
	$address_parts[] = $addressdata;
	}
	if (!empty($state))
	{
		$address_parts[] = $state;
	}
	if (!empty($location))
	{
		if (!empty($zipcode))
		{
			$address_parts[] = $zipcode. ' ' .$location;
		}
		else
		{
			$address_parts[] = $location;
		}
	}
	if (!empty($country))
	{
		$address_parts[] = JSMCountries::getShortCountryName($country);
	}
	$address = implode(', ', $address_parts);
	$coords = sportsmanagementHelper::resolveLocation($address);
    $p_club->latitude = $coords['latitude'];
    $p_club->longitude = $coords['longitude'];        
	$p_club->alias = OutputFilter::stringURLSafe($this->_getDataFromObject($p_club,'name'));

				if ($this->_importType!='clubs')	// force playground_id to be set to default if only clubs are imported
				{
					if (($this->import_version=='NEW') && ($import_club->standard_playground > 0))
					{
						if (isset($this->_convertPlaygroundID[(int)$this->_getDataFromObject($import_club,'standard_playground')]))
						{
							$p_club->standard_playground = (int)$this->_convertPlaygroundID[(int)$this->_getDataFromObject($import_club,'standard_playground')];
						}
					}
				}
				if (($this->import_version=='NEW') && ($import_club->extended!=''))
				{
					$p_club->extended = $this->_getDataFromObject($import_club,'extended');
				}
				
                $query->clear();
        $query->select('id,name,country');
		$query->from('#__sportsmanagement_club');
        $query->where('name LIKE '.Factory::getDbo()->Quote(''.addslashes(stripslashes($p_club->name)).''));
        $query->where('country LIKE '.Factory::getDbo()->Quote(''.$p_club->country.''));
			Factory::getDbo()->setQuery($query);

                sportsmanagementModeldatabasetool::runJoomlaQuery();
				if ( $object = Factory::getDbo()->loadObject())
				{
					$this->_convertClubID[$oldID] = $object->id;
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= Text::sprintf('Using existing club data: %1$s',"</span><strong>$object->name</strong>");
					$my_text .= '<br />';
				}
				else
				{

try
{
$result = Factory::getDbo()->insertObject('#__sportsmanagement_club', $p_club);	
$insertID = Factory::getDbo()->insertid();
$this->_convertClubID[$oldID] = $insertID;
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf('Created new club data: %1$s - %2$s - %3$s',
	"</span><strong>$p_club->name</strong>",
	"".$p_club->country."",$insertID
	);
$my_text .= '<br />';
}
catch (Exception $e)
{
$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
$my_text .= Text::sprintf('Clubname: %1$s',$p_club->name).'<br />';
$my_text .= __LINE__.' '.$e->getMessage().'<br />';						
}
					
				}

			}
		}

		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_convertNewPlaygroundIDs()
	 * 
	 * @return
	 */
	private function _convertNewPlaygroundIDs()
	{
    $app = Factory::getApplication();   
        $my_text = '';
		$converted=false;
		if ( isset($this->_convertPlaygroundID) && !empty($this->_convertPlaygroundID) )
		{
			
            foreach ( $this->_datas['playground'] as $key => $value  )
            {
                
                $import_playground = $this->_datas['playground'][$key];
				$oldID = $this->_getDataFromObject($import_playground,'id');
				$club_id = $this->_getDataFromObject($import_playground,'club_id');
                
                $new_pg_id = $this->_convertPlaygroundID[$oldID];
                if ( isset($this->_convertClubID[$club_id]) )
                {
                $new_club_id = $this->_convertClubID[$club_id];
                }
                else
                {
                    $new_club_id = 0;
                }
                $p_playground = $this->_getPlaygroundRecord($new_pg_id);
                if ( $p_playground->club_id != $new_club_id )
                {
                    if ( $this->_updatePlaygroundRecord($new_club_id,$new_pg_id) )
						{
							$converted = true;
							$my_text .= '<span style="color:green">';
							$my_text .= Text::sprintf(	'Converted club-info %1$s in imported playground %2$s - [club_id: %3$s] [playground-id: %4$s]',
														'</span><strong>'.$this->_getClubName($new_club_id).'</strong><span style="color:green">',
														"</span><strong>$p_playground->name</strong>",
                                                        "".$new_club_id."",
                                                        "".$new_pg_id."");
							$my_text .= '<br />';
						}
						
                }
                
            }
            
			if (!$converted){$my_text .= '<span style="color:green">'.Text::_('Nothing needed to be converted').'<br />';}
			$this->_success_text['Converting new playground club-IDs of new playground data:'] = $my_text;
		}
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importTeams()
	 * 
	 * @return
	 */
	private function _importTeams()
	{
	   $app = Factory::getApplication();
	   $query = Factory::getDbo()->getQuery(true);

		$my_text = '';
if( !isset($this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]) ) {
    $this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
}		
		if (!isset($this->_datas['team']) || count($this->_datas['team'])==0){return true;}
		if ((!isset($this->_newteams) || count($this->_newteams)==0) &&
			(!isset($this->_dbteamsid) || count($this->_dbteamsid)==0)){return true;}

		if (!empty($this->_dbteamsid))
		{
		  $query->clear();
        $query->select('id,name,club_id,short_name,middle_name,info');
		$query->from('#__sportsmanagement_team');
        $query->group('id');
        
			Factory::getDbo()->setQuery($query);
            sportsmanagementModeldatabasetool::runJoomlaQuery();

			$dbTeams = Factory::getDbo()->loadObjectList('id');

			foreach ($this->_dbteamsid AS $key => $id)
			{
				if (empty($this->_newteams[$key]))
				{
					$oldID = $this->_getDataFromObject($this->_datas['team'][$key],'id');
					$this->_convertTeamID[$oldID] = $id;
                    
$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
$my_text .= Text::sprintf('Using existing team data 1: %1$s - %2$s - %3$s - %4$s <- %5$s',
	'</span><strong>'.$dbTeams[$id]->name.'</strong>',
	'<strong>'.$dbTeams[$id]->short_name.'</strong>',
	'<strong>'.$dbTeams[$id]->middle_name.'</strong>',
	'<strong>'.$dbTeams[$id]->info.'</strong>',
	'<strong>'.$id.'</strong>'
	);
$my_text .= '<br />';
				}
			}


		}
//To Be fixed: Falls Verein neu angelegt wird, muss auch das Team neu angelegt werden.


		if (!empty($this->_newteams))
		{
			foreach ($this->_newteams AS $key => $value)
			{
				$import_team = $this->_datas['team'][$key];
$p_team = $this->_importDataForSave($import_team,'team');                

$oldID = $this->_getDataFromObject($import_team,'id');
$oldClubID = $this->_getDataFromObject($import_team,'club_id');
$p_team->short_name = substr($p_team->name,0,14);
$p_team->middle_name = substr($p_team->name,0,24);
$p_team->sports_type_id = $this->_sportstype_id;
$p_team->agegroup_id = $this->_agegroup_id;

				if ( !empty($import_team->club_id) && isset($this->_convertClubID[$oldClubID]) )
				{
					$p_team->club_id = $this->_convertClubID[$oldClubID];
				}
				else
				{
					$p_team->club_id = 0;
				}
				$p_team->alias = OutputFilter::stringURLSafe($this->_getDataFromObject($p_team,'name'));

				if (($this->import_version=='NEW') && ($import_team->extended!=''))
				{
					$p_team->extended = $this->_getDataFromObject($import_team,'extended');
				}
                
                $query->clear();
        $query->select('id,name,short_name,middle_name,info,club_id');
		$query->from('#__sportsmanagement_team');
        $query->where('name LIKE '.Factory::getDbo()->Quote(''.addslashes(stripslashes($p_team->name)).''));
        $query->where('middle_name LIKE '.Factory::getDbo()->Quote(''.addslashes(stripslashes($p_team->middle_name)).''));
        $query->where('info LIKE '.Factory::getDbo()->Quote(''.addslashes(stripslashes($p_team->info)).''));

				Factory::getDbo()->setQuery($query); 
                sportsmanagementModeldatabasetool::runJoomlaQuery();
				if ( $object = Factory::getDbo()->loadObject())
				{
					$this->_convertTeamID[$oldID]=$object->id;
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= Text::sprintf('Using existing team data 2: %1$s',"</span><strong>$object->name</strong>");
					$my_text .= '<br />';
				}
				else
				{

try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_team', $p_team);
$insertID = Factory::getDbo()->insertid();
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf('Created new team data: %1$s - %2$s - %3$s - %4$s - club_id [%5$s]',
	"</span><strong>$p_team->name</strong>",
	"<strong>$p_team->short_name</strong>",
	"<strong>$p_team->middle_name</strong>",
	"<strong>$p_team->info</strong>",
	"<strong>$p_team->club_id</strong>"
	);
$my_text .= '<br />';
}	
catch (Exception $e){
$insertID = 0;
$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
$my_text .= Text::sprintf('Teamname: %1$s',$p_team->name).'<br />';
$my_text .= __LINE__.' '.$e->getMessage().'<br />';	
}	
$this->_convertTeamID[$oldID] = $insertID;
				}
			}
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importPersons()
	 * 
	 * @return
	 */
	private function _importPersons()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
      
		if (!isset($this->_datas['person']) || count($this->_datas['person'])==0){return true;}
		if ((!isset($this->_newpersonsid) || count($this->_newpersonsid)==0) &&
			(!isset($this->_dbpersonsid) || count($this->_dbpersonsid)==0)){return true;}

		$my_text = '';
if( !isset($this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]) ) {
    $this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
}		
		if (!empty($this->_dbpersonsid))
		{
			foreach ($this->_dbpersonsid AS $key => $id)
			{
				$oldID = $this->_getDataFromObject($this->_datas['person'][$key],'id');
				$this->_convertPersonID[$oldID] = $id;
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= Text::sprintf(	'Using existing person data: %1$s',
							'</span><strong>'.$this->_getObjectName('person',$id,"CONCAT(id,' -> ',lastname,',',firstname,' - ',nickname,' - ',birthday) AS name").'</strong>');
				$my_text .= '<br />';
                
                $mdl = BaseDatabaseModel::getInstance("person", "sportsmanagementModel");
                $update_person = $mdl->getTable();
                
                $update_person->load($id);
                $update_person->info = $this->_getDataFromObject($this->_datas['person'][$key],'info');
		$update_person->agegroup_id = $this->_dbpersonsagegroup[$key];		
                if ($update_person->store() === false)
					{
					}
					else
					{
if ( $this->_season_id )
{
try{
// wenn nichts gefunden wurde neu anlegen
$newseasonperson = new stdClass();
$newseasonperson->person_id = $id;
$newseasonperson->season_id = $this->_season_id;
$newseasonperson->persontype = 1;
// Insert the object
$resultperson = Factory::getDbo()->insertObject('#__sportsmanagement_season_person_id', $newseasonperson);	
} catch (Exception $e) {
//$app->enqueueMessage(Text::_($e->getMessage()), 'error');
}
}						
					}
			}
		}
		if (!empty($this->_newpersonsid))
		{
			foreach ($this->_newpersonsid AS $key => $id)
			{
$import_person = $this->_datas['person'][$key];
$p_person = $this->_importDataForSave($import_person,'person');
$oldID = $this->_getDataFromObject($import_person,'id');
$p_person->published = 1;
                
    /** geo coding */
    $address_parts = array();
    $addressdata = $this->_getDataFromObject($import_person,'address');
    $city = $this->_getDataFromObject($import_person,'city');
    $zipcode = $this->_getDataFromObject($import_person,'zipcode');
    $country = $this->_getDataFromObject($import_person,'address_country');
    $state = $this->_getDataFromObject($import_person,'state');
    if (!empty($addressdata))
    {
	$address_parts[] = $addressdata;
	}
	if (!empty($state))
    {
	$address_parts[] = $state;
	}
	if (!empty($city))
	{
		if (!empty($zipcode))
		{
			$address_parts[] = $zipcode. ' ' .$city;
		}
		else
		{
			$address_parts[] = $city;
		}
	}
	if (!empty($country))
	{
		$address_parts[] = JSMCountries::getShortCountryName($country);
	}
	$address = implode(', ', $address_parts);
	$coords = sportsmanagementHelper::resolveLocation($address);
    $p_person->latitude = $coords['latitude'];
    $p_person->longitude = $coords['longitude'];       
    
    
				if ($this->_importType!='persons')	// force position_id to be set to default if only persons are imported
				{
					if ($import_person->position_id > 0)
					{
						if (isset($this->_convertPositionID[(int)$this->_getDataFromObject($import_person,'position_id')]))
						{
							$p_person->position_id = (int)$this->_convertPositionID[(int)$this->_getDataFromObject($import_person,'position_id')];
						}
					}
				}

				$aliasparts = array(trim($p_person->firstname),trim($p_person->lastname));
				$p_alias = OutputFilter::stringURLSafe(implode(' ',$aliasparts));
				$p_person->alias = $p_alias;
                
                $query->clear();
        $query->select('*');
		$query->from('#__sportsmanagement_person');
        $query->where('firstname LIKE '.Factory::getDbo()->Quote(''.addslashes(stripslashes($p_person->firstname)).''));
        $query->where('lastname LIKE '.Factory::getDbo()->Quote(''.addslashes(stripslashes($p_person->lastname)).''));
        $query->where('nickname LIKE '.Factory::getDbo()->Quote(''.addslashes(stripslashes($p_person->nickname)).''));
        $query->where('birthday = '.Factory::getDbo()->Quote(''.$p_person->birthday.''));
			
				Factory::getDbo()->setQuery($query); 
                sportsmanagementModeldatabasetool::runJoomlaQuery();
				if ($object=Factory::getDbo()->loadObject())
				{
					$this->_convertPersonID[$oldID]=$object->id;
					$nameStr=!empty($object->nickname) ? '['.$object->nickname.']' : '';
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= Text::sprintf(	'Using existing person data: %1$s %2$s [%3$s] - %4$s',
												"</span><strong>$object->lastname</strong>",
												"<strong>$object->firstname</strong>",
												"<strong>$nameStr</strong>",
												"<strong>$object->birthday</strong>");
					$my_text .= '<br />';
				}
				else
				{
					try
					{
$result = Factory::getDbo()->insertObject('#__sportsmanagement_person', $p_person);						
						$insertID = Factory::getDbo()->insertid();
						$this->_convertPersonID[$oldID] = $insertID;
						$dNameStr=((!empty($p_person->lastname)) ?
									$p_person->lastname :
									'<span style="color:orange">'.Text::_('Has no lastname').'</span>');
						$dNameStr .= ','.((!empty($p_person->firstname)) ?
									$p_person->firstname.' - ' :
									'<span style="color:orange">'.Text::_('Has no firstname').' - </span>');
						$dNameStr .= ((!empty($p_person->nickname)) ? "'".$p_person->nickname."' - " : '');
						$dNameStr .= $p_person->birthday;
                        $dNameStr .= '<span style="color:blue"> PositionId old/new->'.$import_person->position_id.' - '.$p_person->position_id.' - </span>';

						$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_1',"</span><strong>$dNameStr</strong>" );
						$my_text .= '<br />';

if ( $this->_season_id )
{	
try {
// wenn nichts gefunden wurde neu anlegen
$newseasonperson = new stdClass();
$newseasonperson->person_id = $insertID;
$newseasonperson->season_id = $this->_season_id;
$newseasonperson->persontype = 1;
// Insert the object
$resultperson = Factory::getDbo()->insertObject('#__sportsmanagement_season_person_id', $newseasonperson);												
} catch (Exception $e) {
//$app->enqueueMessage(Text::_($e->getMessage()), 'error');
}	
}						
					}
					catch (Exception $e)
{
    //$app->enqueueMessage(JText::_($e->getMessage()), 'error');
$my_text .= 'error on person import: ';
						$my_text .= $p_person->lastname.'-';
						$my_text .= $p_person->firstname.'-';
						$my_text .= $p_person->nickname.'-';
						$my_text .= $p_person->birthday.'<br>';
						$my_text .= $e->getMessage().'<br>';
}

					
				}
			}
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importProject()
	 * 
	 * @return
	 */
	private function _importProject()
	{
		$app = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
        $my_text = '';
if( !isset($this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]) ) {
    $this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
}		

$import_project = $this->_datas['project'];
$p_project = $this->_importDataForSave($import_project,'project');

		$p_project->name = substr(trim($this->_name),0,99);
		$p_project->alias = substr(OutputFilter::stringURLSafe(trim($this->_name)),0,99);
		$p_project->league_id = $this->_league_id;
        $p_project->import_project_id = $this->_import_project_id;
		$p_project->season_id = $this->_season_id;
		$p_project->admin = $this->_joomleague_admin;
		$p_project->editor = $this->_joomleague_editor;
		$p_project->master_template = $this->_template_id;
		$p_project->sub_template_id = 0;
		$p_project->sports_type_id = $this->_sportstype_id;
        $p_project->agegroup_id = $this->_agegroup_id;
		 $p_project->picture = ComponentHelper::getParams($option)->get('ph_project','');
		
		if ( is_numeric($this->timezone) )
		{
	// Get the list of time zones from the server.
        $zones = DateTimeZone::listIdentifiers();
	$p_project->timezone = $zones[$this->timezone];	
		}
		
		if ($this->_publish){$p_project->published = 1;}

try {		
$result = Factory::getDbo()->insertObject('#__sportsmanagement_project', $p_project);
$insertID = Factory::getDbo()->insertid();
$this->_project_id = $insertID;
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_1',"</span><strong>$this->_name</strong>");
$my_text .= '<br />';
} catch (Exception $e) {
$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
$my_text .= Text::sprintf('Projectname: %1$s',$p_project->name).'<br />';
$my_text .= __LINE__.' '.$e->getMessage().'<br />';	
}		
$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
return true;		
	}

	/**
	 * check that all templates in default location have a corresponding record,except if project has a master template
	 *
	 */
	private function _checklist()
	{
		$project_id = $this->_project_id;
		$defaultpath = JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'settings';
		$extensiontpath = JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'extensions'.DS;
		$predictionTemplatePrefix = 'prediction';
        $my_text = '';

		if (!$project_id){return;}

		// get info from project
		$query='SELECT master_template,extension FROM #__sportsmanagement_project WHERE id='.(int)$project_id;

		Factory::getDbo()->setQuery($query);
		$params = Factory::getDbo()->loadObject();

		// if it's not a master template,do not create records.
		if ($params->master_template)
        {
            return true;
        }

		// otherwise,compare the records with the files
		// get records
		$query='SELECT template FROM #__sportsmanagement_template_config WHERE project_id='.(int)$project_id;

		Factory::getDbo()->setQuery($query);
    
     if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
		$records = $db->loadColumn();
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
		$records = $db->loadResultArray();
}
		//$records = Factory::getDbo()->loadResultArray();
		if (empty($records))
        {
            $records = array();
        }

		// first check extension template folder if template is not default
		if ((isset($params->extension)) && ($params->extension!=''))
		{
			if (is_dir($extensiontpath.$params->extension.DIRECTORY_SEPARATOR.'settings'))
			{
				$xmldirs[] = $extensiontpath.$params->extension.DIRECTORY_SEPARATOR.'settings';
			}
		}

		// add default folder
		$xmldirs[] = $defaultpath.DIRECTORY_SEPARATOR.'default';

		// now check for all xml files in these folders
		foreach ($xmldirs as $xmldir)
		{
			if ($handle=opendir($xmldir))
			{
				/* check that each xml template has a corresponding record in the
				database for this project. If not,create the rows with default values
				from the xml file */
				while ($file=readdir($handle))
				{
					if	(	$file!='.' &&
							$file!='..' &&
							$file!='do_tipsl' &&
							strtolower(substr($file,-3))=='xml' &&
							strtolower(substr($file,0,strlen($predictionTemplatePrefix))) != $predictionTemplatePrefix
						)
					{
						$template = substr($file,0,(strlen($file)-4));

						if ((empty($records)) || (!in_array($template,$records)))
						{
							$xmlfile = $xmldir.DIRECTORY_SEPARATOR.$file;
							$arrStandardSettings = array();
							if(file_exists($xmlfile)) {
								$strXmlFile = $xmlfile;
                                $form = JForm::getInstance($template, $strXmlFile,array('control'=> ''));
                                $fieldsets = $form->getFieldsets();
								foreach ($fieldsets as $fieldset) 
                                {
									foreach($form->getFieldset($fieldset->name) as $field) 
                                    {
										$arrStandardSettings[$field->name] = $field->value;
									}
								}
                                
							}
							
                            $defaultvalues = json_encode( $arrStandardSettings);
                            
							$query="	INSERT INTO #__".COM_SPORTSMANAGEMENT_TABLE."_template_config (template,title,params,project_id)
													VALUES ('$template','".$form->getName()."','$defaultvalues','$project_id')";
							Factory::getDbo()->setQuery($query);
                            
                            $my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
                            $my_text .= Text::sprintf('Created new template data for empty Projectdata: %1$s %2$s',"</span><strong>$template</strong>","<br><strong>".$defaultvalues."</strong>" );
			                $my_text .= '<br />';
                            
							//echo error,allows to check if there is a mistake in the template file
							if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
							{
								//$this->setError(Factory::getDbo()->getErrorMsg());
								return false;
							}
							array_push($records,$template);
						}
					}
				}
                
                $this->_success_text['Importing general template data:'] = $my_text;
                                
				closedir($handle);
			}
		}
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importTemplate()
	 * 
	 * @return
	 */
	private function _importTemplate()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       $defaultpath = JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'default';
       
		$my_text='';
		if ($this->_template_id > 0) // Uses a master template
		{
		  $query->clear();
          // Select some fields
        $query->select('id,master_template');
		// From the table
		$query->from('#__sportsmanagement_project');
        $query->where('id = '.$this->_template_id);
        
			Factory::getDbo()->setQuery($query);
			$template_row = Factory::getDbo()->loadAssoc();
			if ($template_row['master_template']==0)
			{
				$this->_master_template=$template_row['id'];
			}
			else
			{
				$this->_master_template=$template_row['master_template'];
			}
            
            $query->clear();
          // Select some fields
        $query->select('id,master_template');
		// From the table
		$query->from('#__sportsmanagement_template_config');
        $query->where('project_id = '.$this->_master_template);
        
			Factory::getDbo()->setQuery($query);
            sportsmanagementModeldatabasetool::runJoomlaQuery();
			$rows = Factory::getDbo()->loadObjectList();
			foreach ($rows AS $row)
			{
				
                $mdl = BaseDatabaseModel::getInstance("template", "sportsmanagementModel");
                $p_template = $mdl->getTable();
                
				$p_template->load($row->id);
				$p_template->set('project_id',$this->_project_id);
				if ($p_template->store()===false)
				{
					$my_text .= 'error on master template import: ';
					$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
				}
				else
				{
					$my_text .= $p_template->template;
					$my_text .= ' <font color="'.$this->storeSuccessColor.'">'.Text::_('...created new data').'</font><br />';
					$my_text .= '<br />';
				}
			}
		}
		else
		{
			$this->_master_template = 0;
			$predictionTemplatePrefix = 'prediction';
			if ((isset($this->_datas['template'])) && (is_array($this->_datas['template'])))
			{
				foreach ($this->_datas['template'] as $value)
				{
				    
					
                    $mdl = BaseDatabaseModel::getInstance("template", "sportsmanagementModel");
                    $p_template = $mdl->getTable();
                    
					$template = $this->_getDataFromObject($value,'template');
					$p_template->set('template',$template);
                    
                    
					//actually func is unused in 1.5.0
					//$p_template->set('func',$this->_getDataFromObject($value,'func'));
					$p_template->set('title',$this->_getDataFromObject($value,'title'));
					$p_template->set('project_id',$this->_project_id);
					
					$t_params = $this->_getDataFromObject($value,'params');
					$defaultvalues = array();
					$defaultvalues = explode('\n', $t_params);
					$parameter = new JRegistry;
                    
     if(version_compare(JVERSION,'3.0.0','ge')) 
        {
            $ini = $parameter->loadString($defaultvalues[0]);
        }
        else
        {
            $ini = $parameter->loadINI($defaultvalues[0]);
        }
        
			// beim import kann es vorkommen, das wir in der neuen komponente
                    // zusätzliche felder haben, die mit abgespeichert werden müssen
                    $xmlfile = $defaultpath.DIRECTORY_SEPARATOR.$template.'.xml';

                            if( file_exists($xmlfile) )
                            {

$newparams = array();
$xml = Factory::getXML($xmlfile,true);
foreach ($xml->fieldset as $paramGroup)
		{
		foreach ($paramGroup->field as $param)
			{
                $newparams[(string)$param->attributes()->name] = (string)$param->attributes()->default;
			}
        } 

foreach ( $newparams as $key => $value )
{
    if(version_compare(JVERSION,'3.0.0','ge')) 
        {
    $value = $ini->get($key);
    }
    else
    {
    //$value = $ini->getValue($key);
    }
    if ( isset($value) )
    {
    $newparams[$key] = $value;
    }
} 
   
$t_params = json_encode( $newparams ); 
                            }
else
{
$ini = $parameter->toArray($ini);
$t_params = json_encode( $ini ); 
            }
					$p_template->set('params',$t_params);
             unset($t_params);
					if	((strtolower(substr($template,0,strlen($predictionTemplatePrefix)))!=$predictionTemplatePrefix) &&
						($template!='do_tipsl') &&
						($template!='frontpage') &&
						($template!='table') &&
						($template!='tipranking') &&
						($template!='tipresults') &&
						($template!='user'))
					{
						if ($p_template->store()===false)
						{
							$my_text .= 'error on own template import: ';
							$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
						}
						else
						{
							$dTitle = (!empty($p_template->title)) ? Text::_($p_template->title) : $p_template->template;
							$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
							$my_text .= Text::sprintf('Created new template data from Import-Project: %1$ [%2$]',"</span><strong>".$p_template->template."</strong>","<strong>".$p_template->params."</strong>");
							$my_text .= '<br />';
						}
					}
				}
			}
		}
        
        // Create an object for the record we are going to update.
        $object = new stdClass();
        // Must be a valid primary key value.
        $object->id = $this->_project_id;
        $object->master_template = $this->_master_template;
        // Update their details in the table using id as the primary key.
        $result = Factory::getDbo()->updateObject('#__sportsmanagement_project', $object, 'id');
        
//		$query="UPDATE #__".COM_SPORTSMANAGEMENT_TABLE."_project SET master_template=$this->_master_template WHERE id=$this->_project_id";
//        Factory::getDbo()->setQuery($query);
//		sportsmanagementModeldatabasetool::runJoomlaQuery();
        
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		if ( $this->_master_template == 0 )
		{
			// check and create missing templates if needed
			//$this->_checklist();
			$my_text='<span style="color:green">';
			$my_text .= Text::_('Checked and created missing template data if needed');
			$my_text .= '</span><br />';
			$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] .= $my_text;
		}
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importDivisions()
	 * 
	 * @return
	 */
	private function _importDivisions()
	{
		$my_text = '';
		if (!isset($this->_datas['division']) || count($this->_datas['division']) == 0 ){return true;}
		if (isset($this->_datas['division']))
		{
			foreach ($this->_datas['division'] as $key => $division)
			{
				$import_division = $this->_datas['division'][$key];
				$oldId = (int)$division->id;
				
				if ( $division->id == $this->_datas['division'][$key]->id )
				{
$p_division = $this->_importDataForSave($import_division,'division');				    
$p_division->project_id =  $this->_project_id;
$p_division->alias = OutputFilter::stringURLSafe($p_division->name);				    
}
			
try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_division', $p_division);
$insertID = Factory::getDbo()->insertid();
$p_division->id = $insertID;
$this->_convertDivisionID[$oldId] = $insertID;
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf('Created new division data: %1$s',"</span><strong>$p_division->name</strong>");
$my_text .= '<br />';
}	
catch (Exception $e){
$my_text .= ' error on division import: ';
$my_text .= '#'.$oldID.'#<br />';
$my_text .= __LINE__.' '.$e->getMessage().'<br />';
}            
            
            }
			$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
			return true;
		}
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importProjectTeam()
	 * 
	 * @return
	 */
	private function _importProjectTeam()
	{
	   $app = Factory::getApplication();
        $db = Factory::getDbo();
        
		$my_text = '';
if( !isset($this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]) ) {
    $this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
}		
		if ( !isset($this->_datas['projectteam']) || count($this->_datas['projectteam']) == 0 )
        {
            return true;
        }

		if ( !isset($this->_datas['team']) || count($this->_datas['team']) == 0 )
        {
            return true;
        }
		
        if (( !isset($this->_newteams) || count($this->_newteams) == 0 ) &&
			( !isset($this->_dbteamsid) || count($this->_dbteamsid) == 0 ))
            {
                return true;
            }
           
        foreach ($this->_datas['projectteam'] as $key => $projectteam)
		{

          //  $p_projectteam = new stdClass();
			$import_projectteam = $this->_datas['projectteam'][$key];
			$oldID = $this->_getDataFromObject($import_projectteam,'id');
$p_projectteam = $this->_importDataForSave($import_projectteam,'project_team');	

if (array_key_exists('description', $import_projectteam)) {
$p_projectteam->notes = $this->_getDataFromObject($projectteam,'description');
}
if (array_key_exists('info', $import_projectteam)) {
$p_projectteam->reason = $this->_getDataFromObject($projectteam,'info');
}

	$p_projectteam->project_id = $this->_project_id;
            
/**
* jetzt erfolgt die umsetzung der team_id in die neue struktur 
* $p_projectteam->set('team_id',$this->_convertTeamID[$this->_getDataFromObject($projectteam,'team_id')]);
*/            
			$new_team_id = 0;
            $team_id = $this->_convertTeamID[$this->_getDataFromObject($projectteam,'team_id')];
           
            if ( $team_id )
            {
/**
 * ist das team schon durch ein anderes projekt angelegt ?
 */
            $query = $db->getQuery(true);
	    $query->select('id');		
	    $query->from('#__sportsmanagement_season_team_id AS t');
	    $query->where('t.team_id = '.$team_id);
            $query->where('t.season_id = '.$this->_season_id);
	    $db->setQuery($query);
	    $new_team_id = $db->loadResult();
               
            if ( $new_team_id )
            {
                $p_projectteam->team_id = $new_team_id;
            }
            else
            {
            $insertquery = $db->getQuery(true);
            $columns = array('team_id','season_id','picture');
            $values = array($team_id,$this->_season_id,'\''.$p_projectteam->picture.'\'');
            $insertquery
            ->insert($db->quoteName('#__sportsmanagement_season_team_id'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
            $db->setQuery($insertquery);
            if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
	    {
	    }
	    else
	    {
            /** die neue id übergeben */
            $new_team_id = $db->insertid();
            $p_projectteam->team_id = $new_team_id;
	    }
            }
            }
            
            
            $team_id = $this->_convertTeamID[$this->_getDataFromObject($projectteam,'team_id')];

			if ( isset($this->_convertDivisionID) )
            {
            if (count($this->_convertDivisionID) > 0)
			{
				$p_projectteam->division_id = $this->_convertDivisionID[$this->_getDataFromObject($projectteam,'division_id')];
			}
            }

			$p_projectteam->admin = $this->_joomleague_admin;
			
			if ((isset($projectteam->standard_playground)) && ($projectteam->standard_playground > 0))
			{
				if (isset($this->_convertPlaygroundID[$this->_getDataFromObject($projectteam,'standard_playground')]))
				{
					$p_projectteam->standard_playground = $this->_convertPlaygroundID[$this->_getDataFromObject($projectteam,'standard_playground')];
				}
			}

try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_project_team', $p_projectteam);
$insertID = Factory::getDbo()->insertid();
$p_projectteam->id = $insertID;
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf('Created new projectteam data: %1$s - Team ID : %2$s',
	'</span><strong>'.$this->_getTeamName2(0,$p_projectteam->team_id).'</strong>',
        '<strong>'.$team_id.'</strong>');
$my_text .= '<br />';
}	
catch (Exception $e){
$p_projectteam->id = 0;    
$my_text .= __LINE__.' error on projectteam import: ';
$my_text .= $oldID;
$my_text .= '<br />';
$my_text .= $e->getMessage().'<br />';
}	
           
			$insertID = $p_projectteam->id;//Factory::getDbo()->insertid();
			
            if ($this->import_version=='NEW')
			{
            $this->_convertProjectTeamID[$this->_getDataFromObject($projectteam,'id')] = $p_projectteam->id;
            }
            else
            {
            $this->_convertProjectTeamID[$this->_getDataFromObject($projectteam,'team_id')] = $p_projectteam->id;    
            }

		}

		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importProjectReferees()
	 * 
	 * @return
	 */
	private function _importProjectReferees()
	{
		$my_text = '';
		if (!isset($this->_datas['projectreferee']) || count($this->_datas['projectreferee'])==0){return true;}

		if (!isset($this->_datas['person']) || count($this->_datas['person'])==0){return true;}
		if ((!isset($this->_newpersonsid) || count($this->_newpersonsid)==0) &&
			(!isset($this->_dbpersonsid) || count($this->_dbpersonsid)==0)){return true;}

		foreach ($this->_datas['projectreferee'] as $key => $projectreferee)
		{
			$import_projectreferee = $this->_datas['projectreferee'][$key];
			$oldID = $this->_getDataFromObject($import_projectreferee,'id');
			
$p_projectreferee = $this->_importDataForSave($import_projectreferee,'project_referee');	

$p_projectreferee->project_id = $this->_project_id;
$p_projectreferee->person_id = $this->_convertPersonID[$this->_getDataFromObject($import_projectreferee,'person_id')];
$p_projectreferee->project_position_id = $this->_convertProjectPositionID[$this->_getDataFromObject($import_projectreferee,'project_position_id')];
$p_projectreferee->published = 1;
           
try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_project_referee', $p_projectreferee);
$insertID = Factory::getDbo()->insertid();
$p_projectreferee->id = $insertID;
$this->_convertProjectRefereeID[$oldID] = $insertID;
$dPerson = $this->_getPersonName($p_projectreferee->person_id);
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf(	'Created new projectreferee data: %1$s,%2$s',"</span><strong>$dPerson->lastname","$dPerson->firstname</strong>");
$my_text .= '<br />';
}	
catch (Exception $e){
$my_text .= ' error on projectreferee import: ';
$my_text .= '#'.$oldID.'#<br />';
$my_text .= __LINE__.' '.$e->getMessage().'<br />';
}                
            
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importProjectPositions()
	 * 
	 * @return
	 */
	private function _importProjectPositions()
	{
        $app = Factory::getApplication();
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
		$my_text = '';
if( !isset($this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]) ) {
    $this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
}		
		if (!isset($this->_datas['projectposition']) || count($this->_datas['projectposition'])==0){return true;}
		if (!isset($this->_datas['position']) || count($this->_datas['position'])==0){return true;}
		if ((!isset($this->_newpositionsid) || count($this->_newpositionsid)==0) &&
			(!isset($this->_dbpositionsid) || count($this->_dbpositionsid)==0)){return true;}

		foreach ($this->_datas['projectposition'] as $key => $projectposition)
		{
			$import_projectposition = $this->_datas['projectposition'][$key];
			$oldID = $this->_getDataFromObject($import_projectposition,'id');
            $p_projectposition = new stdClass();
			$p_projectposition->project_id = $this->_project_id;
			$oldPositionID = $this->_getDataFromObject($import_projectposition,'position_id');
			
            if (!isset($this->_convertPositionID[$oldPositionID]))
			{
				$my_text .= '<span style="color:red">';
				$my_text .= Text::sprintf(	'Skipping import of ProjectPosition-ID %1$s. Old-PositionID: %2$s',
								"</span><strong>$oldID</strong><span style='color:red'>",
								"</span><strong>$oldPositionID</strong>").'<br />';
				continue;
			}
            
			$p_projectposition->position_id = $this->_convertPositionID[$oldPositionID];
            
            $query->clear();
            $query->select('id');
		      $query->from('#__sportsmanagement_project_position');
            $query->where('project_id = ' . $this->_project_id );
        $query->where('position_id = ' . $this->_convertPositionID[$oldPositionID] );
        $db->setQuery($query);
		$db->execute();
        $p_projectposition->id = $db->loadResult();
        
        if ( $p_projectposition->id )
        {
            
        }
        else
        {
            
try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_project_position', $p_projectposition);
$insertID = Factory::getDbo()->insertid();
$p_projectposition->id = $insertID;
$this->_convertProjectPositionID[$oldID] = $insertID;
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf(	'Created new projectposition data: %1$s - %2$s',
		"</span><strong>".Text::_($this->_getObjectName('position',$p_projectposition->position_id)).'</strong><span style="color:'.$this->storeSuccessColor.'">',
		"</span><strong>".$p_projectposition->position_id.'</strong>');
$my_text .= '<br />';
}	
catch (Exception $e){
$my_text .= __LINE__.' error on ProjectPosition import: ';
$my_text .= '#'.$oldID.'#<br />';
$my_text .= $e->getMessage().'<br />';
}               
            
        }
			
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importTeamPlayer()
	 * 
	 * @return
	 */
	private function _importTeamPlayer()
	{
	   $app = Factory::getApplication();
		$my_text = '';
if( !isset($this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]) ) {
    $this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
}		
		if (!isset($this->_datas['teamplayer']) || count($this->_datas['teamplayer'])==0){return true;}

		if (!isset($this->_datas['person']) || count($this->_datas['person'])==0){return true;}
		if ((!isset($this->_newpersonsid) || count($this->_newpersonsid)==0) &&
			(!isset($this->_dbpersonsid) || count($this->_dbpersonsid)==0)){return true;}

		foreach ($this->_datas['teamplayer'] as $key => $teamplayer)
		{
            
			$import_teamplayer = $this->_datas['teamplayer'][$key];
$p_teamplayer = $this->_importDataForSave($import_teamplayer,'team_player');
            
			$oldID = $this->_getDataFromObject($import_teamplayer,'id');
			$oldTeamID = $this->_getDataFromObject($import_teamplayer,'projectteam_id');
			$oldPersonID = $this->_getDataFromObject($import_teamplayer,'person_id');
            
			if (!isset($this->_convertProjectTeamID[$oldTeamID]) ||
				!isset($this->_convertPersonID[$oldPersonID]))
			{
				$my_text .= '<span style="color:red">';
				$my_text .= Text::sprintf(	'COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_1',
								"</span><strong>$oldID</strong><span style='color:red'>",
								"</span><strong>$oldTeamID</strong><span style='color:red'>",
								"</span><strong>$oldPersonID</strong>").'<br />';
				continue;
			}
			$p_teamplayer->projectteam_id = $this->_convertProjectTeamID[$oldTeamID];
			$p_teamplayer->person_id = $this->_convertPersonID[$oldPersonID];
			$oldPositionID = $this->_getDataFromObject($import_teamplayer,'project_position_id');
			if (isset($this->_convertProjectPositionID[$oldPositionID]))
			{
				$p_teamplayer->project_position_id = $this->_convertProjectPositionID[$oldPositionID];
			}
			
            $p_teamplayer->published = 1;
            
try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_team_player', $p_teamplayer);
$insertID = Factory::getDbo()->insertid();
$dPerson = $this->_getPersonName($p_teamplayer->person_id);
				$project_position_id = $p_teamplayer->project_position_id;
				if($project_position_id>0) {
					$query ='SELECT *
								FROM #__sportsmanagement_project_position
								WHERE	id='.$project_position_id;
					Factory::getDbo()->setQuery($query);
					sportsmanagementModeldatabasetool::runJoomlaQuery();
					$object = Factory::getDbo()->loadObject();
					$position_id = $object->position_id;
					$dPosName = Text::_($this->_getObjectName('position',$position_id));
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= Text::sprintf(	'COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_2',
									'</span><strong>'.$this->_getTeamName($p_teamplayer->projectteam_id).'</strong><span style="color:'.$this->storeSuccessColor.'">',
									'</span><strong>'.$dPerson->lastname,$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
									"</span><strong>$dPosName</strong>");
					$my_text .= '<br />';
				} else {
					$dPosName='<span style="color:orange">'.Text::_('Has no position').'</span>';
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= Text::sprintf(	'COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_2',
									'</span><strong>'.$this->_getTeamName($p_teamplayer->projectteam_id).'</strong><span style="color:'.$this->storeSuccessColor.'">',
									'</span><strong>'.$dPerson->lastname,$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
									"</span><strong>$dPosName</strong>");
					$my_text .= '<br />';
				}
}	
catch (Exception $e){
$insertID = 0;
$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
$my_text .= $oldID.'<br />';
$my_text .= __LINE__.' '.$e->getMessage().'<br />';	
}	

            
			//$insertID = $p_teamplayer->id;//Factory::getDbo()->insertid();
			$this->_convertTeamPlayerID[$oldID] = $insertID;
		}

		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importTeamStaff()
	 * 
	 * @return
	 */
	private function _importTeamStaff()
	{
		$my_text = '';
if( !isset($this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]) ) {
    $this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
}		
		if (!isset($this->_datas['teamstaff']) || count($this->_datas['teamstaff'])==0){return true;}

		if (!isset($this->_datas['person']) || count($this->_datas['person'])==0){return true;}
		if ((!isset($this->_newpersonsid) || count($this->_newpersonsid)==0) &&
			(!isset($this->_dbpersonsid) || count($this->_dbpersonsid)==0)){return true;}

		foreach ($this->_datas['teamstaff'] as $key => $teamstaff)
		{
			$import_teamstaff = $this->_datas['teamstaff'][$key];
$p_teamstaff = $this->_importDataForSave($import_teamstaff,'team_staff');
			$oldID = $this->_getDataFromObject($import_teamstaff,'id');
			$oldProjectTeamID = $this->_getDataFromObject($import_teamstaff,'projectteam_id');
			$oldPersonID = $this->_getDataFromObject($import_teamstaff,'person_id');
            
            
            
			if (!isset($this->_convertProjectTeamID[$oldProjectTeamID]) ||
				!isset($this->_convertPersonID[$oldPersonID]))
			{
				$my_text .= '<span style="color:red">';
				$my_text .= Text::sprintf(	'Skipping import of TeamStaff-ID %1$s. Old-ProjectTeamID: %2$s - Old-PersonID: %3$s',
								"</span><strong>$oldID</strong><span style='color:red'>",
								"</span><strong>$oldProjectTeamID</strong><span style='color:red'>",
								"</span><strong>$oldPersonID</strong>").'<br />';
				continue;
			}
			$p_teamstaff->projectteam_id = $this->_convertProjectTeamID[$oldProjectTeamID];
			$p_teamstaff->person_id = $this->_convertPersonID[$oldPersonID];
			$oldPositionID = $this->_getDataFromObject($import_teamstaff,'project_position_id');
            
			if (isset($this->_convertProjectPositionID[$oldPositionID]))
			{
				$p_teamstaff->project_position_id = $this->_convertProjectPositionID[$oldPositionID];
			}

            $p_teamstaff->published = 1;

try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_team_staff', $p_teamstaff);
$insertID = Factory::getDbo()->insertid();
$dPerson=$this->_getPersonName($p_teamstaff->person_id);
				$project_position_id = $p_teamstaff->project_position_id;
				if($project_position_id>0) 
                {
					$query ='SELECT * FROM #__sportsmanagement_project_position WHERE id='.$project_position_id;
					Factory::getDbo()->setQuery($query);
					sportsmanagementModeldatabasetool::runJoomlaQuery();
					$object = Factory::getDbo()->loadObject();
					$position_id = $object->position_id;
					$dPosName=Text::_($this->_getObjectName('position',$position_id));
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= Text::sprintf(	'Created new teamstaff data. Team: %1$s - Person: %2$s,%3$s - Position: %4$s',
									'</span><strong>'.$this->_getTeamName($p_teamstaff->projectteam_id).'</strong><span style="color:'.$this->storeSuccessColor.'">',
									'</span><strong>'.$dPerson->lastname,
									$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
									"</span><strong>$dPosName</strong>");
					$my_text .= '<br />';
				} 
                else 
                {
					$dPosName='<span style="color:orange">'.Text::_('Has no position').'</span>';
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= Text::sprintf(	'Created new teamstaff data. Team: %1$s - Person: %2$s,%3$s - Position: %4$s',
									'</span><strong>'.$this->_getTeamName($p_teamstaff->projectteam_id).'</strong><span style="color:'.$this->storeSuccessColor.'">',
									'</span><strong>'.$dPerson->lastname,$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
									"</span><strong>$dPosName</strong>");
					$my_text .= '<br />';
				}
}	
catch (Exception $e){
$insertID = 0;
$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
$my_text .= $oldID.'<br />';
$my_text .= __LINE__.' '.$e->getMessage().'<br />';	
}	



			//$insertID=$p_teamstaff->id;//Factory::getDbo()->insertid();
			$this->_convertTeamStaffID[$oldID] = $insertID;
		}

		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importTeamTraining()
	 * 
	 * @return
	 */
	private function _importTeamTraining()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       
		$my_text='';
		if (!isset($this->_datas['teamtraining']) || count($this->_datas['teamtraining'])==0)
        {
        return true;
        }

		foreach ($this->_datas['teamtraining'] as $key => $teamtraining)
		{
			
            $mdl = BaseDatabaseModel::getInstance("trainingdata", "sportsmanagementModel");
            $p_teamtraining = $mdl->getTable();
            
			$import_teamtraining = $this->_datas['teamtraining'][$key];
			$oldID = $this->_getDataFromObject($import_teamtraining,'id');
			$p_teamtraining->set('project_team_id',$this->_convertProjectTeamID[$this->_getDataFromObject($import_teamtraining,'project_team_id')]);
			$p_teamtraining->set('project_id',$this->_project_id);
            // die team_id selektieren
            // Select some fields
            $query->clear();
        $query->select('st.team_id');
		// From the table
		$query->from('#__sportsmanagement_season_team_id AS st');
        $query->join('INNER', '#__sportsmanagement_project_team AS pt on pt.team_id = st.id');  
        $query->where('pt.id = '.(int)$p_teamtraining->project_team_id);    
		Factory::getDbo()->setQuery($query);
        sportsmanagementModeldatabasetool::runJoomlaQuery();
        $team_id = Factory::getDbo()->loadResult();
       
            $p_teamtraining->set('team_id',$team_id);
			$p_teamtraining->set('dayofweek',$this->_getDataFromObject($import_teamtraining,'dayofweek'));
			$p_teamtraining->set('time_start',$this->_getDataFromObject($import_teamtraining,'time_start'));
			$p_teamtraining->set('time_end',$this->_getDataFromObject($import_teamtraining,'time_end'));
			$p_teamtraining->set('place',$this->_getDataFromObject($import_teamtraining,'place'));
			$p_teamtraining->set('notes',$this->_getDataFromObject($import_teamtraining,'notes'));

/**
 * nur wenn keine trainingsdaten vorhanden sind sollen auch welche angelegt werden
 */
            $query->clear();
            $query->select('id');
            $query->from('#__sportsmanagement_team_trainingdata');
            $query->where('project_id = '.(int)$p_teamtraining->project_id);  
            $query->where('team_id = '.(int)$p_teamtraining->team_id);  
            $query->where('project_team_id = '.(int)$p_teamtraining->project_team_id);  
            $query->where('dayofweek = '.(int)$p_teamtraining->dayofweek);  
            $query->where('time_start = '.(int)$p_teamtraining->time_start);  
            $query->where('time_end = '.(int)$p_teamtraining->time_end);  
            Factory::getDbo()->setQuery($query);
            sportsmanagementModeldatabasetool::runJoomlaQuery();
//            $team_tr_id = Factory::getDbo()->loadResult();
            if ( !Factory::getDbo()->loadResult() )
            {
            if ($p_teamtraining->store()===false)
			{
				$my_text .= 'error on teamtraining import: ';
				$my_text .= $oldID;
				$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML_IMPORT_TEAMTRAINING_0')] = $my_text;
			}
			else
			{
				$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
				$my_text .= Text::sprintf(	'Created new teamtraining data. Team: [%1$s]',
								'</span><strong>'.$this-> _getTeamName($p_teamtraining->project_team_id).'</strong>');
				$my_text .= '<br />';
			}
            }
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importRounds()
	 * 
	 * @return
	 */
	private function _importRounds()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       
		$my_text = '';
if( !isset($this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]) ) {
    $this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
}		
		if ( !isset($this->_datas['round']) || count($this->_datas['round']) == 0 )
        {
        return true;
        }

		foreach ($this->_datas['round'] as $key => $round)
		{
			$import_round = $this->_datas['round'][$key];
            $p_round = $this->_importDataForSave($import_round,'round');
			$oldId = (int)$round->id;

$p_round->alias = OutputFilter::stringURLSafe($p_round->name);
$p_round->project_id = $this->_project_id;
// if the roundcode field is empty,it is an old .jlg-Import file
if (array_key_exists('matchcode', $import_round)) {
$p_round->roundcode = $this->_getDataFromObject($round,'matchcode');
}

try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_round', $p_round);
$insertID = Factory::getDbo()->insertid();
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_1',"</span><strong>$p_round->name</strong>");
$my_text .= '<br />';
}	
catch (Exception $e){
$insertID = 0;    
$my_text .= ' error on round import: ';
$my_text .= '#'.$oldID.'#<br />';
$my_text .= __LINE__.' '.$e->getMessage().'<br />';
}    
$this->_convertRoundID[$oldId] = $insertID;

		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importMatches()
	 * 
	 * @return
	 */
	private function _importMatches()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       
		$my_text = '';
if( !isset($this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]) ) {
    $this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
}		
		if ( !isset($this->_datas['match']) || count($this->_datas['match']) == 0 )
        {
        return true;
        }

		if ( !isset($this->_datas['team']) || count($this->_datas['team']) == 0 )
        {
        return true;
        }
		if (( !isset($this->_newteams) || count($this->_newteams) == 0 ) &&
			( !isset($this->_dbteamsid) || count($this->_dbteamsid) == 0 ))
            {
            return true;
            }

		foreach ($this->_datas['match'] as $key => $match)
		{
$import_match = $this->_datas['match'][$key];
$p_match = new stdClass();
$oldId = (int)$match->id;
$p_match = $this->_importDataForSave($import_match,'match');
$oldteam1 = 0;
$oldteam2 = 0;
$p_match->round_id = $this->_convertRoundID[$this->_getDataFromObject($match,'round_id')];
$p_match->projectteam1_id = $this->_convertProjectTeamID[intval($this->_getDataFromObject($match,'projectteam1_id'))];            
$p_match->projectteam2_id = $this->_convertProjectTeamID[intval($this->_getDataFromObject($match,'projectteam2_id'))];            
if (!empty($this->_convertPlaygroundID))
{
if (array_key_exists((int)$this->_getDataFromObject($match,'playground_id'),$this->_convertPlaygroundID))
{
$p_match->playground_id = $this->_convertPlaygroundID[$this->_getDataFromObject($match,'playground_id')];
}
else
{
$p_match->playground_id = 0;
}
}
if ( $p_match->playground_id == 0 )
{
$p_match->playground_id = NULL;
}
$p_match->match_timestamp = sportsmanagementHelper::getTimestamp($this->_getDataFromObject($match,'match_date'));
$p_match->import_match_id = $this->_getDataFromObject($match,'id');
if ( isset($this->_convertDivisionID) )
{
$p_match->division_id = $this->_convertDivisionID[$this->_getDataFromObject($match,'division_id')];
}

if (array_key_exists('matchpart1', $import_match)) {
$p_match->projectteam1_id = $this->_convertProjectTeamID[intval($match->matchpart1)];
}
if (array_key_exists('matchpart2', $import_match)) {
$p_match->projectteam2_id = $this->_convertProjectTeamID[intval($match->matchpart2)];
}
if (array_key_exists('matchpart1_result', $import_match)) {
$p_match->team1_result = $this->_getDataFromObject($match,'matchpart1_result');
}
if (array_key_exists('matchpart2_result', $import_match)) {
$p_match->team2_result = $this->_getDataFromObject($match,'matchpart2_result');
}

if (array_key_exists('matchpart1_bonus', $import_match)) {
$p_match->team1_bonus = $this->_getDataFromObject($match,'matchpart1_bonus');
}
if (array_key_exists('matchpart2_bonus', $import_match)) {
$p_match->team2_bonus = $this->_getDataFromObject($match,'matchpart2_bonus');
}
if (array_key_exists('matchpart1_legs', $import_match)) {
$p_match->team1_legs = $this->_getDataFromObject($match,'matchpart1_legs');
}
if (array_key_exists('matchpart2_legs', $import_match)) {
$p_match->team2_legs = $this->_getDataFromObject($match,'matchpart2_legs');
}

if (array_key_exists('matchpart1_result_split', $import_match)) {
$p_match->team1_result_split = $this->_getDataFromObject($match,'matchpart1_result_split');
}
if (array_key_exists('matchpart2_result_split', $import_match)) {
$p_match->team2_result_split = $this->_getDataFromObject($match,'matchpart2_result_split');
}

if (array_key_exists('matchpart1_result_ot', $import_match)) {
$p_match->team1_result_ot = $this->_getDataFromObject($match,'matchpart1_result_ot');
}
if (array_key_exists('matchpart2_result_ot', $import_match)) {
$p_match->team2_result_ot = $this->_getDataFromObject($match,'matchpart2_result_ot');
}

if (array_key_exists('matchpart1_result_decision', $import_match)) {
$p_match->team1_result_decision = $this->_getDataFromObject($match,'matchpart1_result_decision');
}
if (array_key_exists('matchpart2_result_decision', $import_match)) {
$p_match->team2_result_decision = $this->_getDataFromObject($match,'matchpart2_result_decision');
}

try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_match', $p_match);
$insertID = Factory::getDbo()->insertid();
$p_match->id = $insertID;
if ( $this->import_version == 'NEW' )
{
if ($match->projectteam1_id > 0)
{
$teamname1 = $this->_getTeamName($p_match->projectteam1_id);
}
else
{
$teamname1 = '<span style="color:orange">'.Text::_('Home-Team not asigned').'</span>';
}
if ($match->projectteam2_id > 0)
{
$teamname2 = $this->_getTeamName($p_match->projectteam2_id);
}
else
{
$teamname2='<span style="color:orange">'.Text::_('Guest-Team not asigned').'</span>';
}
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf('Added to round: %1$s / Match: %2$s - %3$s / ProjectTeamID New: %4$s - %5$s / ProjectTeamID Old: %6$s - %7$s',
	'</span><strong>'.$this->_getRoundName($this->_convertRoundID[$this->_getDataFromObject($match,'round_id')]).'</strong><span style="color:'.$this->storeSuccessColor.'">',
	"</span><strong>$teamname1</strong>",
        "<strong>$teamname2</strong>",
	"<strong>$match->projectteam1_id</strong>",
        "<strong>$match->projectteam2_id</strong>",
        "<strong>$oldteam1</strong>",
        "<strong>$oldteam2</strong>"
        );
$my_text .= '<br />';
}
                
if ( $this->import_version == 'OLD' )
{
if ($match->matchpart1 > 0)
{
$teamname1 = $this->_getTeamName2($this->_convertTeamID[intval($match->matchpart1)]);
}
else
{
$teamname1 = '<span style="color:orange">'.Text::_('Home-Team not asigned').'</span>';
}
if ($match->matchpart2 > 0)
{
$teamname2 = $this->_getTeamName2($this->_convertTeamID[intval($match->matchpart2)]);
}
else
{
$teamname2 = '<span style="color:orange">'.Text::_('Guest-Team not asigned').'</span>';
}
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf(	'Added to round: %1$s / Match: %2$s - %3$s',
			'</span><strong>'.$this->_getRoundName($this->_convertRoundID[$this->_getDataFromObject($match,'round_id')]).'</strong><span style="color:'.$this->storeSuccessColor.'">',
			"</span><strong>$teamname1</strong>",
			"<strong>$teamname2</strong>");
$my_text .= '<br />';
}

}	
catch (Exception $e){
$p_match->id = 0;    
$my_text .= 'error on match import: ';
$my_text .= $oldID;
$my_text .= $e->getMessage().'<br />';
$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;	
}	

			$insertID = $p_match->id;
			$this->_convertMatchID[$oldId] = $insertID;
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importMatchPlayer()
	 * 
	 * @return
	 */
	private function _importMatchPlayer()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       
		$my_text = '';
		if (!isset($this->_datas['matchplayer']) || count($this->_datas['matchplayer'])==0)
        {
            return true;
            }

		if (!isset($this->_datas['person']) || count($this->_datas['person'])==0)
        {
            return true;
            }
		if ((!isset($this->_newpersonsid) || count($this->_newpersonsid)==0) &&
			(!isset($this->_dbpersonsid) || count($this->_dbpersonsid)==0))
            {
                return true;
                }

		foreach ($this->_datas['matchplayer'] as $key => $matchplayer)
		{
			$import_matchplayer = $this->_datas['matchplayer'][$key];
			$oldID = $this->_getDataFromObject($import_matchplayer,'id');
			
            $p_matchplayer = new stdClass();
			$oldMatchID = $this->_getDataFromObject($import_matchplayer,'match_id');
			$oldTeamPlayerID = $this->_getDataFromObject($import_matchplayer,'teamplayer_id');
			if (!isset($this->_convertMatchID[$oldMatchID]) ||
				!isset($this->_convertTeamPlayerID[$oldTeamPlayerID]))
			{
				$my_text .= '<span style="color:red">';
				$my_text .= Text::sprintf(	'Skipping import of MatchPlayer-ID [%1$s]. Old-MatchID: [%2$s] - Old-TeamPlayerID: [%3$s]',
								"</span><strong>$oldID</strong><span style='color:red'>",
								"</span><strong>$oldMatchID</strong><span style='color:red'>",
								"</span><strong>$oldTeamPlayerID</strong>").'<br />';
				continue;
			}
			$p_matchplayer->match_id = $this->_convertMatchID[$oldMatchID];
			$p_matchplayer->teamplayer_id = $this->_convertTeamPlayerID[$oldTeamPlayerID];
            $newTeamPlayerID = $this->_convertTeamPlayerID[$oldTeamPlayerID];
            
			$oldPositionID = $this->_getDataFromObject($import_matchplayer,'project_position_id');
			if (isset($this->_convertProjectPositionID[$oldPositionID]))
			{
				$p_matchplayer->project_position_id = $this->_convertProjectPositionID[$oldPositionID];
			}
			$p_matchplayer->came_in = $this->_getDataFromObject($import_matchplayer,'came_in');
			if ($import_matchplayer->in_for > 0)
			{
				$oldTeamPlayerID = $this->_getDataFromObject($import_matchplayer,'in_for');
				if (isset($this->_convertTeamPlayerID[$oldTeamPlayerID]))
				{
					$p_matchplayer->in_for = $this->_convertTeamPlayerID[$oldTeamPlayerID];
				}
			}
			$p_matchplayer->out = $this->_getDataFromObject($import_matchplayer,'out');
			$p_matchplayer->in_out_time = $this->_getDataFromObject($import_matchplayer,'in_out_time');
			$p_matchplayer->ordering = $this->_getDataFromObject($import_matchplayer,'ordering');

try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_match_player', $p_matchplayer);
$dPerson = $this->_getPersonFromTeamPlayer($p_matchplayer->teamplayer_id);
$dPosName = (($p_matchplayer->project_position_id==0) ?
		'<span style="color:orange">'.Text::_('Has no position').'</span>' :
		$this->_getProjectPositionName($p_matchplayer->project_position_id));
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf(	'Created new matchplayer data. MatchID: %1$s - Player: %2$s,%3$s - Position: %4$s - oldTeamPlayerID : %5$s - newTeamPlayerID : %6$s',
		'</span><strong>'.$p_matchplayer->match_id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
		'</span><strong>'.$dPerson->lastname,
		$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
		"</span><strong>$dPosName</strong>",
        "</span><strong>$oldTeamPlayerID</strong>",
        "</span><strong>$newTeamPlayerID</strong>");
$my_text .= '<br />';                  
}
catch (Exception $e){
$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
$my_text .= __LINE__.' '.Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__)).'</strong></span><br />';
$my_text .= 'error on matchplayer import: ';
$my_text .= $oldID;
$my_text .= __LINE__.' '.$e->getMessage().'<br />';
}           
            
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importMatchStaff()
	 * 
	 * @return
	 */
	private function _importMatchStaff()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       
		$my_text = '';
		if (!isset($this->_datas['matchstaff']) || count($this->_datas['matchstaff'])==0)
        {
            return true;
            }

		if (!isset($this->_datas['person']) || count($this->_datas['person'])==0)
        {
            return true;
            }
		if ((!isset($this->_newpersonsid) || count($this->_newpersonsid)==0) &&
			(!isset($this->_dbpersonsid) || count($this->_dbpersonsid)==0))
            {
                return true;
                }

		foreach ($this->_datas['matchstaff'] as $key => $matchstaff)
		{
			$import_matchstaff = $this->_datas['matchstaff'][$key];
			$oldID = $this->_getDataFromObject($import_matchstaff,'id');
            $p_matchstaff = new stdClass();
            
			$oldMatchID = $this->_getDataFromObject($import_matchstaff,'match_id');
			$oldTeamStaffID = $this->_getDataFromObject($import_matchstaff,'team_staff_id');
			if (!isset($this->_convertMatchID[$oldMatchID]) ||
				!isset($this->_convertTeamStaffID[$oldTeamStaffID]))
			{
				$my_text .= '<span style="color:red">';
				$my_text .= Text::sprintf(	'Skipping import of MatchStaff-ID [%1$s]. Old-MatchID: [%2$s] - Old-StaffID: [%3$s]',
								"</span><strong>$oldID</strong><span style='color:red'>",
								"</span><strong>$oldMatchID</strong><span style='color:red'>",
								"</span><strong>$oldTeamStaffID</strong>").'<br />';
				continue;
			}
			$p_matchstaff->match_id = $this->_convertMatchID[$oldMatchID];
			$p_matchstaff->team_staff_id = $this->_convertTeamStaffID[$oldTeamStaffID];
			$oldPositionID = $this->_getDataFromObject($import_matchstaff,'project_position_id');
			if (isset($this->_convertProjectPositionID[$oldPositionID]))
			{
				$p_matchstaff->project_position_id = $this->_convertProjectPositionID[$oldPositionID];
			}
			$p_matchstaff->ordering = $this->_getDataFromObject($import_matchstaff,'ordering');
            
try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_match_staff', $p_matchstaff);
$dPerson = $this->_getPersonFromTeamStaff($p_matchstaff->team_staff_id);
$dPosName=(($p_matchstaff->project_position_id==0) ?
		'<span style="color:orange">'.Text::_('Has no position').'</span>' :
		$this->_getProjectPositionName($p_matchstaff->project_position_id));
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf(	'Created new matchstaff data. MatchID: %1$s - Staff: %2$s,%3$s - Position: %4$s',
			'</span><strong>'.$p_matchstaff->match_id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
			'</span><strong>'.$dPerson->lastname,
			$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
			"</span><strong>$dPosName</strong>");
$my_text .= '<br />';
}
catch (Exception $e){
$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
$my_text .= __LINE__.' '.Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__)).'</strong></span><br />';
$my_text .= 'error on matchstaff import: ';
$my_text .= $oldID;
$my_text .= __LINE__.' '.$e->getMessage().'<br />';
}              
            
            
            
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importMatchReferee()
	 * 
	 * @return
	 */
	private function _importMatchReferee()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       
		$my_text = '';
		if (!isset($this->_datas['matchreferee']) || count($this->_datas['matchreferee'])==0)
        {
            return true;
            }

		if (!isset($this->_datas['person']) || count($this->_datas['person'])==0)
        {
            return true;
            }
		if ((!isset($this->_newpersonsid) || count($this->_newpersonsid)==0) &&
			(!isset($this->_dbpersonsid) || count($this->_dbpersonsid)==0))
            {
                return true;
                }

		foreach ($this->_datas['matchreferee'] as $key => $matchreferee)
		{
			$import_matchreferee = $this->_datas['matchreferee'][$key];
			$oldID = $this->_getDataFromObject($import_matchreferee,'id');
			
            $p_matchreferee = new stdClass();
            
			$oldMatchID = $this->_getDataFromObject($import_matchreferee,'match_id');
			$oldProjectRefereeID = $this->_getDataFromObject($import_matchreferee,'project_referee_id');
			if (!isset($this->_convertMatchID[$oldMatchID]) ||
				!isset($this->_convertProjectRefereeID[$oldProjectRefereeID]))
			{
				$my_text .= '<span style="color:red">';
				$my_text .= Text::sprintf(	'Skipping import of MatchReferee-ID [%1$s]. Old-MatchID: [%2$s] - Old-RefereeID: [%3$s]',
								"</span><strong>$oldID</strong><span style='color:red'>",
								"</span><strong>$oldMatchID</strong><span style='color:red'>",
								"</span><strong>$oldProjectRefereeID</strong>").'<br />';
				continue;
			}
			$p_matchreferee->match_id = $this->_convertMatchID[$oldMatchID];
			$p_matchreferee->project_referee_id = $this->_convertProjectRefereeID[$oldProjectRefereeID];
			$oldPositionID = $this->_getDataFromObject($import_matchreferee,'project_position_id');
			if (isset($this->_convertProjectPositionID[$oldPositionID]))
			{
				$p_matchreferee->project_position_id = $this->_convertProjectPositionID[$oldPositionID];
			}
			$p_matchreferee->ordering = $this->_getDataFromObject($import_matchreferee,'ordering') ? $this->_getDataFromObject($import_matchreferee,'ordering') : 0;
            
try
{
$result = Factory::getDbo()->insertObject('#__sportsmanagement_match_referee', $p_matchreferee);
$dPerson = $this->_getPersonFromProjectReferee($p_matchreferee->project_referee_id);
$dPosName=(($p_matchreferee->project_position_id==0) ?
		'<span style="color:orange">'.Text::_('Has no position').'</span>' :
		$this->_getProjectPositionName($p_matchreferee->project_position_id));
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf(	'Created new matchreferee data. MatchID: %1$s - Referee: %2$s,%3$s - Position: %4$s',
		'</span><strong>'.$p_matchreferee->match_id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
		'</span><strong>'.$dPerson->lastname,
		$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
		"</span><strong>$dPosName</strong>");
$my_text .= '<br />';
}
catch (Exception $e)
{
$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
$my_text .= __LINE__.' error on matchreferee import: ';
$my_text .= $oldID.'<br />';
$my_text .= $e->getMessage().'<br />';
}
            
            
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importMatchEvent()
	 * 
	 * @return
	 */
	private function _importMatchEvent()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       
		$my_text = '';
		if (!isset($this->_datas['matchevent']) || count($this->_datas['matchevent'])==0)
        {
            return true;
            }

		if (!isset($this->_datas['person']) || count($this->_datas['person'])==0)
        {
            return true;
            }
		if ((!isset($this->_newpersonsid) || count($this->_newpersonsid)==0) &&
			(!isset($this->_dbpersonsid) || count($this->_dbpersonsid)==0))
            {
                return true;
                }
		if (!isset($this->_datas['event']) || count($this->_datas['event'])==0)
        {
            return true;
            }
		if ((!isset($this->_neweventsid) || count($this->_neweventsid)==0) &&
			(!isset($this->_dbeventsid) || count($this->_dbeventsid)==0))
            {
                return true;
                }

		foreach ($this->_datas['matchevent'] as $key => $matchevent)
		{
			$import_matchevent = $this->_datas['matchevent'][$key];
			$oldID = $this->_getDataFromObject($import_matchevent,'id');
			
$p_matchevent = $this->_importDataForSave($import_matchevent,'match_event');
$p_matchevent->match_id = $this->_convertMatchID[$this->_getDataFromObject($import_matchevent,'match_id')];
$p_matchevent->projectteam_id = $this->_convertProjectTeamID[$this->_getDataFromObject($import_matchevent,'projectteam_id')];
$p_matchevent->teamplayer_id = $this->_convertTeamPlayerID[$this->_getDataFromObject($import_matchevent,'teamplayer_id')];
$p_matchevent->teamplayer_id2 = $this->_convertTeamPlayerID[$this->_getDataFromObject($import_matchevent,'teamplayer_id2')];
$p_matchevent->event_type_id = $this->_convertEventID[$this->_getDataFromObject($import_matchevent,'event_type_id')];
            
try {
$result = Factory::getDbo()->insertObject('#__sportsmanagement_match_event', $p_matchevent);
$insertID = Factory::getDbo()->insertid();
$dPerson = $this->_getPersonFromTeamPlayer($p_matchevent->teamplayer_id);
$dEventName=(($p_matchevent->event_type_id==0) ?
			'<span style="color:orange">'.Text::_('Has no event').'</span>' :
			Text::_($this->_getObjectName('eventtype',$p_matchevent->event_type_id)));
$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf(	'Created new matchevent data. MatchID: %1$s - Player: %2$s,%3$s - Eventtime: %4$s - Event: %5$s',
			'</span><strong>'.$p_matchevent->match_id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
			'</span><strong>'.$dPerson->lastname,
			$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
			'</span><strong>'.$p_matchevent->event_time.'</strong><span style="color:'.$this->storeSuccessColor.'">',
			"</span><strong>$dEventName</strong>");
$my_text .= '<br />';
}	
catch (Exception $e){
$my_text .= ' error on matchevent import: ';
$my_text .= '#'.$oldID.'#<br />';
$my_text .= __LINE__.' '.$e->getMessage().'<br />';
}              
            
		}
		$this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importPositionStatistic()
	 * 
	 * @return
	 */
	private function _importPositionStatistic()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       
		$my_text='';
		if (!isset($this->_datas['positionstatistic']) || count($this->_datas['positionstatistic'])==0)
        {
            return true;
            }

		if ((!isset($this->_newpositionsid) || count($this->_newpositionsid)==0) &&
			(!isset($this->_dbpositionsid) || count($this->_dbpositionsid)==0))
            {
                return true;
                }
		if ((!isset($this->_newstatisticsid) || count($this->_newstatisticsid)==0) &&
			(!isset($this->_dbstatisticsid) || count($this->_dbstatisticsid)==0))
            {
                return true;
                }

		if (!isset($this->_datas['statistic']) || count($this->_datas['statistic'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= Text::sprintf('Warning: Skipped %1$s records for position statistic data because there is no statistic data included!',count($this->_datas['positionstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing position statistic data:']=$my_text;
			return true;
		}

		if (!isset($this->_datas['position']) || count($this->_datas['position'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= Text::sprintf('Warning: Skipped %1$s records for position statistic data because there is no position data included!',count($this->_datas['positionstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing position statistic data:']=$my_text;
			return true;
		}

		foreach ($this->_datas['positionstatistic'] as $key => $positionstatistic)
		{
			$import_positionstatistic=$this->_datas['positionstatistic'][$key];
			$oldID=$this->_getDataFromObject($import_positionstatistic,'id');

			
            $mdl = BaseDatabaseModel::getInstance("positionstatistic", "sportsmanagementModel");
            $p_positionstatistic = $mdl->getTable();

			$p_positionstatistic->set('position_id',$this->_convertPositionID[$this->_getDataFromObject($import_positionstatistic,'position_id')]);
			$p_positionstatistic->set('statistic_id',$this->_convertStatisticID[$this->_getDataFromObject($import_positionstatistic,'statistic_id')]);
			//$p_positionstatistic->set('ordering',$this->_getDataFromObject($import_positionstatistic,'ordering'));

$query->clear();
			// Select some fields
        $query->select('id');
		// From the table
		$query->from('#__sportsmanagement_position_statistic');
        $query->where('position_id = '.(int)$p_positionstatistic->position_id);
        $query->where('statistic_id = '.(int)$p_positionstatistic->statistic_id);

			Factory::getDbo()->setQuery($query); 
            sportsmanagementModeldatabasetool::runJoomlaQuery();
			if ($object=Factory::getDbo()->loadObject())
			{
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= Text::sprintf(	'Using existing positionstatistic data. Position: %1$s - Statistic: %2$s',
								'</span><strong>'.$this->_getObjectName('position',$p_positionstatistic->position_id).'</strong><span style="color:'.$this->existingInDbColor.'">',
								'</span><strong>'.$this->_getObjectName('statistic',$p_positionstatistic->statistic_id).'</strong>');
				$my_text .= '<br />';
			}
			else
			{
				if ($p_positionstatistic->store()===false)
				{
					$my_text .= 'error on positionstatistic import: ';
					$my_text .= $oldID;
					$this->_success_text['Importing position statistic data:']=$my_text;
				}
				else
				{
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= Text::sprintf(	'Created new position statistic data. Position: %1$s - Statistic: %2$s',
									'</span><strong>'.$this->_getObjectName('position',$p_positionstatistic->position_id).'</strong><span style="color:'.$this->storeSuccessColor.'">',
									'</span><strong>'.$this->_getObjectName('statistic',$p_positionstatistic->statistic_id).'</strong>');
					$my_text .= '<br />';
				}
			}
		}
		$this->_success_text['Importing position statistic data:']=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importMatchStaffStatistic()
	 * 
	 * @return
	 */
	private function _importMatchStaffStatistic()
	{
	   $app = Factory::getApplication();
       $query = Factory::getDbo()->getQuery(true);
       
		$my_text='';
		if (!isset($this->_datas['matchstaffstatistic']) || count($this->_datas['matchstaffstatistic'])==0){return true;}

		if ((!isset($this->_newstatisticsid) || count($this->_newstatisticsid)==0) &&
			(!isset($this->_dbstatisticsid) || count($this->_dbstatisticsid)==0)){return true;}

		if (!isset($this->_datas['statistic']) || count($this->_datas['statistic'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= Text::sprintf('Warning: Skipped %1$s records for match staff statistic data because there is no statistic data included!',count($this->_datas['matchstaffstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match staff statistic data:']=$my_text;
			return true;
		}
		if (!isset($this->_datas['match']) || count($this->_datas['match'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= Text::sprintf('Warning: Skipped %1$s records for match statistic data because there is no match data included!',count($this->_datas['matchstaffstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match staff statistic data:']=$my_text;
			return true;
		}
		if (!isset($this->_datas['projectteam']) || count($this->_datas['projectteam'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= Text::sprintf('Warning: Skipped %1$s records for match statistic data because there is no projectteam data included!',count($this->_datas['matchstaffstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match staff statistic data:']=$my_text;
			return true;
		}
		if (!isset($this->_datas['teamstaff']) || count($this->_datas['teamstaff'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= Text::sprintf('Warning: Skipped %1$s records for match statistic data because there is no teamstaff data included!',count($this->_datas['matchstaffstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match staff statistic data:']=$my_text;
			return true;
		}
		foreach ($this->_datas['matchstaffstatistic'] as $key => $matchstaffstatistic)
		{
			$import_matchstaffstatistic=$this->_datas['matchstaffstatistic'][$key];
			$oldID=$this->_getDataFromObject($import_matchstaffstatistic,'id');

			
            $mdl = BaseDatabaseModel::getInstance("matchstaffstatistic", "sportsmanagementModel");
            $p_matchstaffstatistic = $mdl->getTable();

			$p_matchstaffstatistic->set('match_id',$this->_convertMatchID[$this->_getDataFromObject($import_matchstaffstatistic,'match_id')]);
			$p_matchstaffstatistic->set('projectteam_id',$this->_convertProjectTeamID[$this->_getDataFromObject($import_matchstaffstatistic,'projectteam_id')]);
			$p_matchstaffstatistic->set('team_staff_id',$this->_convertTeamStaffID[$this->_getDataFromObject($import_matchstaffstatistic,'team_staff_id')]);
			$p_matchstaffstatistic->set('statistic_id',$this->_convertStatisticID[$this->_getDataFromObject($import_matchstaffstatistic,'statistic_id')]);
			$p_matchstaffstatistic->set('value',$this->_getDataFromObject($import_matchstaffstatistic,'value'));

			if ($p_matchstaffstatistic->store()===false)
			{
				$my_text .= 'error on matchstaffstatistic import: ';
				$my_text .= $oldID;
				$this->_success_text['Importing match staff statistic data:']=$my_text;
			}
			else
			{
				$dPerson=$this->_getPersonFromTeamStaff($p_matchstaffstatistic->team_staff_id);
				$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
				$my_text .= Text::sprintf(	'Created new match staff statistic data. StatisticID: %1$s - MatchID: %2$s - Player: %3$s,%4$s - Team: %5$s - Value: %6$s',
								'</span><strong>'.$p_matchstaffstatistic->statistic_id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$p_matchstaffstatistic->match_id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$dPerson->lastname,
								$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$this->_getTeamName($p_matchstaffstatistic->projectteam_id).'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$p_matchstaffstatistic->value.'</strong>');
				$my_text .= '<br />';
			}
		}
		$this->_success_text['Importing match staff statistic data:']=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importMatchStatistic()
	 * 
	 * @return
	 */
	private function _importMatchStatistic()
	{
		$my_text = '';
		if (!isset($this->_datas['matchstatistic']) || count($this->_datas['matchstatistic'])==0){return true;}

		if ((!isset($this->_newstatisticsid) || count($this->_newstatisticsid)==0) &&
			(!isset($this->_dbstatisticsid) || count($this->_dbstatisticsid)==0)){return true;}

		if (!isset($this->_datas['statistic']) || count($this->_datas['statistic'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= Text::sprintf('Warning: Skipped %1$s records for match statistic data because there is no statistic data included!',count($this->_datas['matchstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match statistic data:']=$my_text;
			return true;
		}
		if (!isset($this->_datas['match']) || count($this->_datas['match'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= Text::sprintf('Warning: Skipped %1$s records for match statistic data because there is no match data included!',count($this->_datas['matchstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match statistic data:']=$my_text;
			return true;
		}
		if (!isset($this->_datas['projectteam']) || count($this->_datas['projectteam'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= Text::sprintf('Warning: Skipped %1$s records for match statistic data because there is no projectteam data included!',count($this->_datas['matchstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match statistic data:']=$my_text;
			return true;
		}
		if (!isset($this->_datas['teamplayer']) || count($this->_datas['teamplayer'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= Text::sprintf('Warning: Skipped %1$s records for match statistic data because there is no teamplayer data included!',count($this->_datas['matchstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match statistic data:']=$my_text;
			return true;
		}
		foreach ($this->_datas['matchstatistic'] as $key => $matchstatistic)
		{
			$import_matchstatistic=$this->_datas['matchstatistic'][$key];
			$oldID=$this->_getDataFromObject($import_matchstatistic,'id');

			
            $mdl = BaseDatabaseModel::getInstance("matchstatistic", "sportsmanagementModel");
            $p_matchstatistic = $mdl->getTable();

			$p_matchstatistic->set('match_id',$this->_convertMatchID[$this->_getDataFromObject($import_matchstatistic,'match_id')]);
			$p_matchstatistic->set('projectteam_id',$this->_convertProjectTeamID[$this->_getDataFromObject($import_matchstatistic,'projectteam_id')]);
			$p_matchstatistic->set('teamplayer_id',$this->_convertTeamPlayerID[$this->_getDataFromObject($import_matchstatistic,'teamplayer_id')]);
			$p_matchstatistic->set('statistic_id',$this->_convertStatisticID[$this->_getDataFromObject($import_matchstatistic,'statistic_id')]);
			$p_matchstatistic->set('value',$this->_getDataFromObject($import_matchstatistic,'value'));

			if ($p_matchstatistic->store()===false)
			{
				$my_text .= 'error on matchstatistic import: ';
				$my_text .= $oldID;
				$this->_success_text['Importing match statistic data:']=$my_text;
			}
			else
			{
				$dPerson=$this->_getPersonFromTeamPlayer($p_matchstatistic->teamplayer_id);
				$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
				$my_text .= Text::sprintf(	'Created new match statistic data. StatisticID: %1$s - MatchID: %2$s - Player: %3$s,%4$s - Team: %5$s - Value: %6$s',
								'</span><strong>'.$p_matchstatistic->statistic_id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$p_matchstatistic->match_id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$dPerson->lastname,
								$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$this->_getTeamName($p_matchstatistic->projectteam_id).'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$p_matchstatistic->value.'</strong>');
				$my_text .= '<br />';
			}
		}
		$this->_success_text['Importing match statistic data:']=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importTreetos()
	 * 
	 * @return
	 */
	private function _importTreetos()
	{
		$my_text='';
		if (!isset($this->_datas['treeto']) || count($this->_datas['treeto'])==0){return true;}
		if (isset($this->_datas['treeto']))
		{
			foreach ($this->_datas['treeto'] as $key => $treeto)
			{
				
                $mdl = BaseDatabaseModel::getInstance("treeto", "sportsmanagementModel");
                $p_treeto = $mdl->getTable();
            
				$oldId=(int)$treeto->id;
				$p_treeto->set('project_id',$this->_project_id);
				if ($treeto->id ==$this->_datas['treeto'][$key]->id)
				{
					if (trim($p_treeto->name)!='')
					{
						$p_treeto->set('name',$this->_getDataFromObject($treeto,'name'));
					}
					else
					{
						$p_treeto->set('name',$this->_getDataFromObject($treeto,'id'));
					}
					if (count($this->_convertDivisionID) > 0)
					{
						$p_treeto->set('division_id',$this->_convertDivisionID[$this->_getDataFromObject($treeto,'division_id')]);
					}
					$p_treeto->set('tree_i',$this->_getDataFromObject($treeto,'tree_i'));
					$p_treeto->set('global_bestof',$this->_getDataFromObject($treeto,'global_bestof'));
					$p_treeto->set('global_matchday',$this->_getDataFromObject($treeto,'global_matchday'));
					$p_treeto->set('global_known',$this->_getDataFromObject($treeto,'global_known'));
					$p_treeto->set('global_fake',$this->_getDataFromObject($treeto,'global_fake'));
					$p_treeto->set('leafed',$this->_getDataFromObject($treeto,'leafed'));
					$p_treeto->set('mirror',$this->_getDataFromObject($treeto,'mirror'));
					$p_treeto->set('hide',$this->_getDataFromObject($treeto,'hide'));
					$p_treeto->set('trophypic',$this->_getDataFromObject($treeto,'trophypic'));
				}
				if ($p_treeto->store()===false)
				{
					$my_text .= 'error on treeto import: ';
					$my_text .= '#'.$oldID.'#';
					$this->_success_text['Importing treeto data:']=$my_text;
				}
				else
				{
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= Text::sprintf('Created new treeto data: %1$s','</span><strong>'.$p_treeto->name.'</strong>');
					$my_text .= '<br />';
				}
				$insertID=Factory::getDbo()->insertid();
				$this->_convertTreetoID[$oldId]=$insertID;
			}
			$this->_success_text['Importing treeto data:']=$my_text;
			return true;
		}
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importTreetonode()
	 * 
	 * @return
	 */
	private function _importTreetonode()
	{
		$my_text='';
		if (!isset($this->_datas['treetonode']) || count($this->_datas['treetonode'])==0){return true;}
		if (isset($this->_datas['treetonode']))
		{
			foreach ($this->_datas['treetonode'] as $key => $treetonode)
			{
				
                $mdl = BaseDatabaseModel::getInstance("treetonode", "sportsmanagementModel");
                $p_treetonode = $mdl->getTable();
            
				$oldId=(int)$treetonode->id;
				if ($treetonode->id ==$this->_datas['treetonode'][$key]->id)
				{
					$p_treetonode->set('treeto_id',$this->_convertTreetoID[$this->_getDataFromObject($treetonode,'treeto_id')]);
					$p_treetonode->set('node',$this->_getDataFromObject($treetonode,'node'));
					$p_treetonode->set('row',$this->_getDataFromObject($treetonode,'row'));
					$p_treetonode->set('bestof',$this->_getDataFromObject($treetonode,'bestof'));
					$p_treetonode->set('title',$this->_getDataFromObject($treetonode,'title'));
					$p_treetonode->set('content',$this->_getDataFromObject($treetonode,'content'));
					$p_treetonode->set('team_id',$this->_convertProjectTeamID[$this->_getDataFromObject($treetonode,'team_id')]);
					$p_treetonode->set('published',$this->_getDataFromObject($treetonode,'published'));
					$p_treetonode->set('is_leaf',$this->_getDataFromObject($treetonode,'is_leaf'));
					$p_treetonode->set('is_lock',$this->_getDataFromObject($treetonode,'is_lock'));
					$p_treetonode->set('is_ready',$this->_getDataFromObject($treetonode,'is_ready'));
					$p_treetonode->set('got_lc',$this->_getDataFromObject($treetonode,'got_lc'));
					$p_treetonode->set('got_rc',$this->_getDataFromObject($treetonode,'got_rc'));
				}
				if ($p_treetonode->store()===false)
				{
					$my_text .= 'error on treetonode import: ';
					$my_text .= '#'.$oldID.'#';
					$this->_success_text['Importing treetonode data:']=$my_text;
				}
				else
				{
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= Text::sprintf('Created new treetonode data: %1$s','</span><strong>'.$p_treetonode->id.'</strong>');
					$my_text .= '<br />';
				}
				$insertID=Factory::getDbo()->insertid();
				$this->_convertTreetonodeID[$oldId]=$insertID;
			}
			$this->_success_text['Importing treetonode data:']=$my_text;
			return true;
		}
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importTreetomatch()
	 * 
	 * @return
	 */
	private function _importTreetomatch()
	{
		$my_text='';
		if (!isset($this->_datas['treetomatch']) || count($this->_datas['treetomatch'])==0){return true;}
		if (isset($this->_datas['treetomatch']))
		{
			foreach ($this->_datas['treetomatch'] as $key => $treetomatch)
			{
				
                $mdl = BaseDatabaseModel::getInstance("treetomatch", "sportsmanagementModel");
                $p_treetomatch = $mdl->getTable();
                
				$oldId=(int)$treetomatch->id;
				if ($treetomatch->id ==$this->_datas['treetomatch'][$key]->id)
				{
					$p_treetomatch->set('node_id',$this->_convertTreetonodeID[$this->_getDataFromObject($treetomatch,'node_id')]);
					$p_treetomatch->set('match_id',$this->_convertMatchID[$this->_getDataFromObject($treetomatch,'match_id')]);
				}
				if ($p_treetomatch->store()===false)
				{
					$my_text .= 'error on treetomatch import: ';
					$my_text .= '#'.$oldID.'#';
					$this->_success_text['Importing treetomatch data:']=$my_text;
				}
				else
				{
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= Text::sprintf('Created new treetomatch data: %1$s','</span><strong>'.$p_treetomatch->id.'</strong>');
					$my_text .= '<br />';
				}
				$insertID=Factory::getDbo()->insertid();
				$this->_convertTreetomatchID[$oldId]=$insertID;
			}
			$this->_success_text['Importing treetomatch data:']=$my_text;
			return true;
		}
	}

	/**
	 * sportsmanagementModelJLXMLImport::_beforeFinish()
	 * 
	 * @return void
	 */
	private function _beforeFinish()
	{
		// convert favorite teams
		$checked_fav_teams=trim($this->_getDataFromObject($this->_datas['project'],'fav_team'));
		$t_fav_team='';
		if ($checked_fav_teams!='')
		{
			$t_fav_teams=explode(",",$checked_fav_teams);
			foreach ($t_fav_teams as $value)
			{
				if (isset($this->_convertTeamID[$value])){$t_fav_team .= $this->_convertTeamID[$value].',';}
			}
			$t_fav_team=trim($t_fav_team,',');
		}
		$query="UPDATE #__".COM_SPORTSMANAGEMENT_TABLE."_project SET fav_team='$t_fav_team' WHERE id=$this->_project_id";
		Factory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
	}

	/**
	 * sportsmanagementModelJLXMLImport::importData()
	 * 
	 * @param mixed $post
	 * @return
	 */
	public function importData($post)
	{
	   $app = Factory::getApplication();
       $this->_season_id = $post['filter_season'];
       $this->_agegroup_id = $post['agegroup_id'];
	$this->master_template = $post['copyTemplate'];
		$this->_template_id = $post['copyTemplate'];
		$this->timezone = $post['timezone'];
       
		$option = Factory::getApplication()->input->getCmd('option');
        $this->show_debug_info = ComponentHelper::getParams($option)->get('show_debug_info',0) ;
        $this->_datas = $this->getData();

		$this->_newteams=array();
		$this->_newteamsshort=array();
		$this->_dbteamsid=array();
		$this->_newteamsmiddle=array();
		$this->_newteamsinfo=array();

		$this->_newclubs=array();
		$this->_newclubsid=array();
		$this->_newclubscountry=array();
		$this->_dbclubsid=array();
		$this->_createclubsid=array();

		$this->_newplaygroundid=array();
		$this->_newplaygroundname=array();
		$this->_newplaygroundshort=array();
		$this->_dbplaygroundsid=array();

		$this->_newpersonsid=array();
		$this->_newperson_lastname=array();
		$this->_newperson_firstname=array();
		$this->_newperson_nickname=array();
		$this->_newperson_birthday=array();
		$this->_dbpersonsid=array();

		$this->_neweventsname=array();
		$this->_neweventsid=array();
		$this->_dbeventsid=array();

		$this->_newpositionsname=array();
		$this->_newpositionsid=array();
		$this->_dbpositionsid=array();

		$this->_newparentpositionsname=array();
		$this->_newparentpositionsid=array();
		$this->_dbparentpositionsid=array();

		$this->_newstatisticsname=array();
		$this->_newstatisticsid=array();
		$this->_dbstatisticsid=array();

		if (is_array($post) && count($post) > 0)
		{
			foreach($post as $key => $element)
			{
				if ( substr($key,0,8)=='teamName')
				{
					$tempteams=explode("_",$key);
					$this->_newteams[$tempteams[1]]=$element;
				}
				elseif ( substr($key,0,13)=='teamShortname')
				{
					$tempteams=explode("_",$key);
					$this->_newteamsshort[$tempteams[1]]=$element;
				}
				elseif ( substr($key,0,8)=='teamInfo')
				{
					$tempteams=explode("_",$key);
					$this->_newteamsinfo[$tempteams[1]]=$element;
				}
				elseif ( substr($key,0,14)=='teamMiddleName')
				{
					$tempteams=explode("_",$key);
					$this->_newteamsmiddle[$tempteams[1]]=$element;
				}
				elseif ( substr($key,0,6)=='teamID')
				{
					$tempteams=explode("_",$key);
					$this->_newteamsid[$tempteams[1]]=$element;
				}
				elseif ( substr($key,0,8)=='dbTeamID')
				{
					$tempteams=explode("_",$key);
					$this->_dbteamsid[$tempteams[1]]=$element;
				}
				elseif ( substr($key,0,8)=='clubName')
				{
					$tempclubs=explode("_",$key);
					$this->_newclubs[$tempclubs[1]]=$element;
				}
				elseif ( substr($key,0,11)=='clubCountry')
				{
					$tempclubs=explode("_",$key);
					$this->_newclubscountry[$tempclubs[1]]=$element;
				}
				/**/
				elseif ( substr($key,0,6)=='clubID')
				{
					$tempclubs=explode("_",$key);
					$this->_newclubsid[$tempclubs[1]]=$element;
				}
				/**/
				elseif ( substr($key,0,10)=='createClub')
				{
					$tempclubs=explode("_",$key);
					$this->_createclubsid[$tempclubs[1]]=$element;
				}
				elseif ( substr($key,0,8)=='dbClubID')
				{
					$tempclubs=explode("_",$key);
					$this->_dbclubsid[$tempclubs[1]]=$element;
				}
				elseif ( substr($key,0,9)=='eventName')
				{
					$tempevent=explode("_",$key);
					$this->_neweventsname[$tempevent[1]]=$element;
				}
				elseif ( substr($key,0,7)=='eventID')
				{
					$tempevent=explode("_",$key);
					$this->_neweventsid[$tempevent[1]]=$element;
				}
				elseif ( substr($key,0,9)=='dbEventID')
				{
					$tempevent=explode("_",$key);
					$this->_dbeventsid[$tempevent[1]]=$element;
				}
				elseif ( substr($key,0,12)=='positionName')
				{
					$tempposition=explode("_",$key);
					$this->_newpositionsname[$tempposition[1]]=$element;
				}
				elseif ( substr($key,0,10)=='positionID')
				{
					$tempposition=explode("_",$key);
					$this->_newpositionsid[$tempposition[1]]=$element;
				}
				elseif ( substr($key,0,12)=='dbPositionID')
				{
					$tempposition=explode("_",$key);
					$this->_dbpositionsid[$tempposition[1]]=$element;
				}
				elseif ( substr($key,0,18)=='parentPositionName')
				{
					$tempposition=explode("_",$key);
					$this->_newparentpositionsname[$tempposition[1]]=$element;
				}
				elseif ( substr($key,0,16) =="parentPositionID")
				{
					$tempposition=explode("_",$key);
					$this->_newparentpositionsid[$tempposition[1]]=$element;
				}
				elseif ( substr($key,0,18)=='dbParentPositionID')
				{
					$tempposition=explode("_",$key);
					$this->_dbparentpositionsid[$tempposition[1]]=$element;
				}
				elseif ( substr($key,0,14)=='playgroundName')
				{
					$tempplayground=explode("_",$key);
					$this->_newplaygroundname[$tempplayground[1]]=$element;
				}
                
                elseif ( substr($key,0,17)=='playgroundCountry')
				{
					$tempplayground=explode("_",$key);
					$this->_newplaygroundcountry[$tempplayground[1]]=$element;
				}
                
				elseif ( substr($key,0,19)=='playgroundShortname')
				{
					$tempplayground=explode("_",$key);
					$this->_newplaygroundshort[$tempplayground[1]]=$element;
				}
				elseif ( substr($key,0,12)=='playgroundID')
				{
					$tempplayground=explode("_",$key);
					$this->_newplaygroundid[$tempplayground[1]]=$element;
				}
				elseif ( substr($key,0,14)=='dbPlaygroundID')
				{
					$tempplayground=explode("_",$key);
					$this->_dbplaygroundsid[$tempplayground[1]]=$element;
				}
				elseif ( substr($key,0,13)=='statisticName')
				{
					$tempstatistic=explode("_",$key);
					$this->_newstatisticsname[$tempstatistic[1]]=$element;
				}
				elseif ( substr($key,0,11)=='statisticID')
				{
					$tempstatistic=explode("_",$key);
					$this->_newstatisticsid[$tempstatistic[1]]=$element;
				}
				elseif ( substr($key,0,13)=='dbStatisticID')
				{
					$tempstatistic=explode("_",$key);
					$this->_dbstatisticsid[$tempstatistic[1]]=$element;
				}
				elseif ( substr($key,0,14)=='personLastname')
				{
					$temppersons=explode("_",$key);
					$this->_newperson_lastname[$temppersons[1]]=$element;
				}
				elseif ( substr($key,0,15)=='personFirstname')
				{
					$temppersons=explode("_",$key);
					$this->_newperson_firstname[$temppersons[1]]=$element;
				}
				elseif (substr($key,0,14)=='personNickname')
				{
					$temppersons=explode("_",$key);
					$this->_newperson_nickname[$temppersons[1]]=$element;
				}
				elseif (substr($key,0,14)=='personBirthday')
				{
					$temppersons=explode("_",$key);
					$this->_newperson_birthday[$temppersons[1]]=$element;
				}
				elseif (substr($key,0,8)=='personID')
				{
					$temppersons=explode("_",$key);
					$this->_newpersonsid[$temppersons[1]]=$element;
				}
				elseif (substr($key,0,10)=='dbPersonID')
				{
					$temppersons=explode("_",$key);
					$this->_dbpersonsid[$temppersons[1]]=$element;
				}
				elseif ( substr($key,0,14) == 'personAgeGroup' )
				{
					$temppersons = explode("_",$key);
					$this->_dbpersonsagegroup[$temppersons[1]] = $element;
				}
			}

			$this->_success_text='';

			//set $this->_importType
			$this->_importType=$post['importType'];

			if (isset($post['admin']))
			{
				$this->_joomleague_admin=(int)$post['admin'];
			}
			else
			{
				$this->_joomleague_admin=62;
			}

			//check project name
			if ($post['importProject'])
			{
				if (isset($post['name'])) // Project Name
				{
					$this->_name=substr($post['name'],0,100);
				}
				else
				{
					echo "<script> alert('".Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Missing projectname')."'); window.history.go(-1); </script>\n";
				}

				if (empty($this->_datas['project']))
				{
					echo "<script> alert('".Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Project object is missing inside import file!!!')."'); window.history.go(-1); </script>\n";
					return false;
				}

				if ($this->_checkProject()===false)
				{
					echo "<script> alert('".Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Projectname already exists')."'); window.history.go(-1); </script>\n";
					return false;
				}
			}

			//check sportstype
			if ($post['importProject'] || $post['importType']=='events' || $post['importType']=='positions')
			{
				if ((isset($post['sportstype'])) && ($post['sportstype'] > 0))
				{
					$this->_sportstype_id=(int)$post['sportstype'];
				}
				elseif (isset($post['sportstypeNew']))
					{
						$this->_sportstype_new=substr($post['sportstypeNew'],0,25);
					}
					else
					{
						echo "<script> alert('".Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Missing sportstype')."'); window.history.go(-1); </script>\n";
						return false;
					}
			}

			//check league/season/admin/editor/publish/template
			if ($post['importProject'])
			{
				if (isset($post['league']))
				{
					$this->_league_id=(int)$post['league'];
				}
				elseif (isset($post['leagueNew']))
					{
						$this->_league_new=substr($post['leagueNew'],0,75);
					}
					else
					{
						echo "<script> alert('".Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Missing league')."'); window.history.go(-1); </script>\n";
						return false;
					}

				if ( isset($post['season']))
				{
					$this->_season_id=(int)$post['season'];
				}
				elseif ( isset($post['seasonNew']))
					{
						$this->_season_new=substr($post['seasonNew'],0,75);
					}
					else
					{
						echo "<script> alert('".Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Missing season')."'); window.history.go(-1); </script>\n";
						return false;
					}

				if ( isset($post['editor']))
				{
					$this->_joomleague_editor=(int)$post['editor'];
				}
				else
				{
					$this->_joomleague_editor=62;
				}

				if ( isset($post['publish']))
				{
					$this->_publish=(int)$post['publish'];
				}
				else
				{
					$this->_publish=0;
				}

				if ( isset($post['copyTemplate'])) // if new template set this value is 0
				{
					$this->_template_id=(int)$post['copyTemplate'];
				}
				else
				{
					$this->_template_id=0;
				}
			}

			/**
			 *
			 * ab hier startet der import
			 *
			 */
             $step = ComponentHelper::getParams('com_sportsmanagement')->get('backend_xmlimport_step',1);
             
			if ( $post['importProject'] || $post['importType']=='events' || $post['importType'] == 'positions' )
			{
				// import sportstype
                if( version_compare($step,'1','ge') ) 
        {
				if ( $this->_importSportsType() === false)
				{
					return $this->_success_text;
				}
                }
			}

			if ( $post['importProject'] )
			{
				// import league
                if( version_compare($step,'2','ge')) 
        {
				if ( $this->_importLeague()===false)
				{
					return $this->_success_text;
				}
}
				// import season
                if( version_compare($step,'3','ge')) 
        {
				if ( $this->_importSeason()===false)
				{
					return $this->_success_text;
				}
                }
			}

			// import events / should also work with exported events-XML without problems
            if( version_compare($step,'4','ge') ) 
        {
			if ( $this->_importEvents() === false )
			{
				return $this->_success_text;
			}
}
			// import Statistic
            if(version_compare($step,'5','ge')) 
        {
			if ($this->_importStatistics()===false)
			{
				return $this->_success_text;
			}
}
			// import parent positions
            if(version_compare($step,'6','ge')) 
        {
			if ($this->_importParentPositions()===false)
			{
				return $this->_success_text;
			}
}
			// import positions
            if(version_compare($step,'7','ge')) 
        {
			if ($this->_importPositions()===false)
			{
				return $this->_success_text;
			}
}
			// import PositionEventType
            if(version_compare($step,'8','ge')) 
        {
			if ($this->_importPositionEventType()===false)
			{
				return $this->_success_text;
			}
}
			// import playgrounds
            if(version_compare($step,'9','ge')) 
        {
			if ($this->_importPlayground()===false)
			{
				return $this->_success_text;
			}
}
			// import clubs
            if(version_compare($step,'10','ge')) 
        {
			if ($this->_importClubs()===false)
			{
				return $this->_success_text;
			}
}
			if ($this->_importType!='playgrounds')	// don't convert club_id if only playgrounds are imported
			{
				// convert playground Club-IDs
                if(version_compare($step,'11','ge')) 
        {
				if ($this->_convertNewPlaygroundIDs()===false)
				{
					return $this->_success_text;
				}
                }
			}

			// import teams
            if(version_compare($step,'12','ge')) 
        {
			if ($this->_importTeams()===false)
			{
				return $this->_success_text;
			}
}
			// import persons
            if(version_compare($step,'13','ge')) 
        {
			if ($this->_importPersons()===false)
			{
				return $this->_success_text;
			}
}

			if ($post['importProject'])
			{
				// import project
                if(version_compare($step,'14','ge')) 
        {
				if ($this->_importProject()===false)
				{
					return $this->_success_text;
				}
}
                
			}

			// import divisions
            if(version_compare($step,'16','ge')) 
        {
			if ($this->_importDivisions()===false)
			{
				return $this->_success_text;
			}
}
			// import project positions
            if(version_compare($step,'17','ge')) 
        {
			if ($this->_importProjectPositions()===false)
			{
				return $this->_success_text;
			}
}
			// import project referees
            if(version_compare($step,'18','ge')) 
        {
			if ($this->_importProjectReferees()===false)
			{
				return $this->_success_text;
			}
}

			// import projectteam
            if(version_compare($step,'19','ge')) 
        {
			if ($this->_importProjectTeam()===false)
			{
				return $this->_success_text;
			}
}
			// import teamplayers
            if(version_compare($step,'21','ge')) 
        {
			if ($this->_importTeamPlayer()===false)
			{
				return $this->_success_text;
			}
}
			// import teamstaffs
            if(version_compare($step,'22','ge')) 
        {
			if ($this->_importTeamStaff()===false)
			{
				return $this->_success_text;
			}
}
			// import teamtrainingdata
            if(version_compare($step,'23','ge')) 
        {
			if ($this->_importTeamTraining()===false)
			{
				return $this->_success_text;
			}
}
			// import rounds
            if( version_compare($step,'24','ge') ) 
        {
			if ( $this->_importRounds() === false )
			{
				return $this->_success_text;
			}
}


			// import matches
			// last to import cause needs a lot of imports and conversions inside the database before match-conversion may be done
			// after this import only the matchplayers,-staffs,-referees and -events can be imported cause they need existing
			//
			// imported matches
            if(version_compare($step,'25','ge')) 
        {
			if ($this->_importMatches()===false)
			{
				return $this->_success_text;
			}
}
			// import MatchPlayer
            if(version_compare($step,'26','ge')) 
        {
			if ($this->_importMatchPlayer()===false)
			{
				return $this->_success_text;
			}
}
			// import MatchStaff
            if(version_compare($step,'27','ge')) 
        {
			if ($this->_importMatchStaff()===false)
			{
				return $this->_success_text;
			}
}
			// import MatchReferee
            if(version_compare($step,'28','ge')) 
        {
			if ($this->_importMatchReferee()===false)
			{
				return $this->_success_text;
			}
}
			// import MatchEvent
            if(version_compare($step,'29','ge')) 
        {
			if ($this->_importMatchEvent()===false)
			{
				return $this->_success_text;
			}
}
			// import PositionStatistic
            if(version_compare($step,'30','ge')) 
        {
			if ($this->_importPositionStatistic()===false)
			{
				return $this->_success_text;
			}
}
			// import MatchStaffStatistic
            if(version_compare($step,'31','ge')) 
        {
			if ($this->_importMatchStaffStatistic()===false)
			{
				return $this->_success_text;
			}
}
			// import MatchStatistic
            if(version_compare($step,'32','ge')) 
        {
			if ($this->_importMatchStatistic()===false)
			{
				return $this->_success_text;
			}
            }
			// import Treeto
            if(version_compare($step,'33','ge')) 
        {
			if ($this->_importTreetos()===false)
			{
				return $this->_success_text;
			}
}
			// import Treetonode
            if(version_compare($step,'34','ge')) 
        {
			if ($this->_importTreetonode()===false)
			{
				return $this->_success_text;
			}
}
			// import Treetomatch
            if(version_compare($step,'35','ge')) 
        {
			if ($this->_importTreetomatch()===false)
			{
				return $this->_success_text;
			}
}
			if ($post['importProject'])
			{
				$this->_beforeFinish();
			}

			$this->_deleteImportFile();

			// daten für die neue zuordnung setzen
            $this->setNewDataStructur();
/**
 * zum schluss werden noch die bilderpfade umgesetzt
 */
$mdl = BaseDatabaseModel::getInstance("databasetool", "sportsmanagementModel"); 
$mdl->setNewPicturePath();            

            return $this->_success_text;
		}
		else
		{
			$this->_deleteImportFile();
			return false;
		}
	}

	/**
	 * sportsmanagementModelJLXMLImport::dump_header()
	 * 
	 * @param mixed $text
	 * @return void
	 */
	private function dump_header($text)
	{
		echo "<h1>$text</h1>";
	}

	/**
	 * sportsmanagementModelJLXMLImport::dump_variable()
	 * 
	 * @param mixed $description
	 * @param mixed $variable
	 * @return void
	 */
	private function dump_variable($description, $variable)
	{
		
	}
    
    /**
     * sportsmanagementModelJLXMLImport::setNewDataStructur()
     * 
     * @return void
     */
    function setNewDataStructur()
    {
        $app = Factory::getApplication();
        // Get a db connection.
        $db = Factory::getDbo();
        // $this->_project_id
        // $this->_season_id 
        $my_text = '';
if( !isset($this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]) ) {
    $this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
}	    
        $update_match_ids = array();
        
        // zum update der neue spieler id benötigen wir die match id´s aus dem projekt.
        $query = $db->getQuery(true);
        $query->clear();
		$query->select('m.id');		
		$query->from('#__sportsmanagement_match as m');
		$query->join('INNER','#__sportsmanagement_round r ON m.round_id = r.id ');
        $query->join('INNER','#__sportsmanagement_project AS p ON p.id = r.project_id');
        $query->where('p.id = '.$this->_project_id);
        $db->setQuery($query);
        $match_ids = $db->loadObjectList();
        foreach($match_ids as $match_id)
		{
			$update_match_ids[] = $match_id->id;
		}
            
        // die schiedsrichter verarbeiten
        $query = 'SELECT * FROM #__sportsmanagement_project_referee where project_id = '.$this->_project_id ;
		Factory::getDbo()->setQuery($query);
		$result_pr = Factory::getDbo()->loadObjectList();
        
        foreach ( $result_pr as $protref )
        {
            // ist der schiedsrichter schon durch ein anderes projekt angelegt ?
            $query = $db->getQuery(true);
            $query->clear();
		    $query->select('id');		
		    $query->from('#__sportsmanagement_season_person_id AS t');
		    $query->where('t.person_id = '.$protref->person_id);
            $query->where('t.season_id = '.$this->_season_id);
            $query->where('t.persontype IN (0,3) ');
		    $db->setQuery($query);
		    $new_person_id = $db->loadResult();
         
         if ( $new_person_id )
         {
         $new_match_referee_id = $new_person_id;  
         }
         else
         {   
            // Create a new query object.
                $insertquery = $db->getQuery(true);
                // Insert columns.
                $columns = array('person_id','season_id','persontype','picture');
                // Insert values.
                $values = array($protref->person_id,$this->_season_id,3,'\''.$protref->picture.'\'');
                // Prepare the insert query.
                $insertquery
                ->insert($db->quoteName('#__sportsmanagement_season_person_id'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                $db->setQuery($insertquery);
            
	            if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
			    {
			    //sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, Factory::getDbo()->getErrorMsg(), __LINE__); 
			    }
			    else
			    {
			    // die match referee tabelle updaten
                $new_match_referee_id = $db->insertid();
                                 
			    }
        }
        // Fields to update.
                $query = $db->getQuery(true);
                $fields = array(
                $db->quoteName('person_id') . '=' . $new_match_referee_id
                );
                // Conditions for which records should be updated.
                $conditions = array(
                $db->quoteName('person_id') . '=' . $protref->person_id,
                $db->quoteName('project_id') . '=' . $this->_project_id
                );
                $query->update($db->quoteName('#__sportsmanagement_project_referee'))->set($fields)->where($conditions);
                $db->setQuery($query);
                $result = sportsmanagementModeldatabasetool::runJoomlaQuery();  
                if (!$result)
			    {
			    //sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, Factory::getDbo()->getErrorMsg(), __LINE__); 
			    } 
                
                
        
        }
        
        // die teams verarbeiten
        $query = 'SELECT * FROM #__sportsmanagement_project_team where project_id = '.$this->_project_id ;
		Factory::getDbo()->setQuery($query);
		$result_pt = Factory::getDbo()->loadObjectList();
        
        foreach ( $result_pt as $proteam )
        {
            // ist das team schon durch ein anderes projekt angelegt ?
            $query = $db->getQuery(true);
            $query->select('team_id');		
		    $query->from('#__sportsmanagement_season_team_id AS t');
		    $query->where('t.id = '.$proteam->team_id);
            $query->where('t.season_id = '.$this->_season_id);
		    $db->setQuery($query);
		    $new_team_id = $db->loadResult();

            // die spieler verarbeiten
            $query = $db->getQuery(true);
            $query->select('tp.*');
            $query->select('p.position_id');
            $query->from('#__sportsmanagement_team_player AS tp');
            $query->join('INNER','#__sportsmanagement_person as p ON p.id = tp.person_id ');
            $query->where('tp.projectteam_id = '.$proteam->id);
            Factory::getDbo()->setQuery($query);
		    $result_tp = Factory::getDbo()->loadObjectList();
            foreach ( $result_tp as $team_member )
            {
                if ( !$team_member->position_id || $team_member->position_id == '' )
                {
                $team_member->position_id = 0;
                }
                if ( !$team_member->project_position_id || $team_member->project_position_id == '' )
                {
                $team_member->project_position_id = 0;
                }
                
                // ist der spieler schon durch ein anderes projekt angelegt ?
                $query = $db->getQuery(true);
		        $query->select('id');		
		        $query->from('#__sportsmanagement_season_person_id AS t');
		        $query->where('t.person_id = '.$team_member->person_id);
                $query->where('t.season_id = '.$this->_season_id);
                $query->where('t.persontype IN (0,1)');
		        $db->setQuery($query);
		        $new_player_id = $db->loadResult();
                
                if ( $new_player_id )
                {
                    // vorhanden
                    $my_text .= '<span style="color:'.$this->existingInDbColor.'">';
						$my_text .= Text::sprintf(	'spieler vorhanden season_person_id: alte id: %1$s - saison: %2$s - neue id: %3$s',
													"</span><strong>$team_member->person_id</strong>",
													"<strong>$this->_season_id</strong>",
                                                    "<strong>$new_player_id</strong>"
													);
						$my_text .= '<br />';
                }
                else
                {
                // Create a new query object.
                $insertquery = $db->getQuery(true);
                // Insert columns.
                $columns = array('person_id','season_id','persontype','picture','position_id');
                // Insert values.
                $values = array($team_member->person_id,$this->_season_id,1,'\''.$team_member->picture.'\'',$team_member->position_id);
                // Prepare the insert query.
                $insertquery
                ->insert($db->quoteName('#__sportsmanagement_season_person_id'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                $db->setQuery($insertquery);
            
	            if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
			    {
			    //sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, Factory::getDbo()->getErrorMsg(), __LINE__); 
			    }
			    else
			    {
			    }
                
                }
                
                // spieler dem team/saison schon zugeordnet ?
                $query = $db->getQuery(true);
		        $query->select('id');		
		        $query->from('#__sportsmanagement_season_team_person_id AS t');
		        $query->where('t.person_id = '.$team_member->person_id);
                $query->where('t.season_id = '.$this->_season_id);
                $query->where('t.team_id = '.$new_team_id);
                $query->where('t.persontype IN (0,1)');
		        $db->setQuery($query);
		        $new_match_player_id = $db->loadResult();
                
                if ( $new_match_player_id )
                {
                    // vorhanden
                    $my_text .= '<span style="color:'.$this->existingInDbColor.'">';
						$my_text .= Text::sprintf(	'spieler vorhanden season_team_person_id: alte id: %1$s - saison: %2$s - neue id: %3$s - team id: %4$s',
													"</span><strong>$team_member->person_id</strong>",
													"<strong>$this->_season_id</strong>",
                                                    "<strong>$new_match_player_id</strong>",
                                                    "<strong>$new_team_id</strong>"
													);
						$my_text .= '<br />';
                }
                else
                {
                // Create a new query object.
                $insertquery = $db->getQuery(true);
                // Insert columns.
                $columns = array('person_id','season_id','team_id','persontype','published','picture','project_position_id','jerseynumber','position_id');
                // Insert values.
                $values = array($team_member->person_id,$this->_season_id,$new_team_id,1,1,'\''.$team_member->picture.'\'',$team_member->project_position_id,$team_member->jerseynumber,$team_member->position_id);
                // Prepare the insert query.
                $insertquery
                ->insert($db->quoteName('#__sportsmanagement_season_team_person_id'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                $db->setQuery($insertquery);
                
	            if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
			    {
			    //sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, Factory::getDbo()->getErrorMsg(), __LINE__); 
			    }
			    else
			    {
			    // die match player tabelle updaten
                $new_match_player_id = $db->insertid();
                $my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= Text::sprintf(	'spieler nicht vorhanden und angelegt season_team_person_id: alte id: %1$s - saison: %2$s - neue id: %3$s - team id: %4$s',
													"</span><strong>$team_member->person_id</strong>",
													"<strong>$this->_season_id</strong>",
                                                    "<strong>$new_match_player_id</strong>",
                                                    "<strong>$new_team_id</strong>"
													);
						$my_text .= '<br />';
                }
                
                }
                
                
                $query->clear();
            // Select some fields
            $query->select('person_id');
		      // From the table
		      $query->from('#__sportsmanagement_person_project_position');
            $query->where('person_id = ' . $team_member->person_id );
        $query->where('project_id = ' . $this->_project_id );
        $query->where('project_position_id = ' . $team_member->project_position_id );
        $query->where('persontype IN (0,1)');
        $db->setQuery($query);
		$db->execute();
        $result_ppp = $db->loadResult();
        
                if ( $result_ppp )
                {
                    
                }
                else
                {
                // projekt position eintragen
                // Create a new query object.
                $insertquery = $db->getQuery(true);
                // Insert columns.
                $columns = array('person_id','project_id','project_position_id','persontype');
                // Insert values.
                $values = array($team_member->person_id,$this->_project_id,$team_member->project_position_id,1);
                // Prepare the insert query.
                $insertquery
                ->insert($db->quoteName('#__sportsmanagement_person_project_position'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                $db->setQuery($insertquery);
                if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
			    {
			    $my_text .= '<span style="color:'.$this->existingInDbColor.'">';
						$my_text .= Text::sprintf(	'spieler vorhanden _person_project_position: person id: %1$s - projekt: %2$s - project_position_id: %3$s',
													"</span><strong>$team_member->person_id</strong>",
													"<strong>$this->_project_id</strong>",
                                                    "<strong>$team_member->project_position_id</strong>"
													);
						$my_text .= '<br />'; 
			    }
			    else
			    {
                $my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= Text::sprintf(	'spieler nicht vorhanden und angelegt _person_project_position: person id: %1$s - projekt: %2$s - project_position_id: %3$s',
													"</span><strong>$team_member->person_id</strong>",
													"<strong>$this->_project_id</strong>",
                                                    "<strong>$team_member->project_position_id</strong>"
													);
						$my_text .= '<br />';
                }
                }
                
                // Fields to update. match ids = $update_match_ids
                $query = $db->getQuery(true);
                $fields = array(
                $db->quoteName('teamplayer_id') . '=' . $new_match_player_id
                );
                // Conditions for which records should be updated.
                $conditions = array(
                $db->quoteName('teamplayer_id') . '=' . $team_member->id,
                $db->quoteName('match_id') . 'IN (' . implode(',',$update_match_ids) .')'
                );
                $query->update($db->quoteName('#__sportsmanagement_match_player'))->set($fields)->where($conditions);
                $db->setQuery($query);
                $result = sportsmanagementModeldatabasetool::runJoomlaQuery();
                if (!$result)
			    {
			    //sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, Factory::getDbo()->getErrorMsg(), __LINE__); 
			    }
                $query->update($db->quoteName('#__sportsmanagement_match_event'))->set($fields)->where($conditions);
                $db->setQuery($query);
                $result = sportsmanagementModeldatabasetool::runJoomlaQuery();
                if (!$result)
			    {
			    //sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, Factory::getDbo()->getErrorMsg(), __LINE__); 
			    }
                // Fields to update.
                $query = $db->getQuery(true);
                $fields = array(
                $db->quoteName('in_for') . '=' . $new_match_player_id
                );
                // Conditions for which records should be updated.
                $conditions = array(
                $db->quoteName('in_for') . '=' . $team_member->id,
                $db->quoteName('match_id') . 'IN (' . implode(',',$update_match_ids) .')'
                );
                $query->update($db->quoteName('#__sportsmanagement_match_player'))->set($fields)->where($conditions);
                $db->setQuery($query);
                $result = sportsmanagementModeldatabasetool::runJoomlaQuery(); 
                if (!$result)
			    {
			    //sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, Factory::getDbo()->getErrorMsg(), __LINE__); 
			    } 
			    
            }
            
            // staffs verarbeiten
            $query = $db->getQuery(true);
            $query->select('tp.*');
            $query->select('p.position_id');
            $query->from('#__sportsmanagement_team_staff AS tp');
            $query->join('INNER','#__sportsmanagement_person as p ON p.id = tp.person_id ');
            $query->where('tp.projectteam_id = '.$proteam->id);
            Factory::getDbo()->setQuery($query);
           
		    $result_tp = Factory::getDbo()->loadObjectList();
            foreach ( $result_tp as $team_member )
            {
                if ( !$team_member->position_id || $team_member->position_id == '' )
                {
                $team_member->position_id = 0;
                }
                // ist der spieler schon durch ein anderes projekt angelegt ?
                $query = $db->getQuery(true);
		        $query->select('id');		
		        $query->from('#__sportsmanagement_season_person_id AS t');
		        $query->where('t.person_id = '.$team_member->person_id);
                $query->where('t.season_id = '.$this->_season_id);
                $query->where('t.persontype IN (0,2)');
		        $db->setQuery($query);
		        $new_staff_id = $db->loadResult();
                
                if ( $new_staff_id )
                {
                    // vorhanden
                    $my_text .= '<span style="color:'.$this->existingInDbColor.'">';
						$my_text .= Text::sprintf(	'trainer vorhanden season_person_id: alte id: %1$s - saison: %2$s - neue id: %3$s',
													"</span><strong>$team_member->person_id</strong>",
													"<strong>$this->_season_id</strong>",
                                                    "<strong>$new_staff_id</strong>"
													);
						$my_text .= '<br />';
                }
                else
                {
                // Create a new query object.
                $insertquery = $db->getQuery(true);
                // Insert columns.
                $columns = array('person_id','season_id','persontype','picture','position_id');
                // Insert values.
                $values = array($team_member->person_id,$this->_season_id,2,'\''.$team_member->picture.'\'',$team_member->position_id);
                // Prepare the insert query.
                $insertquery
                ->insert($db->quoteName('#__sportsmanagement_season_person_id'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                $db->setQuery($insertquery);
                
	            if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
			    {
 
			    }
			    else
			    {
			    }
                }
                
                // spieler dem team/saison schon zugeordnet ?
                $query = $db->getQuery(true);
		        $query->select('id');		
		        $query->from('#__sportsmanagement_season_team_person_id AS t');
		        $query->where('t.person_id = '.$team_member->person_id);
                $query->where('t.season_id = '.$this->_season_id);
                $query->where('t.team_id = '.$new_team_id);
                $query->where('t.persontype IN (0,2)');
		        $db->setQuery($query);
		        $new_match_staff_id = $db->loadResult();
                
                if ( $new_match_staff_id )
                {
                    //vorhanden
                    $my_text .= '<span style="color:'.$this->existingInDbColor.'">';
						$my_text .= Text::sprintf(	'trainer vorhanden season_team_person_id: alte id: %1$s - saison: %2$s - neue id: %3$s - team id: %4$s',
													"</span><strong>$team_member->person_id</strong>",
													"<strong>$this->_season_id</strong>",
                                                    "<strong>$new_match_staff_id</strong>",
                                                    "<strong>$new_team_id</strong>"
													);
						$my_text .= '<br />';
                }
                else
                {
                // Create a new query object.
                $insertquery = $db->getQuery(true);
                // Insert columns.
                $columns = array('person_id','season_id','team_id','persontype','published','picture','project_position_id','position_id');
                // Insert values.
                $values = array($team_member->person_id,$this->_season_id,$new_team_id,2,1,'\''.$team_member->picture.'\'',$team_member->project_position_id,$team_member->position_id);
                // Prepare the insert query.
                $insertquery
                ->insert($db->quoteName('#__sportsmanagement_season_team_person_id'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                $db->setQuery($insertquery);
                
	            if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
			    {
 
			    }
			    else
			    {
			    // die match staff tabelle updaten
                $new_match_staff_id = $db->insertid();
                $my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= Text::sprintf(	'trainer nicht vorhanden und angelegt season_team_person_id: alte id: %1$s - saison: %2$s - neue id: %3$s - team id: %4$s',
													"</span><strong>$team_member->person_id</strong>",
													"<strong>$this->_season_id</strong>",
                                                    "<strong>$new_match_staff_id</strong>",
                                                    "<strong>$new_team_id</strong>"
													);
						$my_text .= '<br />';
                }
                
                } 
                
                 $query->clear();
            // Select some fields
            $query->select('person_id');
		      // From the table
		      $query->from('#__sportsmanagement_person_project_position');
            $query->where('person_id = ' . $team_member->person_id );
        $query->where('project_id = ' . $this->_project_id );
        $query->where('project_position_id = ' . $team_member->project_position_id );
        $query->where('persontype IN (0,2)');
        $db->setQuery($query);
		$db->execute();
        $result_ppp = $db->loadResult();
        
                if ( $result_ppp )
                {
                    
                }
                else
                {
                // projekt position eintragen
                // Create a new query object.
                $insertquery = $db->getQuery(true);
                // Insert columns.
                $columns = array('person_id','project_id','project_position_id','persontype');
                // Insert values.
                $values = array($team_member->person_id,$this->_project_id,$team_member->project_position_id,2);
                // Prepare the insert query.
                $insertquery
                ->insert($db->quoteName('#__sportsmanagement_person_project_position'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                $db->setQuery($insertquery);
                if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
			    {
			    $my_text .= '<span style="color:'.$this->existingStaff.'">';
						$my_text .= Text::sprintf(	'trainer vorhanden _person_project_position: person id: %1$s - projekt: %2$s - project_position_id: %3$s',
													"</span><strong>$team_member->person_id</strong>",
													"<strong>$this->_project_id</strong>",
                                                    "<strong>$team_member->project_position_id</strong>"
													);
						$my_text .= '<br />'; 
			    }
			    else
			    {
                $my_text .= '<span style="color:'.$this->existingStaff.'">';
						$my_text .= Text::sprintf(	'trainer nicht vorhanden und angelegt _person_project_position: person id: %1$s - projekt: %2$s - project_position_id: %3$s',
													"</span><strong>$team_member->person_id</strong>",
													"<strong>$this->_project_id</strong>",
                                                    "<strong>$team_member->project_position_id</strong>"
													);
						$my_text .= '<br />';
                }
                }
                // Fields to update.
                $query = $db->getQuery(true);
                $fields = array(
                $db->quoteName('team_staff_id') . '=' . $new_match_staff_id
                );
                // Conditions for which records should be updated.
                $conditions = array(
                $db->quoteName('team_staff_id') . '=' . $team_member->id,
                $db->quoteName('match_id') . 'IN (' . implode(',',$update_match_ids) .')'
                );
                $query->update($db->quoteName('#__sportsmanagement_match_staff'))->set($fields)->where($conditions);
                $db->setQuery($query);
                $result = sportsmanagementModeldatabasetool::runJoomlaQuery();
                if (!$result)
			    {
			    //sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, Factory::getDbo()->getErrorMsg(), __LINE__); 
			    }

            }
            
            

        }
        
        // zum schluss den inhalt der alten tabellen löschen
        // wegen speicherplatz
        $databasetool = BaseDatabaseModel::getInstance("databasetool", "sportsmanagementModel");
        $successTable = $databasetool->setSportsManagementTableQuery('#__sportsmanagement_team_staff', 'truncate');
        $successTable = $databasetool->setSportsManagementTableQuery('#__sportsmanagement_team_player', 'truncate');
        
         $this->_success_text[Text::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
        
        self::setNewRoundDates();
        
    }
    
    /**
     * sportsmanagementModelJLXMLImport::setNewRoundDates()
     * 
     * @return void
     */
    function setNewRoundDates()
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        // Get a db connection.
        $db = Factory::getDbo();
        
        $query = "SELECT r.id as round_id
FROM #__".COM_SPORTSMANAGEMENT_TABLE."_round AS r
WHERE r.project_id = " . $this->_project_id .' ORDER by roundcode DESC';

Factory::getDbo()->setQuery( $query );
$rounds = Factory::getDbo()->loadObjectList();

$current_round = 0;
$current_round_old = 0;

// anfang schleife runden        
foreach( $rounds as $rounddate)
{

// current round für das projekt
$current_round_old = $rounddate->round_id;
    
$query = "SELECT min(m.match_date)
from #__".COM_SPORTSMANAGEMENT_TABLE."_match as m
where m.round_id = '$rounddate->round_id'
";

Factory::getDbo()->setQuery( $query );
$von = Factory::getDbo()->loadResult();
$teile = explode(" ",$von);
$von = $teile[0];

$query = "SELECT max(m.match_date)
from #__".COM_SPORTSMANAGEMENT_TABLE."_match as m
where m.round_id = '$rounddate->round_id'
";

Factory::getDbo()->setQuery( $query );
$bis = Factory::getDbo()->loadResult();
$teile = explode(" ",$bis);
$bis = $teile[0];

// anfang bedingung
if ( $von && $bis )
{
// Create an object for the record we are going to update.
$object = new stdClass();
 
// Must be a valid primary key value.
$object->id = $rounddate->round_id;
$object->round_date_first = $von;
$object->round_date_last = $bis;
 
// Update their details in the users table using id as the primary key.
$result = Factory::getDbo()->updateObject('#__sportsmanagement_round', $object, 'id');                

if (!$result)
		{

		}
        else
        {

        }
        
        
// gibt es noch nicht ausgetragene spiele in der runde ?
// Create a new query object.
        $query = Factory::getDbo()->getQuery(true);
        $query->select(array('id'))
        ->from('#__sportsmanagement_match')
        ->where('team1_result IS NULL ')
        ->where('round_id = '.$rounddate->round_id);    
        Factory::getDbo()->setQuery($query);
		$match_id = Factory::getDbo()->loadResult();

if ( $match_id  )
{
    $current_round = $rounddate->round_id;
}

}
// ende bedingung

}        
// ende schleife runden        

if ( empty($current_round) )
{
    $current_round = $current_round_old;
}

// Create an object for the record we are going to update.
$object = new stdClass();
 
// Must be a valid primary key value.
$object->id = $this->_project_id;
$object->current_round = $current_round;
 
// Update their details in the users table using id as the primary key.
$result = Factory::getDbo()->updateObject('#__sportsmanagement_project', $object, 'id');                

if (!$result)
		{

		}
        else
        {

        }
                
        
    }    
}
?>
