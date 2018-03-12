<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jlxmlimport.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
$option = JFactory::getApplication()->input->getCmd('option');
$maxImportTime=JComponentHelper::getParams($option)->get('max_import_time',0);
if (empty($maxImportTime))
{
	$maxImportTime=480;
}
if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

$maxImportMemory=JComponentHelper::getParams($option)->get('max_import_memory',0);
if (empty($maxImportMemory))
{
	$maxImportMemory='150M';
}
if ((int)ini_get('memory_limit') < (int)$maxImportMemory){@ini_set('memory_limit',$maxImportMemory);}

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');
//jimport('joomla.application.component.modelform');

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.$option.DS.'models'.DS.'databasetool.php');

/**
 * sportsmanagementModelJLXMLImport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementModelJLXMLImport extends JModelLegacy
{
	var $_datas=array();
	var $_league_id=0;
	var $_season_id=0;
    var $_project_id = 0;
	var $_sportstype_id=0;
	var $import_version='';
	var $storeFailedColor = 'red';
	var $storeSuccessColor = 'green';
	var $existingInDbColor = 'orange';
    var $existingStaff = 'blue';
    var $show_debug_info = false;
    var $_league_new_country = '';
    var $_import_project_id = 0;
    
//    public function __construct($config = array())
//        {   
////            $app = JFactory::getApplication();
////            
////            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($config,true).'</pre>'),'Notice');
////            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getDBConnection<br><pre>'.print_r(sportsmanagementHelper::getDBConnection(),true).'</pre>'),'Notice');
////            
////                parent::__construct($config);
////                parent::setDbo(sportsmanagementHelper::getDBConnection());
//        }

	/**
	 * sportsmanagementModelJLXMLImport::_getXml()
	 * 
	 * @return
	 */
	private function _getXml()
	{
	   $app = JFactory::getApplication();
    $option = JFactory::getApplication()->input->getCmd('option');
    
		if (JFile::exists(JPATH_SITE.DS.'tmp'.DS.'joomleague_import.jlg'))
		{
			
            if(version_compare(JVERSION,'3.0.0','ge'))
            {
            
            if (function_exists('simplexml_load_file'))
			{
				return @simplexml_load_file(JPATH_SITE.DS.'tmp'.DS.'joomleague_import.jlg','SimpleXMLElement',LIBXML_NOCDATA);
			}
            
//            $xml = JFactory::getXML(JPATH_SITE.DS.'tmp'.DS.'joomleague_import.jlg');  
//            return $xml;
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($xml,true).'</pre>'),'');
              
            }
            else
            {
            
            if (function_exists('simplexml_load_file'))
			{
				return @simplexml_load_file(JPATH_SITE.DS.'tmp'.DS.'joomleague_import.jlg','SimpleXMLElement',LIBXML_NOCDATA);
			}
			else
			{
				JError::raiseWarning(500,JText::_('<a href="http://php.net/manual/en/book.simplexml.php" target="_blank">SimpleXML</a> does not exist on your system!'));
			}
            
            }
		}
		else
		{
			JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR', 'Missing import file'));
			echo "<script> alert('".JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR', 'Missing import file')."'); window.history.go(-1); </script>\n";
		}
	}
    
    
    /**
     * sportsmanagementModelJLXMLImport::getDataUpdateImportID()
     * 
     * @return
     */
    public function getDataUpdateImportID()
    {
    $app = JFactory::getApplication();
    $option = JFactory::getApplication()->input->getCmd('option');
    $project_id = $app->getUserState( "$option.pid", '0' ); 
    
    //$app->enqueueMessage(JText::_('_displayUpdate project_id -> '.'<pre>'.print_r($project_id ,true).'</pre>' ),'');
    
    if ( $project_id )
    {
    $model = JModelLegacy::getInstance('project', 'sportsmanagementmodel');
    $update_project = $model->getProject($project_id);  
    //$update_project = JTable::getInstance('Project','Table');
    //$update_project->load($project_id);
    //$app->enqueueMessage(JText::_('_displayUpdate import_project_id -> '.'<pre>'.print_r($update_project->import_project_id ,true).'</pre>' ),'');
    return $update_project->import_project_id;  
    }
    else
    {
        return false;
    }
    
      
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
						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON pt1.id=m.projectteam1_id
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id=pt1.team_id
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON pt2.id=m.projectteam2_id
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id=pt2.team_id
						WHERE m.import_match_id = '.(int) $value->id;
			JFactory::getDbo()->setQuery($query);
			$match_data = JFactory::getDbo()->loadObject();
            $my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf(	'Update Match: %1$s / Match: %2$s - %3$s / Result: %4$s - %5$s',
									'</span><strong>'.$match_data->id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
									"</span><strong>$match_data->hometeam</strong>",
									"<strong>$match_data->awayteam</strong>",
									"<strong>$value->team1_result</strong>",
									"<strong>$value->team2_result</strong>"
                                    );
					$my_text .= '<br />';
                    
     $update_match = JTable::getInstance('Match','sportsmanagementTable');
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
	 * @return
	 */
	public function getData()
	{
		$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
        $post = JFactory::getApplication()->input->post->getArray(array());
        $this->_season_id = $post['filter_season'];
        $result = NULL;
        
        $this->_import_project_id = $app->getUserState($option.'projectidimport'); ;
        
        $this->pl_import = $app->getUserState($option.'pltree'); ;
        
        //$app->enqueueMessage(JText::_('pl_import<br><pre>'.print_r($this->pl_import ,true).'</pre>'),'');
        
        //$app->enqueueMessage(JText::_('exportplayer<br><pre>'.print_r($app->getUserState($option.'exportplayer'),true).'</pre>'),'');
        //$app->enqueueMessage(JText::_('exportteamplayer<br><pre>'.print_r($app->getUserState($option.'exportteamplayer'),true).'</pre>'),'');
        
        //$app->enqueueMessage(JText::_('exportmatchplayer<br><pre>'.print_r($app->getUserState($option.'exportmatchplayer'),true).'</pre>'),'');
        //$app->enqueueMessage(JText::_('exportmatchstaff<br><pre>'.print_r($app->getUserState($option.'exportmatchstaff'),true).'</pre>'),'');
        
        //$app->enqueueMessage(JText::_('exportevent<br><pre>'.print_r($app->getUserState($option.'exportevent'),true).'</pre>'),'');
        //$app->enqueueMessage(JText::_('exportmatchevent<br><pre>'.print_r($app->getUserState($option.'exportmatchevent'),true).'</pre>'),'');
        
        //$app->enqueueMessage(JText::_('exportteamstaff<br><pre>'.print_r($app->getUserState($option.'exportteamstaff'),true).'</pre>'),'');
        //$app->enqueueMessage(JText::_('exportreferee<br><pre>'.print_r($app->getUserState($option.'exportreferee'),true).'</pre>'),'');
        //$app->enqueueMessage(JText::_('pl_matchreferee<br><pre>'.print_r($app->getUserState($option.'pl_matchreferee'),true).'</pre>'),'');
        
        //$app->enqueueMessage(JText::_('tempexportteamplayer<br><pre>'.print_r($app->getUserState($option.'tempexportteamplayer'),true).'</pre>'),'');
        
        //$app->enqueueMessage(JText::_('getData _import_project_id<br><pre>'.print_r($this->_import_project_id ,true).'</pre>'),'');
        
        libxml_use_internal_errors(true);
		if ( !$xmlData = $this->_getXml() )
		{
			$errorFound = false;
			//echo JText::_('Load of the importfile failed:').'<br />';
            JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR', 'Load of the importfile failed:'));
			foreach(libxml_get_errors() as $error)
			{
				//echo "<br>",$error->message;
                JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR', $error->message));
				$errorFound = true;
			}
			if (!$errorFound)
            {
                //echo ' '.JText::_('Unknown error :-(');
                JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR', 'Unknown error :-('));
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
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' xmlData -><br><pre>'.print_r($xmlData,true).'</pre>'),'');
        
        // ist die xmldatei gelesen machen wir weiter
		if ((isset($xmlData->record)) && (is_object($xmlData->record)))
		{
			foreach ($xmlData->record as $value)
			{
				// collect the project data of a .jlg file of JoomLeague <1.5x
				if ($xmlData->record[$i]['object']=='JoomLeagueVersion')
				{
					$this->_datas['exportversion']=$xmlData->record[$i];
				}

				// collect the project data of a .jlg file of JoomLeague <1.5x
				if ($xmlData->record[$i]['object']=='JoomLeague')
				{
					$this->_datas['project']=$xmlData->record[$i];
					$this->import_version='OLD';
                    //$this->import_version='NEW';
					JError::raiseNotice(0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_RENDERING_093'));
				}

				// collect the project data of a .jlg file of JoomLeague 1.5x
				if ($xmlData->record[$i]['object']=='JoomLeague15')
				{
					$this->_datas['project']=$xmlData->record[$i];
					$this->import_version='NEW';
					JError::raiseNotice(500,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_RENDERING_15'));
				}

				// collect the project data of a .jlg file of JoomLeague 1.5x
				if ($xmlData->record[$i]['object']=='JoomLeague20')
				{
					$this->_datas['project']=$xmlData->record[$i];
					$this->import_version='NEW';
					JError::raiseNotice(500,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_RENDERING_20'));
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
//echo '<pre>'.print_r($this->_datas['club'],true).'</pre>';
//die();
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
//                    $app->enqueueMessage(JText::_('sportsmanagementModelJLXMLImport event<br><pre>'.print_r($this->_datas['event'],true).'</pre>'   ),'');
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
//                    JError::raiseNotice(0,$this->_datas['sportstype']);
//                    $this->_datas['sportstype']->name    = str_replace('COM_SPORTSMANAGEMENT', strtoupper($option), $this->_datas['sportstype']->name);
//                    $app->enqueueMessage(JText::_('sportsmanagementModelJLXMLImport sportstype<br><pre>'.print_r($this->_datas['sportstype'],true).'</pre>'   ),'');
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

				// collect the Treeto data
				if ($xmlData->record[$i]['object']=='Treeto')
				{
					$this->_datas['treeto'][$tto]=$xmlData->record[$i];
					$tto++;
				}

				// collect the TreetoNode data
				if ($xmlData->record[$i]['object']=='TreetoNode')
				{
					$this->_datas['treetonode'][$ttn]=$xmlData->record[$i];
					$ttn++;
				}

				// collect the TreetoMatch data
				if ($xmlData->record[$i]['object']=='TreetoMatch')
				{
					$this->_datas['treetomatch'][$ttm]=$xmlData->record[$i];
					$ttm++;
				}

				$i++;
			}

// ############################ anpassungen anfang ###########################################################            
            // bilderpfade anpassen
            if ( isset($this->_datas['person']) )
            {
            foreach ($this->_datas['person'] as $temppicture)
            {
                $temppicture->picture = str_replace('com_joomleague', $option, $temppicture->picture);
                $temppicture->picture = str_replace('media', 'images', $temppicture->picture);
                if (preg_match("/placeholders/i", $temppicture->picture) || empty($temppicture->picture) ) 
                {
                      $temppicture->picture = JComponentHelper::getParams($option)->get('ph_player','');
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
                      $temppicture->picture = JComponentHelper::getParams($option)->get('ph_player','');
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
                      $temppicture->picture = JComponentHelper::getParams($option)->get('ph_player','');
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
                      $temppicture->picture = JComponentHelper::getParams($option)->get('ph_player','');
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
                      $temppicture->picture = JComponentHelper::getParams($option)->get('ph_team','');
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
                      $temppicture->picture = JComponentHelper::getParams($option)->get('ph_team','');
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
                      $temppicture->logo_big = JComponentHelper::getParams($option)->get('ph_logo_big','');
                }
                if (preg_match("/placeholders/i", $temppicture->logo_middle) || empty($temppicture->logo_middle) ) 
                {
                      $temppicture->logo_middle = JComponentHelper::getParams($option)->get('ph_logo_medium','');
                }
                if (preg_match("/placeholders/i", $temppicture->logo_small) || empty($temppicture->logo_small) ) 
                {
                      $temppicture->logo_small = JComponentHelper::getParams($option)->get('ph_logo_small','');
                }
                
            }    
            }
            
            
            
            
                
            //$app->enqueueMessage(JText::_('sportsmanagementModelJLXMLImport league<br><pre>'.print_r($this->_datas['league'],true).'</pre>'   ),'');
            if ( isset($this->_datas['league']) )
            {
            $this->_league_new_country = (string) $this->_datas['league']->country;
            }
            //$app->enqueueMessage(JText::_('sportsmanagementModelJLXMLImport league<br><pre>'.print_r($this->_league_new_country,true).'</pre>'   ),'');
            
            // textelemente bereinigen
            if ( isset($this->_datas['sportstype']) )
            {
            $this->_datas['sportstype']->name = str_replace('COM_JOOMLEAGUE', strtoupper($option), $this->_datas['sportstype']->name);
            
            //$app->enqueueMessage(JText::_('sportsmanagementModelJLXMLImport sportstype<br><pre>'.print_r($this->_datas['sportstype'],true).'</pre>'   ),'');
            
            // ereignisse um die textelemente bereinigen
            $temp = explode("_",$this->_datas['sportstype']->name);
            $sport_type_name = array_pop($temp);
            }
            else
            {
            $sport_type_name = '';    
            }
            
            if ( isset($this->_datas['event']) )
            {
            //$app->enqueueMessage(JText::_('sportsmanagementModelJLXMLImport sport_type_name<br><pre>'.print_r($sport_type_name,true).'</pre>'   ),'');
            foreach ($this->_datas['event'] as $event)
            {
                $event->name = str_replace('COM_JOOMLEAGUE', strtoupper($option).'_'.$sport_type_name, $event->name);
            }
            }
            //$app->enqueueMessage(JText::_('sportsmanagementModelJLXMLImport event<br><pre>'.print_r($this->_datas['event'],true).'</pre>'   ),'');
            
            // klartexte in textvariable umwandeln.
            $query = "SELECT name,alias FROM #__".COM_SPORTSMANAGEMENT_TABLE."_position WHERE name LIKE '%".$sport_type_name."%'";
            JFactory::getDbo()->setQuery($query);
		    sportsmanagementModeldatabasetool::runJoomlaQuery();
		    if (JFactory::getDbo()->getAffectedRows())
		    {
			$result = JFactory::getDbo()->loadObjectList();
            //$app->enqueueMessage(JText::_('sportsmanagementModelJLXMLImport _position<br><pre>'.print_r($result,true).'</pre>'   ),'');
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
                        if ( $position->name == JText::_($pos->name) )
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
                        if ( $position->name == JText::_($pos->name) )
                        {
                            $position->name = $pos->name;
                            $position->alias = $pos->alias;
                        }
                    }
                }
            }
            }
            
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' person<br><pre>'.print_r($this->_datas['person'],true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' position<br><pre>'.print_r($this->_datas['position'],true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' parentposition<br><pre>'.print_r($this->_datas['parentposition'],true).'</pre>'),'');
            
            if ( isset($this->_datas['person']) )
            {
            // jetzt werden die positionen in den personen überprüft.
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
                    //$app->enqueueMessage(JText::sprintf('Spieler %1$s %2$s hat fehlende Importposition-ID ( %3$s )',$person->firstname,$person->lastname,$person->position_id ),'Error');
                } 
                        
            }
            }
            
            
            // länder bei den spielorten vervollständigen
            // bilderpfad ändern
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
                      $playground->picture = JComponentHelper::getParams($option)->get('ph_team','');
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
			JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR', 'Something is wrong inside the import file'));
			return false;
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
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
		$query='SELECT id,username FROM #__users';
		if ($is_admin==true)
		{
			$query .= " WHERE usertype='Super Administrator' OR usertype='Administrator'";
		}
		$query .= ' ORDER BY username ASC';
		JFactory::getDbo()->setQuery($query);
		return JFactory::getDbo()->loadObjectList();
	}

	/**
	 * sportsmanagementModelJLXMLImport::getTemplateList()
	 * 
	 * @return
	 */
	public function getTemplateList()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
		$query='SELECT id,name FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project WHERE master_template=0 ORDER BY name ASC';
		JFactory::getDbo()->setQuery($query);
		return JFactory::getDbo()->loadObjectList();
	}


	/**
	 * sportsmanagementModelJLXMLImport::getNewClubList()
	 * 
	 * @return
	 */
	public function getNewClubList()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
		$query='SELECT id,name,country FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_club ORDER BY name ASC';
		JFactory::getDbo()->setQuery($query);
		return JFactory::getDbo()->loadObjectList();
	}

	/**
	 * sportsmanagementModelJLXMLImport::getNewClubListSelect()
	 * 
	 * @return
	 */
	public function getNewClubListSelect()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
		$query='SELECT id AS value, name AS text, country FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_club ORDER BY name';
		JFactory::getDbo()->setQuery($query);
		if ($results=JFactory::getDbo()->loadObjectList())
		{
			return $results;
		}
		return false;
	}

	public function getClubAndTeamList()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
		$query  = ' SELECT id, c.name AS club_name, t.name AS team_name, c.country'
				. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_club'
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.club_id=c.id'
				. ' ORDER BY c.name, t.name ASC';
		JFactory::getDbo()->setQuery($query);
		return JFactory::getDbo()->loadObjectList();
	}

	public function getClubAndTeamListSelect()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
		$query  = ' SELECT t.id AS value, CONCAT(c.name, " - ", t.name , " (", t.info , ")" ) AS text, t.club_id, c.name AS club_name, t.name AS team_name, c.country'
				. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c'
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.club_id=c.id'
				. ' ORDER BY c.name, t.name';
		JFactory::getDbo()->setQuery($query);
		if ($results=JFactory::getDbo()->loadObjectList())
		{
			return $results;
		}
		return false;
	}



	// Should be called as the last function in importData() to delete
	/**
	 * sportsmanagementModelJLXMLImport::_deleteImportFile()
	 * 
	 * @return
	 */
	private function _deleteImportFile()
	{
		$importFileName = JPATH_SITE.DS.'tmp'.DS.'joomleague_import.jlg';
		if (JFile::exists($importFileName))
        {
            JFile::delete($importFileName);
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
	 * @access private
	 * @since  1.5.0a
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
	   $query = JFactory::getDbo()->getQuery(true);
       // Select some fields
        $query->select('ppl.firstname,ppl.lastname');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person as ppl');
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff AS r on r.person_id = ppl.id');
        $query->where('r.id = '.(int)$teamstaff_id);  
        
//		$query="	SELECT	ppl.firstname,
//					ppl.lastname
//				FROM #__".COM_SPORTSMANAGEMENT_TABLE."_person as ppl
//				INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_team_staff AS r on r.person_id=ppl.id
//				WHERE r.id=".(int)$teamstaff_id;
		JFactory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if (JFactory::getDbo()->getAffectedRows())
		{
			$result=JFactory::getDbo()->loadObject();
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
	   $query = JFactory::getDbo()->getQuery(true);
       // Select some fields
        $query->select('ppl.firstname,ppl.lastname');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person as ppl');
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS r on r.person_id = ppl.id');
        $query->where('r.id = '.(int)$teamplayer_id);   
        
//		$query ="	SELECT	ppl.firstname,
//					ppl.lastname
//				FROM #__".COM_SPORTSMANAGEMENT_TABLE."_person as ppl
//				INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_team_player AS r on r.person_id=ppl.id
//				WHERE r.id=".(int)$teamplayer_id;
                
		JFactory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if (JFactory::getDbo()->getAffectedRows())
		{
			$result = JFactory::getDbo()->loadObject();
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
	   $query = JFactory::getDbo()->getQuery(true);
       // Select some fields
        $query->select('ppl.firstname,ppl.lastname');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person as ppl');
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pr on pr.person_id = ppl.id');
        $query->where('pr.id = '.(int)$project_referee_id);   
        
//		$query ="	SELECT	ppl.firstname,
//					ppl.lastname
//				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_person as ppl
//				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pr on pr.person_id=ppl.id
//				WHERE pr.id=".(int)$project_referee_id;
                
		JFactory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if (JFactory::getDbo()->getAffectedRows())
		{
			$result = JFactory::getDbo()->loadObject();
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
		$query = JFactory::getDbo()->getQuery(true);
        // Select some fields
        $query->select('lastname,firstname');
		// From the seasons table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person');
        $query->where('id = '.(int)$person_id);
        
        //$query='SELECT lastname,firstname FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_person WHERE id='.(int)$person_id;
        
		JFactory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if (JFactory::getDbo()->getAffectedRows())
		{
			$result=JFactory::getDbo()->loadObject();
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
        $query = JFactory::getDbo()->getQuery(true);
       // Select some fields
        $query->select('name');
		// From the seasons table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_club');
        $query->where('id = '.(int)$club_id);
		JFactory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if (JFactory::getDbo()->getAffectedRows())
		{
			$result = JFactory::getDbo()->loadResult();
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
        $query = JFactory::getDbo()->getQuery(true);
        // Select some fields
        $query->select('t.name');
		// From the table
		$query->from('#__sportsmanagement_team as t');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
        $query->join('INNER', '#__sportsmanagement_project_team AS pt on pt.team_id = st.id');  
        $query->where('pt.id = '.(int)$team_id);    
		
        JFactory::getDbo()->setQuery($query);
        sportsmanagementModeldatabasetool::runJoomlaQuery();

		if ($object = JFactory::getDbo()->loadObject())
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
	private function _getTeamName2($team_id)
	{
		// Create a new query object.		
		//$db = sportsmanagementHelper::getDBConnection();
        $query = JFactory::getDbo()->getQuery(true);
        // Select some fields
        $query->select('name');
		// From the table
		$query->from('#__sportsmanagement_team');
        $query->where('id = '.(int)$team_id);
		JFactory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
        $result = JFactory::getDbo()->loadResult();
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
	   $query = JFactory::getDbo()->getQuery(true);
       
       // Select some fields
        $query->select('*');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_playground');
        $query->where('id = '.(int)$id);
        
		//$query='SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_playground WHERE id='.(int)$id;
		JFactory::getDbo()->setQuery($query);
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if ( $object = JFactory::getDbo()->loadObject())
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
        $result = JFactory::getDbo()->updateObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_playground', $object, 'id');
        
		//$query='UPDATE #__'.COM_SPORTSMANAGEMENT_TABLE.'_playground SET club_id='.(int)$club_id.' WHERE id='.(int)$playground_id;
//		JFactory::getDbo()->setQuery($query);
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
	   $app = JFactory::getApplication();
       // Create a new query object.		
		//$db = sportsmanagementHelper::getDBConnection();
		$query = JFactory::getDbo()->getQuery(true);
        $query->select('name');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round ');
        $query->where('id = '.(int)$round_id);
        		
        JFactory::getDbo()->setQuery($query);
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if (JFactory::getDbo()->getAffectedRows())
		{
			$result = JFactory::getDbo()->loadResult();
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
	   $app = JFactory::getApplication();
       // Create a new query object.		
		//$db = sportsmanagementHelper::getDBConnection();
		$query = JFactory::getDbo()->getQuery(true);
        $query->select('name');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');
        $query->where('ppos.id = '.(int)$project_position_id);
        
		JFactory::getDbo()->setQuery($query);
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		sportsmanagementModeldatabasetool::runJoomlaQuery();
		if ($object = JFactory::getDbo()->loadResult())
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
	   $app = JFactory::getApplication();
       $db = JFactory::getDbo();
       //$db = sportsmanagementHelper::getDBConnection();
       $query = $db->getQuery(true);
       
		/*
		TO BE FIXED again
		$query="	SELECT id
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project
					WHERE name='$this->_name' AND league_id='$this->_league_id' AND season_id='$this->_season_id'";
		*/
        $query->clear();
          // Select some fields
        $query->select('id');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project');
        $query->where('name LIKE '.$db->Quote(''.addslashes(stripslashes($this->_name)).''));
        //$query->where('country LIKE '.JFactory::getDbo()->Quote(''.$p_club->country.''));
			$db->setQuery($query);
            
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
                sportsmanagementModeldatabasetool::runJoomlaQuery();
        
//		$query="SELECT id FROM #__".COM_SPORTSMANAGEMENT_TABLE."_project WHERE name='".addslashes(stripslashes($this->_name))."'";
//		JFactory::getDbo()->setQuery($query);
//		sportsmanagementModeldatabasetool::runJoomlaQuery();
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
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
		$fieldName=($usedFieldName=='') ? 'name' : $usedFieldName;
        $query->clear();
          // Select some fields
        $query->select($fieldName);
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_'.$tableName);
        $query->where('id = '.$id);
//		$query="SELECT $fieldName FROM #__".COM_SPORTSMANAGEMENT_TABLE."_$tableName WHERE id=$id";
		JFactory::getDbo()->setQuery($query);
		if ($result=JFactory::getDbo()->loadResult())
        {
            return $result;
        }
		return JText::sprintf('Item with ID [%1$s] not found inside [#__'.COM_SPORTSMANAGEMENT_TABLE.'_%2$s]',$id,$tableName);
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importSportsType()
	 * 
	 * @return
	 */
	private function _importSportsType()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
		$my_text='';
		if (!empty($this->_sportstype_new))
		{
		  $query->clear();
          // Select some fields
        $query->select('id');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type');
        $query->where('name LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($this->_sportstype_new)).''));
        //$query->where('country LIKE '.JFactory::getDbo()->Quote(''.$p_club->country.''));
			JFactory::getDbo()->setQuery($query);
            

                sportsmanagementModeldatabasetool::runJoomlaQuery();
//			$query="SELECT id FROM #__".COM_SPORTSMANAGEMENT_TABLE."_sports_type WHERE name='".addslashes(stripslashes($this->_sportstype_new))."'";
//			JFactory::getDbo()->setQuery($query);
			if ($sportstypeObject=JFactory::getDbo()->loadObject())
			{
				$this->_sportstype_id = $sportstypeObject->id;
				$my_text .= '<span style="color:orange">';
				$my_text .= JText::sprintf('Using existing sportstype data: %1$s',"</span><strong>$this->_sportstype_new</strong>");
				$my_text .= '<br />';
			}
			else
			{
				
                $mdl = JModelLegacy::getInstance("sportstype", "sportsmanagementModel");
                $p_sportstype = $mdl->getTable();
            
				$p_sportstype->set('name',trim($this->_sportstype_new));

				if ($p_sportstype->store()===false)
				{
					$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
					$my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
					$my_text .= JText::sprintf('Sportstypename: %1$s',JText::_($this->_sportstype_new)).'<br />';
					//$my_text .= JText::sprintf('Error-Text #%1$s#',JFactory::getDbo()->getErrorMsg()).'<br />';
					//$my_text .= '<pre>'.print_r($p_sportstype,true).'</pre>';
					$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
					//return false;
                    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
				}
				else
				{
					$insertID = JFactory::getDbo()->insertid();
					$this->_sportstype_id = $insertID;
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf('Created new sportstype data: %1$s',"</span><strong>$this->_sportstype_new</strong>");
					$my_text .= '<br />';
				}
			}
		}
		else
		{
			$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
			$my_text .= JText::sprintf(	'Using existing sportstype data: %1$s',
										'</span><strong>'.JText::_($this->_getObjectName('sports_type',$this->_sportstype_id)).'</strong>');
			$my_text .= '<br />';
		}
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importLeague()
	 * 
	 * @return
	 */
	private function _importLeague()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
		$my_text='';
		if (!empty($this->_league_new))
		{
		  $query->clear();
          // Select some fields
        $query->select('id,name,country');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_league');
        $query->where('name LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($this->_league_new)).''));
        //$query->where('country LIKE '.JFactory::getDbo()->Quote(''.$p_club->country.''));
			JFactory::getDbo()->setQuery($query);
            

                sportsmanagementModeldatabasetool::runJoomlaQuery();
                
//			$query="SELECT id FROM #__".COM_SPORTSMANAGEMENT_TABLE."_league WHERE name='".addslashes(stripslashes($this->_league_new))."'";
//			JFactory::getDbo()->setQuery($query);

			if ($leagueObject=JFactory::getDbo()->loadObject())
			{
				$this->_league_id=$leagueObject->id;
				$my_text .= '<span style="color:orange">';
				$my_text .= JText::sprintf('Using existing league data: %1$s',"</span><strong>$this->_league_new</strong>");
				$my_text .= '<br />';
			}
			else
			{
				
                $mdl = JModelLegacy::getInstance("league", "sportsmanagementModel");
                $p_league = $mdl->getTable();
                
				$p_league->set('name',trim($this->_league_new));
				$p_league->set('alias',JFilterOutput::stringURLSafe($this->_league_new));
                
                $p_league->set('short_name',JFilterOutput::stringURLSafe($this->_league_new));
                $p_league->set('middle_name',JFilterOutput::stringURLSafe($this->_league_new));
                
				$p_league->set('country',$this->_league_new_country);
                $p_league->set('sports_type_id',$this->_sportstype_id);

				if ($p_league->store()===false)
				{
					$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
					$my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
					$my_text .= JText::sprintf('Leaguenname: %1$s',$this->_league_new).'<br />';
					//$my_text .= JText::sprintf('Error-Text #%1$s#',JFactory::getDbo()->getErrorMsg()).'<br />';
					//$my_text .= '<pre>'.print_r($p_league,true).'</pre>';
					$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
					//return false;
                    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
				}
				else
				{
					$insertID=JFactory::getDbo()->insertid();
					$this->_league_id=$insertID;
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf('Created new league data: %1$s',"</span><strong>$this->_league_new</strong>");
					$my_text .= '<br />';
				}
			}
		}
		else
		{
			$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
			$my_text .= JText::sprintf(	'Using existing league data: %1$s',
										'</span><strong>'.$this->_getObjectName('league',$this->_league_id).'</strong>');
			$my_text .= '<br />';
		}
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importSeason()
	 * 
	 * @return
	 */
	private function _importSeason()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
		$my_text='';
		if (!empty($this->_season_new))
		{
		  $query->clear();
          // Select some fields
        $query->select('id');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season');
        $query->where('name LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($this->_season_new)).''));
        //$query->where('country LIKE '.JFactory::getDbo()->Quote(''.$p_club->country.''));
			JFactory::getDbo()->setQuery($query);
            

                sportsmanagementModeldatabasetool::runJoomlaQuery();
//			$query="SELECT id FROM #__".COM_SPORTSMANAGEMENT_TABLE."_season WHERE name='".addslashes(stripslashes($this->_season_new))."'";
//			JFactory::getDbo()->setQuery($query);

			if ($seasonObject=JFactory::getDbo()->loadObject())
			{
				$this->_season_id=$seasonObject->id;
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= JText::sprintf('Using existing season data: %1$s',"</span><strong>$this->_season_new</strong>");
				$my_text .= '<br />';
			}
			else
			{
				
                $mdl = JModelLegacy::getInstance("season", "sportsmanagementModel");
                $p_season = $mdl->getTable();
                
				$p_season->set('name',trim($this->_season_new));
				$p_season->set('alias',JFilterOutput::stringURLSafe($this->_season_new));

				if ($p_season->store()===false)
				{
					$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
					$my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
					$my_text .= JText::sprintf('Seasonname: %1$s',$this->_season_new).'<br />';
					//$my_text .= JText::sprintf('Error-Text #%1$s#',JFactory::getDbo()->getErrorMsg()).'<br />';
					//$my_text .= '<pre>'.print_r($p_season,true).'</pre>';
					$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
					//return false; 
                    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
				}
				else
				{
					$insertID=JFactory::getDbo()->insertid();
					$this->_season_id=$insertID;
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf('Created new season data: %1$s',"</span><strong>$this->_season_new</strong>");
					$my_text .= '<br />';
				}
			}
		}
		else
		{
			$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
			$my_text .= JText::sprintf(	'Using existing season data: %1$s',
										'</span><strong>'.$this->_getObjectName('season',$this->_season_id).'</strong>');
			$my_text .= '<br />';
		}
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importEvents()
	 * 
	 * @return
	 */
	private function _importEvents()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
//$this->dump_header("function _importEvents");
		$my_text='';
		if (!isset($this->_datas['event']) || count($this->_datas['event'])==0){return true;}
		if ((!isset($this->_neweventsid) || count($this->_neweventsid)==0) &&
			(!isset($this->_dbeventsid) || count($this->_dbeventsid)==0)){return true;}
		if (!empty($this->_dbeventsid))
		{
			foreach ($this->_dbeventsid AS $key => $id)
			{
				$oldID=$this->_getDataFromObject($this->_datas['event'][$key],'id');
				$this->_convertEventID[$oldID]=$id;
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= JText::sprintf(	'Using existing event data: %1$s',
											'</span><strong>'.JText::_($this->_getObjectName('eventtype',$id)).'</strong>');
				$my_text .= '<br />';
			}
		}

		if (!empty($this->_neweventsid))
		{
			foreach ($this->_neweventsid AS $key => $id)
			{
				
                $mdl = JModelLegacy::getInstance("eventtype", "sportsmanagementModel");
                $p_eventtype = $mdl->getTable();
                
				$import_event=$this->_datas['event'][$key];
				$oldID=$this->_getDataFromObject($import_event,'id');
				$alias=$this->_getDataFromObject($import_event,'alias');
				$p_eventtype->set('name',trim($this->_neweventsname[$key]));
				$p_eventtype->set('icon',$this->_getDataFromObject($import_event,'icon'));
				$p_eventtype->set('parent',$this->_getDataFromObject($import_event,'parent'));
				$p_eventtype->set('splitt',$this->_getDataFromObject($import_event,'splitt'));
				$p_eventtype->set('direction',$this->_getDataFromObject($import_event,'direction'));
				$p_eventtype->set('double',$this->_getDataFromObject($import_event,'double'));
				$p_eventtype->set('sports_type_id',$this->_sportstype_id);
				if ((isset($alias)) && (trim($alias)!=''))
				{
					$p_eventtype->set('alias',$alias);
				}
				else
				{
					$p_eventtype->set('alias',JFilterOutput::stringURLSafe($this->_getDataFromObject($p_eventtype,'name')));
				}
                
                $query->clear();
          // Select some fields
        $query->select('id,name');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype');
        $query->where('name LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($p_eventtype->name)).''));
        
				JFactory::getDbo()->setQuery($query); 
                sportsmanagementModeldatabasetool::runJoomlaQuery();
				if ($object=JFactory::getDbo()->loadObject())
				{
					$this->_convertEventID[$oldID]=$object->id;
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= JText::sprintf('Using existing eventtype data: %1$s','</span><strong>'.JText::_($object->name).'</strong>');
					$my_text .= '<br />';
				}
				else
				{
					if ($p_eventtype->store()===false)
					{
						$my_text .= 'error on event import: ';
						$my_text .= $oldID;
						//$my_text .= "<br />Error: _importEvents<br />#$my_text#<br />#<pre>".print_r($p_eventtype,true).'</pre>#';
						$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
						//return false;
                        sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
					}
					else
					{
						$insertID=JFactory::getDbo()->insertid();
						$this->_convertEventID[$oldID]=$insertID;
						$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= JText::sprintf('Created new eventtype data: %1$s','</span><strong>'.JText::_($p_eventtype->name).'</strong>');
						$my_text .= '<br />';
					}
				}
			}
		}
//$this->dump_variable("this->_convertEventID", $this->_convertEventID);
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	private function _importStatistics()
	{
//$this->dump_header("function _importStatistics");
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
				$my_text .= JText::sprintf(	'Using existing statistic data: %1$s',
											'</span><strong>'.JText::_($this->_getObjectName('statistic',$id)).'</strong>');
				$my_text .= '<br />';
			}
		}

		if (!empty($this->_newstatisticsid))
		{
			foreach ($this->_newstatisticsid AS $key => $id)
			{
				
                $mdl = JModelLegacy::getInstance("statistic", "sportsmanagementModel");
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
					$p_statistic->set('alias',JFilterOutput::stringURLSafe($this->_getDataFromObject($p_statistic,'name')));
				}
				$query="SELECT * FROM #__".COM_SPORTSMANAGEMENT_TABLE."_statistic WHERE name='".addslashes(stripslashes($p_statistic->name))."' AND class='".addslashes(stripslashes($p_statistic->class))."'";
				JFactory::getDbo()->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery();
				if ($object=JFactory::getDbo()->loadObject())
				{
					$this->_convertStatisticID[$oldID]=$object->id;
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= JText::sprintf('Using existing statistic data: %1$s','</span><strong>'.JText::_($object->name).'</strong>');
					$my_text .= '<br />';
				}
				else
				{
					if ($p_statistic->store()===false)
					{
						$my_text .= 'error on statistic import: ';
						$my_text .= $oldID;
						//$my_text .= "<br />Error: _importStatistics<br />#$my_text#<br />#<pre>".print_r($p_statistic,true).'</pre>#';
						$this->_success_text['Importing general statistic data:']=$my_text;
						//return false;
                        sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
					}
					else
					{
						$insertID=JFactory::getDbo()->insertid();
						$this->_convertStatisticID[$oldID]=$insertID;
						$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= JText::sprintf('Created new statistic data: %1$s','</span><strong>'.JText::_($p_statistic->name).'</strong>');
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
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
       //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' _newparentpositionsid<br><pre>'.print_r($this->_newparentpositionsid,true).'</pre>'),'');
//       $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' _dbparentpositionsid<br><pre>'.print_r($this->_dbparentpositionsid,true).'</pre>'),'');
       
       //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' parentposition<br><pre>'.print_r($this->_datas['parentposition'],true).'</pre>'),'');
       
//$this->dump_header("function _importParentPositions");
		$my_text='';
		if (!isset($this->_datas['parentposition']) || count($this->_datas['parentposition'])==0){return true;}
		if ((!isset($this->_newparentpositionsid) || count($this->_newparentpositionsid)==0) &&
			(!isset($this->_dbparentpositionsid) || count($this->_dbparentpositionsid)==0)){return true;}
//$this->dump_variable("this->_dbparentpositionsid", JFactory::getDbo()parentpositionsid);
		if (!empty($this->_dbparentpositionsid))
		{
			foreach ($this->_dbparentpositionsid AS $key => $id)
			{
				$oldID=$this->_getDataFromObject($this->_datas['parentposition'][$key],'id');
				$this->_convertParentPositionID[$oldID]=$id;
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= JText::sprintf(	'Using existing parentposition data: %1$s',
											'</span><strong>'.JText::_($this->_getObjectName('position',$id)).'</strong>');
				$my_text .= '<br />';
			}
		}

//$this->dump_variable("this->_newparentpositionsid", $this->_newparentpositionsid);
		if (!empty($this->_newparentpositionsid))
		{
			foreach ($this->_newparentpositionsid AS $key => $id)
			{
				
                $mdl = JModelLegacy::getInstance("position", "sportsmanagementModel");
                $p_position = $mdl->getTable();
                
				$import_position=$this->_datas['parentposition'][$key];
//$this->dump_variable("import_position", $import_position);
				$oldID = $this->_getDataFromObject($import_position,'id');
				$alias = $this->_getDataFromObject($import_position,'alias');
				$p_position->set('name',trim($this->_newparentpositionsname[$key]));
				$p_position->set('parent_id',0);
				$p_position->set('persontype',$this->_getDataFromObject($import_position,'persontype'));
				$p_position->set('sports_type_id',$this->_sportstype_id);
				$p_position->set('published',1);
				if ((isset($alias)) && (trim($alias)!=''))
				{
					$p_position->set('alias',$alias);
				}
				else
				{
					$p_position->set('alias',JFilterOutput::stringURLSafe($this->_getDataFromObject($p_position,'name')));
				}
				
                $query->clear();
          // Select some fields
        $query->select('id,name');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_position');
        $query->where('name LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($p_position->name)).''));
        $query->where('parent_id = 0');
        
                JFactory::getDbo()->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery();
				if (JFactory::getDbo()->getAffectedRows())
				{
					$p_position->load(JFactory::getDbo()->loadResult());
					$this->_convertParentPositionID[$oldID]=$p_position->id;
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= JText::sprintf('Using existing parent-position data: %1$s','</span><strong>'.JText::_($p_position->name).'</strong>');
					$my_text .= '<br />';
				}
				else
				{
					if ($p_position->store()===false)
					{
						$my_text .= 'error on parent-position import: ';
						$my_text .= $oldID;
						//$my_text .= "<br />Error: _importParentPositions<br />#$my_text#<br />#<pre>".print_r($p_position,true).'</pre>#';
						$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
						//return false;
                        sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
					}
					else
					{
						$insertID = JFactory::getDbo()->insertid();
						$this->_convertParentPositionID[$oldID] = $insertID;
						$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= JText::sprintf('Created new parent-position data: %1$s','</span><strong>'.JText::_($p_position->name).'</strong>');
						$my_text .= '<br />';
					}
				}
//$this->dump_variable("p_position", $p_position);
			}
		}
//$this->dump_variable("this->_convertParentPositionID", $this->_convertParentPositionID);
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importPositions()
	 * 
	 * @return
	 */
	private function _importPositions()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
//       $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' _newpositionsid<br><pre>'.print_r($this->_newpositionsid,true).'</pre>'),'');
//       $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' _dbpositionsid<br><pre>'.print_r($this->_dbpositionsid,true).'</pre>'),'');
       
       //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' position<br><pre>'.print_r($this->_datas['position'],true).'</pre>'),'');
       
       
//$this->dump_header("function _importPositions");
		$my_text='';
		if (!isset($this->_datas['position']) || count($this->_datas['position'])==0){return true;}
		if ((!isset($this->_newpositionsid) || count($this->_newpositionsid)==0) &&
			(!isset($this->_dbpositionsid) || count($this->_dbpositionsid)==0)){return true;}

//$this->dump_variable("this->_dbpositionsid", JFactory::getDbo()positionsid);
		if (!empty($this->_dbpositionsid))
		{
			foreach ($this->_dbpositionsid AS $key => $id)
			{
				$oldID = $this->_getDataFromObject($this->_datas['position'][$key],'id');
				$this->_convertPositionID[$oldID] = $id;
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= JText::sprintf(	'Using existing position data: %1$s',
											'</span><strong>'.JText::_($this->_getObjectName('position',$id)).'</strong>');
				$my_text .= '<br />';
			}
		}

//$this->dump_variable("this->_newpositionsid", $this->_newpositionsid);
		if (!empty($this->_newpositionsid))
		{
			foreach ($this->_newpositionsid AS $key => $id)
			{
				
                $mdl = JModelLegacy::getInstance("position", "sportsmanagementModel");
                $p_position = $mdl->getTable();
                
				$import_position = $this->_datas['position'][$key];
//$this->dump_variable("import_position", $import_position);
				$oldID=$this->_getDataFromObject($import_position,'id');
				$alias=$this->_getDataFromObject($import_position,'alias');
				$p_position->set('name',trim($this->_newpositionsname[$key]));
				$oldParentPositionID=$this->_getDataFromObject($import_position,'parent_id');
				if (isset($this->_convertPositionID[$oldParentPositionID]))
				{
					$p_position->set('parent_id',$this->_convertPositionID[$oldParentPositionID]);
				} else {
					$p_position->set('parent_id', 0);
				}
				//$p_position->set('parent_id',$this->_convertParentPositionID[(int)$this->_getDataFromObject($import_position,'parent_id')]);
				$p_position->set('persontype',$this->_getDataFromObject($import_position,'persontype'));
				$p_position->set('sports_type_id',$this->_sportstype_id);
				$p_position->set('published',1);
				if ((isset($alias)) && (trim($alias)!=''))
				{
					$p_position->set('alias',$alias);
				}
				else
				{
					$p_position->set('alias',JFilterOutput::stringURLSafe($this->_getDataFromObject($p_position,'name')));
				}
				
                $query->clear();
          // Select some fields
        $query->select('id,name');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_position');
        $query->where('name LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($p_position->name)).''));
        $query->where('parent_id = '.$p_position->parent_id);
				
                JFactory::getDbo()->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery();
				if (JFactory::getDbo()->getAffectedRows())
				{
					$p_position->load(JFactory::getDbo()->loadResult());
					// Prevent showing of using existing position twice (see the foreach JFactory::getDbo()positionsid loop)
					if ( isset($this->_convertPositionID[$oldID]) )
                    {
                    if ( $this->_convertPositionID[$oldID] != $p_position->id )
					{
						$this->_convertPositionID[$oldID]=$p_position->id;
						$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
						$my_text .= JText::sprintf('Using existing position data: %1$s','</span><strong>'.JText::_($p_position->name).'</strong>');
						$my_text .= '<br />';
					}
                    }
				}
				else
				{
					if ($p_position->store()===false)
					{
						$my_text .= 'error on position import: ';
						$my_text .= $oldID;
						//$my_text .= "<br />Error: _importPositions<br />#$my_text#<br />#<pre>".print_r($p_position,true).'</pre>#';
						$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
						//return false;
                        sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
					}
					else
					{
						$insertID = JFactory::getDbo()->insertid();
						$this->_convertPositionID[$oldID] = $insertID;
						$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= JText::sprintf('Created new position data: %1$s','</span><strong>'.JText::_($p_position->name).'</strong>');
						$my_text .= '<br />';
					}
				}
//$this->dump_variable("p_position", $p_position);
			}
		}
//$this->dump_variable("this->_convertPositionID", $this->_convertPositionID);
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
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
			
            $mdl = JModelLegacy::getInstance("positioneventtype", "sportsmanagementModel");
            $p_positioneventtype = $mdl->getTable();
                
			$oldEventID=$this->_getDataFromObject($import_positioneventtype,'eventtype_id');
			$oldPositionID=$this->_getDataFromObject($import_positioneventtype,'position_id');
			if (!isset($this->_convertEventID[$oldEventID]) ||
				!isset($this->_convertPositionID[$oldPositionID]))
			{
				$my_text .= '<span style="color:red">';
				$my_text .= JText::sprintf(	'Skipping import of PositionEventtype-ID %1$s. Old-EventID: %2$s - Old-PositionID: %3$s',
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
			JFactory::getDbo()->setQuery($query);
			sportsmanagementModeldatabasetool::runJoomlaQuery();
			if ($object=JFactory::getDbo()->loadObject())
			{
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= JText::sprintf(	'Using existing positioneventtype data - Position: %1$s - Event: %2$s',
								'</span><strong>'.JText::_($this->_getObjectName('position',$p_positioneventtype->position_id)).'</strong><span style="color:'.$this->existingInDbColor.'">',
								'</span><strong>'.JText::_($this->_getObjectName('eventtype',$p_positioneventtype->eventtype_id)).'</strong>');
				$my_text .= '<br />';
			}
			else
			{
				if ($p_positioneventtype->store()===false)
				{
					$my_text .= 'error on PositionEventType import: ';
					$my_text .= '#'.$oldID.'#';
					//$my_text .= "<br />Error: _importPositionEventType<br />#$my_text#<br />#<pre>".print_r($p_positioneventtype,true).'</pre>#';
					$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
					//return false;
                    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
				}
				else
				{
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf(	'Created new positioneventtype data. Position: %1$s - Event: %2$s',
									'</span><strong>'.JText::_($this->_getObjectName('position',$p_positioneventtype->position_id)).'</strong><span style="color:'.$this->storeSuccessColor.'">',
									'</span><strong>'.JText::_($this->_getObjectName('eventtype',$p_positioneventtype->eventtype_id)).'</strong>');
					$my_text .= '<br />';
				}
			}
		}
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	private function _importPlayground()
	{
	   $app = JFactory::getApplication();
	   $query = JFactory::getDbo()->getQuery(true);
       
if ( $this->show_debug_info )
{	   
$this->dump_header("function _importPlayground");
$this->dump_variable("this->_datas playground", $this->_datas['playground']);
}	   
		$my_text='';
		if (!isset($this->_datas['playground']) || count($this->_datas['playground'])==0){return true;}
		if ((!isset($this->_newplaygroundid) || count($this->_newplaygroundid)==0) &&
			(!isset($this->_dbplaygroundsid) || count($this->_dbplaygroundsid)==0)){return true;}

		if (!empty($this->_dbplaygroundsid))
		{
			foreach ($this->_dbplaygroundsid AS $key => $id)
			{
				$oldID=$this->_getDataFromObject($this->_datas['playground'][$key],'id');
				$this->_convertPlaygroundID[$oldID]=$id;
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= JText::sprintf(	'Using existing playground data: %1$s - %2$s',
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
				
                $mdl = JModelLegacy::getInstance("playground", "sportsmanagementModel");
                $p_playground = $mdl->getTable();
                
				$import_playground = $this->_datas['playground'][$key];
				$oldID = $this->_getDataFromObject($import_playground,'id');
				$alias = $this->_getDataFromObject($import_playground,'alias');
				$p_playground->set('name',trim($this->_newplaygroundname[$key]));
				$p_playground->set('short_name',$this->_newplaygroundshort[$key]);
				$p_playground->set('address',$this->_getDataFromObject($import_playground,'address'));
				$p_playground->set('zipcode',$this->_getDataFromObject($import_playground,'zipcode'));
				$p_playground->set('city',$this->_getDataFromObject($import_playground,'city'));
				$p_playground->set('country',$this->_getDataFromObject($import_playground,'country'));
				$p_playground->set('max_visitors',$this->_getDataFromObject($import_playground,'max_visitors'));
				$p_playground->set('website',$this->_getDataFromObject($import_playground,'website'));
				$p_playground->set('picture',$this->_getDataFromObject($import_playground,'picture'));
				$p_playground->set('notes',$this->_getDataFromObject($import_playground,'notes'));
                
    // geo coding
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
    $p_playground->set('latitude',$coords['latitude']);
    $p_playground->set('longitude',$coords['longitude']);        
                
				if ((isset($alias)) && (trim($alias)!=''))
				{
					$p_playground->set('alias',$alias);
				}
				else
				{
					$p_playground->set('alias',JFilterOutput::stringURLSafe($this->_getDataFromObject($p_playground,'name')));
				}
				/*
                if (array_key_exists((int)$this->_getDataFromObject($import_playground,'country'),$this->_convertCountryID))
				{
					$p_playground->set('country',(int)$this->_convertCountryID[(int)$this->_getDataFromObject($import_playground,'country')]);
				}
				else
				{
					$p_playground->set('country',$this->_getDataFromObject($import_playground,'country'));
				}
                */
				if ($this->_importType!='playgrounds')	// force club_id to be set to default if only playgrounds are imported
				{
					//if (!isset($this->_getDataFromObject($import_playground,'club_id')))
					{
						$p_playground->set('club_id',$this->_getDataFromObject($import_playground,'club_id'));
					}
				}
                
                $query->clear();
          // Select some fields
        $query->select('id,name,country');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_playground');
        $query->where('name LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($p_playground->name)).''));
        //$query->where('country LIKE '.JFactory::getDbo()->Quote(''.$p_playground->country.''));
			JFactory::getDbo()->setQuery($query);
 
                sportsmanagementModeldatabasetool::runJoomlaQuery();
				if ($object=JFactory::getDbo()->loadObject())
				{
					$this->_convertPlaygroundID[$oldID]=$object->id;
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= JText::sprintf('Using existing playground data: %1$s',"</span><strong>$object->name</strong>");
					$my_text .= '<br />';
				}
				else
				{
					if ($p_playground->store()===false)
					{
						$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
						$my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
						$my_text .= JText::sprintf('Playgroundname: %1$s',$p_playground->name).'<br />';
						//$my_text .= JText::sprintf('Error-Text #%1$s#',JFactory::getDbo()->getErrorMsg()).'<br />';
						//$my_text .= '<pre>'.print_r($p_playground,true).'</pre>';
						$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
						//return false;
                        sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
					}
					else
					{
						$insertID=JFactory::getDbo()->insertid();
						$this->_convertPlaygroundID[$oldID]=$insertID;
						$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= JText::sprintf('Created new playground data: %1$s (%2$s)',"</span><strong>$p_playground->name</strong>","<strong>$p_playground->country</strong>");
						$my_text .= '<br />';
					}
				}
			}
		}
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importClubs()
	 * 
	 * @return
	 */
	private function _importClubs()
	{
	   $app = JFactory::getApplication();
	   $query = JFactory::getDbo()->getQuery(true);

if ( $this->show_debug_info )
{	   
$this->dump_header("function _importClubs");
$this->dump_variable("this->_datas club", $this->_datas['club']);
$this->dump_variable("this->_dbclubsid", $this->_dbclubsid);
$this->dump_variable("this->_newclubs", $this->_newclubs);
}

		$my_text='';
		// $this->_datas['club'] : array of all clubs obtained from the xml import file
		// $this->_newclubsid    : array of club ids (xml values) for the new clubs to be created in the database
		// JFactory::getDbo()clubsid     : array of club ids (db values) for the existing clubs to be used from the database
		//                         (value of 0 means that the club does not exist in the database)
		if (!isset($this->_datas['club']) || count($this->_datas['club'])==0){return true;}
		if ((!isset($this->_newclubsid) || count($this->_newclubsid)==0) &&
			(!isset($this->_dbclubsid) || count($this->_dbclubsid)==0)){return true;}



		if (!empty($this->_dbclubsid))
		{
		  $query->clear();
          // Select some fields
        $query->select('id,name,standard_playground,country');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_club');
        $query->group('id');
        
			JFactory::getDbo()->setQuery($query);
            sportsmanagementModeldatabasetool::runJoomlaQuery();
			$dbClubs = JFactory::getDbo()->loadObjectList('id');

			foreach ($this->_dbclubsid AS $key => $id)
			{
				if (empty($this->_newclubs[$key]))
				{
					//$oldID = $this->_getDataFromObject($this->_datas['club'][$key],'id');
                    
                    $oldID = $this->_getDataFromObject($this->_datas['team'][$key],'club_id');
					$this->_convertClubID[$oldID] = $id;
                    
                    //$app->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' oldID -> '.$oldID.''),'');
//                    $app->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' id -> '.$id.''),'');

if ( $this->show_debug_info )
{
$this->dump_variable("this->_datas['club'] key", $key);
$this->dump_variable("this->_dbclubsid id", $id);
$this->dump_variable("this->_dbclubsid oldID", $oldID);
}                    
                    
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= JText::sprintf(	'COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_1',
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
				
                $mdl = JModelLegacy::getInstance("club", "sportsmanagementModel");
                $p_club = $mdl->getTable();
                
                //$this->dump_variable("p_club", $p_club);
                
				foreach ($this->_datas['club'] AS $dClub)
				{
					if ($dClub->id == $id)
					{
						$import_club=$dClub;
						break;
					}
				}

if ( $this->show_debug_info )
{                
$this->dump_variable("import_club", $import_club);
}

				$oldID=$this->_getDataFromObject($import_club,'id');
				$alias=$this->_getDataFromObject($import_club,'alias');

if ( $this->show_debug_info )
{
$this->dump_variable("this->_newclubs", $this->_newclubs);
}

				$p_club->set('name',$this->_newclubs[$key]);
				$p_club->set('admin',$this->_joomleague_admin);
				$p_club->set('address',$this->_getDataFromObject($import_club,'address'));
				$p_club->set('zipcode',$this->_getDataFromObject($import_club,'zipcode'));
				$p_club->set('location',$this->_getDataFromObject($import_club,'location'));
				$p_club->set('state',$this->_getDataFromObject($import_club,'state'));
				$p_club->set('country',$this->_newclubscountry[$key]);
				$p_club->set('founded',$this->_getDataFromObject($import_club,'founded'));
				$p_club->set('phone',$this->_getDataFromObject($import_club,'phone'));
				$p_club->set('fax',$this->_getDataFromObject($import_club,'fax'));
				$p_club->set('email',$this->_getDataFromObject($import_club,'email'));
				$p_club->set('website',$this->_getDataFromObject($import_club,'website'));
				$p_club->set('president',$this->_getDataFromObject($import_club,'president'));
				$p_club->set('manager',$this->_getDataFromObject($import_club,'manager'));
				$p_club->set('logo_big',$this->_getDataFromObject($import_club,'logo_big'));
				$p_club->set('logo_middle',$this->_getDataFromObject($import_club,'logo_middle'));
				$p_club->set('logo_small',$this->_getDataFromObject($import_club,'logo_small'));
                
                $p_club->set('dissolved_year',$this->_getDataFromObject($import_club,'dissolved_year'));
                $p_club->set('founded_year',$this->_getDataFromObject($import_club,'founded_year'));
                $p_club->set('unique_id',$this->_getDataFromObject($import_club,'unique_id'));
                $p_club->set('new_club_id',$this->_getDataFromObject($import_club,'new_club_id'));
                
    // geo coding
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
    $p_club->set('latitude',$coords['latitude']);
    $p_club->set('longitude',$coords['longitude']);        
                
                
                
                if ((isset($alias)) && (trim($alias)!=''))
				{
					$p_club->set('alias',$alias);
				}
				else
				{
					$p_club->set('alias',JFilterOutput::stringURLSafe($this->_getDataFromObject($p_club,'name')));
				}
				if ($this->_importType!='clubs')	// force playground_id to be set to default if only clubs are imported
				{
					if (($this->import_version=='NEW') && ($import_club->standard_playground > 0))
					{
						if (isset($this->_convertPlaygroundID[(int)$this->_getDataFromObject($import_club,'standard_playground')]))
						{
							$p_club->set('standard_playground',(int)$this->_convertPlaygroundID[(int)$this->_getDataFromObject($import_club,'standard_playground')]);
						}
					}
				}
				if (($this->import_version=='NEW') && ($import_club->extended!=''))
				{
					$p_club->set('extended',$this->_getDataFromObject($import_club,'extended'));
				}
				
                $query->clear();
          // Select some fields
        $query->select('id,name,country');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_club');
        $query->where('name LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($p_club->name)).''));
        $query->where('country LIKE '.JFactory::getDbo()->Quote(''.$p_club->country.''));
			JFactory::getDbo()->setQuery($query);
            

                sportsmanagementModeldatabasetool::runJoomlaQuery();
				if ( $object = JFactory::getDbo()->loadObject())
				{
					$this->_convertClubID[$oldID] = $object->id;
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= JText::sprintf('Using existing club data: %1$s',"</span><strong>$object->name</strong>");
					$my_text .= '<br />';
				}
				else
				{
					if ($p_club->store()===false)
					{
						$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
						$my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
						$my_text .= JText::sprintf('Clubname: %1$s',$p_club->name).'<br />';
						//$my_text .= JText::sprintf('Error-Text #%1$s#',JFactory::getDbo()->getErrorMsg()).'<br />';
						//$my_text .= '<pre>'.print_r($p_club,true).'</pre>';
						$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
						//return false; 
                        sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
					}
					else
					{
						$insertID = JFactory::getDbo()->insertid();
						$this->_convertClubID[$oldID] = $insertID;
						$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= JText::sprintf(	'Created new club data: %1$s - %2$s',
													"</span><strong>$p_club->name</strong>",
													"".$p_club->country.""
													);
						$my_text .= '<br />';
					}
				}

if ( $this->show_debug_info )
{
$this->dump_variable("p_club", $p_club);
}

			}
		}

if ( $this->show_debug_info )
{
$this->dump_variable("this->_convertClubID", $this->_convertClubID);
}

		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_convertNewPlaygroundIDs()
	 * 
	 * @return
	 */
	private function _convertNewPlaygroundIDs()
	{
    $app = JFactory::getApplication();   

if ( $this->show_debug_info )
{
$this->dump_header("Function _convertNewPlaygroundIDs");
}
if ( $this->show_debug_info )
{
$this->dump_variable("this->_convertPlaygroundID", $this->_convertPlaygroundID);
$this->dump_variable("this->_convertClubID", $this->_convertClubID);
}
		
        $my_text='';
		$converted=false;
		if ( isset($this->_convertPlaygroundID) && !empty($this->_convertPlaygroundID) )
		{
			
            foreach ( $this->_datas['playground'] as $key => $value  )
            {
                
                $import_playground = $this->_datas['playground'][$key];
				$oldID = $this->_getDataFromObject($import_playground,'id');
				$club_id = $this->_getDataFromObject($import_playground,'club_id');
                
                //$app->enqueueMessage(JText::_('result<br><pre>'.print_r($key,true).'</pre>'   ),'');
                
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
							$converted=true;
							$my_text .= '<span style="color:green">';
							$my_text .= JText::sprintf(	'Converted club-info %1$s in imported playground %2$s - [club_id: %3$s] [playground-id: %4$s]',
														'</span><strong>'.$this->_getClubName($new_club_id).'</strong><span style="color:green">',
														"</span><strong>$p_playground->name</strong>",
                                                        "".$new_club_id."",
                                                        "".$new_pg_id."");
							$my_text .= '<br />';
						}
						
                }
                
            }
            
            
            
            /*
            foreach ($this->_convertPlaygroundID AS $key => $new_pg_id)
			{
			 
if ( $this->show_debug_info )
{
$this->dump_variable("this->_convertPlaygroundID -> key", $key);
$this->dump_variable("this->_convertPlaygroundID -> new_club_id", $new_pg_id);
}
             
				$p_playground = $this->_getPlaygroundRecord($new_pg_id);
				foreach ($this->_convertClubID AS $key2 => $new_club_id)
				{

if ( $this->show_debug_info )
{
$this->dump_variable("this->_convertClubID -> key", $key2);
$this->dump_variable("this->_convertClubID -> new_club_id", $new_club_id);
}
                    
					//if (isset($p_playground->club_id) && ($p_playground->club_id == $key2))
					//{
						if ( $this->_updatePlaygroundRecord($new_club_id,$new_pg_id) )
						{
							$converted=true;
							$my_text .= '<span style="color:green">';
							$my_text .= JText::sprintf(	'Converted club-info %1$s in imported playground %2$s',
														'</span><strong>'.$this->_getClubName($new_club_id).'</strong><span style="color:green">',
														"</span><strong>$p_playground->name</strong>");
							$my_text .= '<br />';
						}
						break;
					//}
				}
			}
            */
			if (!$converted){$my_text .= '<span style="color:green">'.JText::_('Nothing needed to be converted').'<br />';}
			$this->_success_text['Converting new playground club-IDs of new playground data:']=$my_text;
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
	   $app = JFactory::getApplication();
	   $query = JFactory::getDbo()->getQuery(true);

if ( $this->show_debug_info )
{
$this->dump_header("Function _importTeams");
}

		$my_text='';
		if (!isset($this->_datas['team']) || count($this->_datas['team'])==0){return true;}
		if ((!isset($this->_newteams) || count($this->_newteams)==0) &&
			(!isset($this->_dbteamsid) || count($this->_dbteamsid)==0)){return true;}


//$this->dump_variable(__FUNCTION__." this->_datas['team']", $this->_datas['team']);
//$this->dump_variable(__FUNCTION__." this->_newteams", $this->_newteams);
//$this->dump_variable(__FUNCTION__." this->_dbteamsid", JFactory::getDbo()teamsid);


		if (!empty($this->_dbteamsid))
		{
		  $query->clear();
          // Select some fields
        $query->select('id,name,club_id,short_name,middle_name,info');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team');
        $query->group('id');
        
			JFactory::getDbo()->setQuery($query);
            sportsmanagementModeldatabasetool::runJoomlaQuery();
            

			$dbTeams = JFactory::getDbo()->loadObjectList('id');

			foreach ($this->_dbteamsid AS $key => $id)
			{
				if (empty($this->_newteams[$key]))
				{
					$oldID = $this->_getDataFromObject($this->_datas['team'][$key],'id');
					$this->_convertTeamID[$oldID] = $id;
                    
                    //$app->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' oldID -> '.$oldID.''),'');
//                    $app->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' id -> '.$id.''),'');
                    
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= JText::sprintf(	'Using existing team data: %1$s - %2$s - %3$s - %4$s <- %5$s',
												'</span><strong>'.$dbTeams[$id]->name.'</strong>',
												'<strong>'.$dbTeams[$id]->short_name.'</strong>',
												'<strong>'.$dbTeams[$id]->middle_name.'</strong>',
												'<strong>'.$dbTeams[$id]->info.'</strong>',
												'<strong>'.$id.'</strong>'
												);
					$my_text .= '<br />';
				}
			}

if ( $this->show_debug_info )
{
$this->dump_variable("_convertTeamID", $this->_convertTeamID);
}
		}
//To Be fixed: Falls Verein neu angelegt wird, muss auch das Team neu angelegt werden.
if ( $this->show_debug_info )
{
echo '_dbclubsid<pre>'.print_r($this->_dbclubsid,true).'</pre>';
echo '_newclubs<pre>'.print_r($this->_newclubs,true).'</pre>';
echo '_newclubsid<pre>'.print_r($this->_newclubsid,true).'</pre>';
echo '_dbteamsid<pre>'.print_r($this->_dbteamsid,true).'</pre>';
echo '_newteams<pre>'.print_r($this->_newteams,true).'</pre>';
echo '_convertClubID<pre>'.print_r($this->_convertClubID,true).'</pre>';
}

		if (!empty($this->_newteams))
		{

if ( $this->show_debug_info )
{		  
$this->dump_variable("newteams", $this->_newteams);
}

			foreach ($this->_newteams AS $key => $value)
			{
				
                $mdl = JModelLegacy::getInstance("team", "sportsmanagementModel");
                $p_team = $mdl->getTable();
            
				$import_team = $this->_datas['team'][$key];

if ( $this->show_debug_info )
{
$this->dump_variable("import_team", $import_team);
}

				$oldID = $this->_getDataFromObject($import_team,'id');
				$alias = $this->_getDataFromObject($import_team,'alias');
				$oldClubID = $this->_getDataFromObject($import_team,'club_id');
                
				if ((!empty($import_team->club_id)) && (isset($this->_convertClubID[$oldClubID])))
				{
					$p_team->set('club_id',$this->_convertClubID[$oldClubID]);
				}
				else
				{
					$p_team->set('club_id',(-1));
				}
                
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' _datas team -><br><pre>'.print_r($this->_datas['team'][$key],true).'</pre>'),'');
//                $app->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' import_team -><br><pre>'.print_r($import_team,true).'</pre>'),'');
//                $app->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' oldClubID -> '.$oldClubID.''),'');
//                $app->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' club_id -> '.$p_team->club_id.''),'');
                
                
				$p_team->set('name',$this->_newteams[$key]);
				$p_team->set('short_name',$this->_newteamsshort[$key]);
				$p_team->set('middle_name',$this->_newteamsmiddle[$key]);
				$p_team->set('website',$this->_getDataFromObject($import_team,'website'));
                
                $p_team->set('agegroup_id',$this->_getDataFromObject($import_team,'agegroup_id'));
                $p_team->set('sports_type_id',$this->_sportstype_id);
                
				$p_team->set('notes',$this->_getDataFromObject($import_team,'notes'));
				$p_team->set('picture',$this->_getDataFromObject($import_team,'picture'));
				$p_team->set('info',$this->_newteamsinfo[$key]);
				if ((isset($alias)) && (trim($alias)!=''))
				{
					$p_team->set('alias',$alias);
				}
				else
				{
					$p_team->set('alias',JFilterOutput::stringURLSafe($this->_getDataFromObject($p_team,'name')));
				}
				if (($this->import_version=='NEW') && ($import_team->extended!=''))
				{
					$p_team->set('extended',$this->_getDataFromObject($import_team,'extended'));
				}
                
                $query->clear();
          // Select some fields
        $query->select('id,name,short_name,middle_name,info,club_id');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team');
        $query->where('name LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($p_team->name)).''));
        $query->where('middle_name LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($p_team->middle_name)).''));
        $query->where('info LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($p_team->info)).''));

				JFactory::getDbo()->setQuery($query); 
                sportsmanagementModeldatabasetool::runJoomlaQuery();
				if ( $object = JFactory::getDbo()->loadObject())
				{
					$this->_convertTeamID[$oldID]=$object->id;
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= JText::sprintf('Using existing team data: %1$s',"</span><strong>$object->name</strong>");
					$my_text .= '<br />';
				}
				else
				{
					if ($p_team->store()===false)
					{
						$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
						$my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
						$my_text .= JText::sprintf('Teamname: %1$s',$p_team->name).'<br />';
						//$my_text .= JText::sprintf('Error-Text #%1$s#',JFactory::getDbo()->getErrorMsg()).'<br />';
						//$my_text .= '<pre>'.print_r($p_team,true).'</pre>';
						$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
						//return false;
                        sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
					}
					else
					{
						$insertID = JFactory::getDbo()->insertid();
						$this->_convertTeamID[$oldID] = $insertID;
						$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= JText::sprintf(	'Created new team data: %1$s - %2$s - %3$s - %4$s - club_id [%5$s]',
													"</span><strong>$p_team->name</strong>",
													"<strong>$p_team->short_name</strong>",
													"<strong>$p_team->middle_name</strong>",
													"<strong>$p_team->info</strong>",
													"<strong>$p_team->club_id</strong>"
													);
						$my_text .= '<br />';
					}
				}
			}
		}
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importPersons()
	 * 
	 * @return
	 */
	private function _importPersons()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
//       $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' _convertPositionID<br><pre>'.print_r($this->_convertPositionID,true).'</pre>'),'');
//       $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' _convertParentPositionID<br><pre>'.print_r($this->_convertParentPositionID,true).'</pre>'),'');
       
		if (!isset($this->_datas['person']) || count($this->_datas['person'])==0){return true;}
		if ((!isset($this->_newpersonsid) || count($this->_newpersonsid)==0) &&
			(!isset($this->_dbpersonsid) || count($this->_dbpersonsid)==0)){return true;}

		$my_text='';
		if (!empty($this->_dbpersonsid))
		{
			foreach ($this->_dbpersonsid AS $key => $id)
			{
				$oldID = $this->_getDataFromObject($this->_datas['person'][$key],'id');
				$this->_convertPersonID[$oldID] = $id;
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= JText::sprintf(	'Using existing person data: %1$s',
											'</span><strong>'.$this->_getObjectName('person',$id,"CONCAT(id,' -> ',lastname,',',firstname,' - ',nickname,' - ',birthday) AS name").'</strong>');
				$my_text .= '<br />';
                
                $mdl = JModelLegacy::getInstance("person", "sportsmanagementModel");
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
$resultperson = JFactory::getDbo()->insertObject('#__sportsmanagement_season_person_id', $newseasonperson);	
} catch (Exception $e) {
$app->enqueueMessage(JText::_($e->getMessage()), 'error');
}
}						
					}
			}
		}
		if (!empty($this->_newpersonsid))
		{
			foreach ($this->_newpersonsid AS $key => $id)
			{
				
                $mdl = JModelLegacy::getInstance("person", "sportsmanagementModel");
                $p_person = $mdl->getTable();
                
				$import_person=$this->_datas['person'][$key];
				$oldID=$this->_getDataFromObject($import_person,'id');
				$p_person->set('lastname',trim($this->_newperson_lastname[$key]));
				$p_person->set('firstname',trim($this->_newperson_firstname[$key]));
				$p_person->set('nickname',trim($this->_newperson_nickname[$key]));
				$p_person->set('birthday',$this->_newperson_birthday[$key]);
				$p_person->set('agegroup_id',$this->_dbpersonsagegroup[$key]);
				$p_person->set('country',$this->_getDataFromObject($import_person,'country'));
				$p_person->set('knvbnr',$this->_getDataFromObject($import_person,'knvbnr'));
				$p_person->set('height',$this->_getDataFromObject($import_person,'height'));
				$p_person->set('weight',$this->_getDataFromObject($import_person,'weight'));
				$p_person->set('picture',$this->_getDataFromObject($import_person,'picture'));
				$p_person->set('show_pic',$this->_getDataFromObject($import_person,'show_pic'));
				$p_person->set('show_persdata',$this->_getDataFromObject($import_person,'show_persdata'));
				$p_person->set('show_teamdata',$this->_getDataFromObject($import_person,'show_teamdata'));
				$p_person->set('show_on_frontend',$this->_getDataFromObject($import_person,'show_on_frontend'));
				$p_person->set('info',$this->_getDataFromObject($import_person,'info'));
				$p_person->set('notes',$this->_getDataFromObject($import_person,'notes'));
				$p_person->set('phone',$this->_getDataFromObject($import_person,'phone'));
				$p_person->set('mobile',$this->_getDataFromObject($import_person,'mobile'));
				$p_person->set('email',$this->_getDataFromObject($import_person,'email'));
				$p_person->set('website',$this->_getDataFromObject($import_person,'website'));
				$p_person->set('address',$this->_getDataFromObject($import_person,'address'));
				$p_person->set('zipcode',$this->_getDataFromObject($import_person,'zipcode'));
				$p_person->set('location',$this->_getDataFromObject($import_person,'location'));
				$p_person->set('state',$this->_getDataFromObject($import_person,'state'));
				$p_person->set('address_country',$this->_getDataFromObject($import_person,'address_country'));
				$p_person->set('extended',$this->_getDataFromObject($import_person,'extended'));
				$p_person->set('published',1);
                
                
    // geo coding
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
    $p_person->set('latitude',$coords['latitude']);
    $p_person->set('longitude',$coords['longitude']);       
    
    
				if ($this->_importType!='persons')	// force position_id to be set to default if only persons are imported
				{
					if ($import_person->position_id > 0)
					{
						if (isset($this->_convertPositionID[(int)$this->_getDataFromObject($import_person,'position_id')]))
						{
							$p_person->set('position_id',(int)$this->_convertPositionID[(int)$this->_getDataFromObject($import_person,'position_id')]);
						}
					}
				}
				$alias = $this->_getDataFromObject($import_person,'alias');
				$aliasparts = array(trim($p_person->firstname),trim($p_person->lastname));
				$p_alias = JFilterOutput::stringURLSafe(implode(' ',$aliasparts));
				if ((isset($alias)) && (trim($alias)!=''))
				{
					$p_person->set('alias',JFilterOutput::stringURLSafe($alias));
				}
				else
				{
					$p_person->set('alias',$p_alias);
				}
                
                //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' person<br><pre>'.print_r($p_person,true).'</pre>'),'');
                $query->clear();
          // Select some fields
        $query->select('*');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person');
        $query->where('firstname LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($p_person->firstname)).''));
        $query->where('lastname LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($p_person->lastname)).''));
        $query->where('nickname LIKE '.JFactory::getDbo()->Quote(''.addslashes(stripslashes($p_person->nickname)).''));
        $query->where('birthday = '.JFactory::getDbo()->Quote(''.$p_person->birthday.''));
			
            
//				$query="	SELECT * FROM #__".COM_SPORTSMANAGEMENT_TABLE."_person
//							WHERE	firstname='".addslashes(stripslashes($p_person->firstname))."' AND
//									lastname='".addslashes(stripslashes($p_person->lastname))."' AND
//									nickname='".addslashes(stripslashes($p_person->nickname))."' AND
//									birthday='$p_person->birthday'";
				JFactory::getDbo()->setQuery($query); 
                sportsmanagementModeldatabasetool::runJoomlaQuery();
				if ($object=JFactory::getDbo()->loadObject())
				{
					$this->_convertPersonID[$oldID]=$object->id;
					$nameStr=!empty($object->nickname) ? '['.$object->nickname.']' : '';
					$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
					$my_text .= JText::sprintf(	'Using existing person data: %1$s %2$s [%3$s] - %4$s',
												"</span><strong>$object->lastname</strong>",
												"<strong>$object->firstname</strong>",
												"<strong>$nameStr</strong>",
												"<strong>$object->birthday</strong>");
					$my_text .= '<br />';
				}
				else
				{
					if ($p_person->store()===false)
					{
						$my_text .= 'error on person import: ';
						$my_text .= $p_person->lastname.'-';
						$my_text .= $p_person->firstname.'-';
						$my_text .= $p_person->nickname.'-';
						$my_text .= $p_person->birthday;
						//$my_text .= "<br />Error: _importPersons<br />#$my_text#<br />#<pre>".print_r($p_person,true).'</pre>#';
						$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
						//return false;
                        sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
					}
					else
					{
						$insertID = JFactory::getDbo()->insertid();
						$this->_convertPersonID[$oldID] = $insertID;
						$dNameStr=((!empty($p_person->lastname)) ?
									$p_person->lastname :
									'<span style="color:orange">'.JText::_('Has no lastname').'</span>');
						$dNameStr .= ','.((!empty($p_person->firstname)) ?
									$p_person->firstname.' - ' :
									'<span style="color:orange">'.JText::_('Has no firstname').' - </span>');
						$dNameStr .= ((!empty($p_person->nickname)) ? "'".$p_person->nickname."' - " : '');
						$dNameStr .= $p_person->birthday;
                        $dNameStr .= '<span style="color:blue"> PositionId old/new->'.$import_person->position_id.' - '.$p_person->position_id.' - </span>';

						$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= JText::sprintf('Created new person data: %1$s',"</span><strong>$dNameStr</strong>" );
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
$resultperson = JFactory::getDbo()->insertObject('#__sportsmanagement_season_person_id', $newseasonperson);												
} catch (Exception $e) {
$app->enqueueMessage(JText::_($e->getMessage()), 'error');
}	
}						
					}
				}
			}
		}
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importProject()
	 * 
	 * @return
	 */
	private function _importProject()
	{
		$app = JFactory::getApplication();
        $my_text='';
		
        $mdl = JModelLegacy::getInstance("project", "sportsmanagementModel");
        $p_project = $mdl->getTable();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' mdl<br><pre>'.print_r($mdl,true).'</pre>'),'Notice');  
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' p_project<br><pre>'.print_r($p_project,true).'</pre>'),'Notice');  
        
		$p_project->set('name',trim($this->_name));
		$p_project->set('alias',JFilterOutput::stringURLSafe(trim($this->_name)));
		$p_project->set('league_id',$this->_league_id);
        $p_project->set('import_project_id',$this->_import_project_id);
		$p_project->set('season_id',$this->_season_id);
		$p_project->set('admin',$this->_joomleague_admin);
		$p_project->set('editor',$this->_joomleague_editor);
		$p_project->set('master_template',$this->_template_id);
		$p_project->set('sub_template_id',0);
		$p_project->set('staffel_id',$this->_getDataFromObject($this->_datas['project'],'staffel_id'));
		$p_project->set('extension',$this->_getDataFromObject($this->_datas['project'],'extension'));
		$p_project->set('timezone',$this->_getDataFromObject($this->_datas['project'],'timezone'));
		$p_project->set('project_type',$this->_getDataFromObject($this->_datas['project'],'project_type'));
		$p_project->set('teams_as_referees',$this->_getDataFromObject($this->_datas['project'],'teams_as_referees'));
		$p_project->set('sports_type_id',$this->_sportstype_id);
		$p_project->set('current_round',$this->_getDataFromObject($this->_datas['project'],'current_round'));
		$p_project->set('current_round_auto',$this->_getDataFromObject($this->_datas['project'],'current_round_auto'));
		$p_project->set('auto_time',$this->_getDataFromObject($this->_datas['project'],'auto_time'));
		$p_project->set('start_date',$this->_getDataFromObject($this->_datas['project'],'start_date'));
		$p_project->set('start_time',$this->_getDataFromObject($this->_datas['project'],'start_time'));
		$p_project->set('fav_team_color',$this->_getDataFromObject($this->_datas['project'],'fav_team_color'));
		$p_project->set('fav_team_text_color',$this->_getDataFromObject($this->_datas['project'],'fav_team_text_color'));
		$p_project->set('use_legs',$this->_getDataFromObject($this->_datas['project'],'use_legs'));
		$p_project->set('game_regular_time',$this->_getDataFromObject($this->_datas['project'],'game_regular_time'));
		$p_project->set('game_parts',$this->_getDataFromObject($this->_datas['project'],'game_parts'));
		$p_project->set('halftime',$this->_getDataFromObject($this->_datas['project'],'halftime'));
		$p_project->set('allow_add_time',$this->_getDataFromObject($this->_datas['project'],'allow_add_time'));
		$p_project->set('add_time',$this->_getDataFromObject($this->_datas['project'],'add_time'));
		$p_project->set('points_after_regular_time',$this->_getDataFromObject($this->_datas['project'],'points_after_regular_time'));
		$p_project->set('points_after_add_time',$this->_getDataFromObject($this->_datas['project'],'points_after_add_time'));
		$p_project->set('points_after_penalty',$this->_getDataFromObject($this->_datas['project'],'points_after_penalty'));
		$p_project->set('template',$this->_getDataFromObject($this->_datas['project'],'template'));
		$p_project->set('enable_sb',$this->_getDataFromObject($this->_datas['project'],'enable_sb'));
		$p_project->set('sb_catid',$this->_getDataFromObject($this->_datas['project'],'sb_catid'));
		if ($this->_publish){$p_project->set('published',1);}
		if ($p_project->store()===false)
		{
			$my_text .= '<span style="color:'.$this->storeFailedColor.'"><strong>';
			$my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_XML_IMPORT_ERROR_IN_FUNCTION',__FUNCTION__).'</strong></span><br />';
			$my_text .= JText::sprintf('Projectname: %1$s',$p_project->name).'<br />';
			//$my_text .= JText::sprintf('Error-Text #%1$s#',JFactory::getDbo()->getErrorMsg()).'<br />';
			//$my_text .= '<pre>'.print_r($p_project,true).'</pre>';
			$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
			//return false;
            sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
		}
		else
		{
			$insertID=JFactory::getDbo()->insertid();
			$this->_project_id=$insertID;
			$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
			$my_text .= JText::sprintf('Created new project data: %1$s',"</span><strong>$this->_name</strong>");
			$my_text .= '<br />';
			$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
			return true;
		}
	}

	/**
	 * check that all templates in default location have a corresponding record,except if project has a master template
	 *
	 */
	private function _checklist()
	{
		$project_id = $this->_project_id;
		$defaultpath = JPATH_COMPONENT_SITE.DS.'settings';
		$extensiontpath = JPATH_COMPONENT_SITE.DS.'extensions'.DS;
		$predictionTemplatePrefix = 'prediction';
        $my_text = '';

		if (!$project_id){return;}

		// get info from project
		$query='SELECT master_template,extension FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project WHERE id='.(int)$project_id;

		JFactory::getDbo()->setQuery($query);
		$params = JFactory::getDbo()->loadObject();

		// if it's not a master template,do not create records.
		if ($params->master_template)
        {
            return true;
        }

		// otherwise,compare the records with the files
		// get records
		$query='SELECT template FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_template_config WHERE project_id='.(int)$project_id;

		JFactory::getDbo()->setQuery($query);
    
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
		//$records = JFactory::getDbo()->loadResultArray();
		if (empty($records))
        {
            $records = array();
        }

		// first check extension template folder if template is not default
		if ((isset($params->extension)) && ($params->extension!=''))
		{
			if (is_dir($extensiontpath.$params->extension.DS.'settings'))
			{
				$xmldirs[] = $extensiontpath.$params->extension.DS.'settings';
			}
		}

		// add default folder
		$xmldirs[] = $defaultpath.DS.'default';

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
							$xmlfile = $xmldir.DS.$file;
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
							JFactory::getDbo()->setQuery($query);
                            
                            $my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
                            $my_text .= JText::sprintf('Created new template data for empty Projectdata: %1$s %2$s',"</span><strong>$template</strong>","<br><strong>".$defaultvalues."</strong>" );
			                $my_text .= '<br />';
                            
							//echo error,allows to check if there is a mistake in the template file
							if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
							{
								$this->setError(JFactory::getDbo()->getErrorMsg());
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
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       $defaultpath = JPATH_COMPONENT_SITE.DS.'settings'.DS.'default';
       
		$my_text='';
		if ($this->_template_id > 0) // Uses a master template
		{
		  $query->clear();
          // Select some fields
        $query->select('id,master_template');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project');
        $query->where('id = '.$this->_template_id);
        
			JFactory::getDbo()->setQuery($query);
			$template_row = JFactory::getDbo()->loadAssoc();
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
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_template_config');
        $query->where('project_id = '.$this->_master_template);
        
			JFactory::getDbo()->setQuery($query);
            sportsmanagementModeldatabasetool::runJoomlaQuery();
			$rows = JFactory::getDbo()->loadObjectList();
			foreach ($rows AS $row)
			{
				
                $mdl = JModelLegacy::getInstance("template", "sportsmanagementModel");
                $p_template = $mdl->getTable();
                
				$p_template->load($row->id);
				$p_template->set('project_id',$this->_project_id);
				if ($p_template->store()===false)
				{
					$my_text .= 'error on master template import: ';
					//$my_text .= "<br />Error: _importTemplate<br />#$my_text#<br />#<pre>".print_r($p_template,true).'</pre>#';
					$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
					//return false;
                    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
				}
				else
				{
					$my_text .= $p_template->template;
					$my_text .= ' <font color="'.$this->storeSuccessColor.'">'.JText::_('...created new data').'</font><br />';
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
				    
					
                    $mdl = JModelLegacy::getInstance("template", "sportsmanagementModel");
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
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ini -> '.'<pre>'.print_r($ini,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' defaultvalues -> '.'<pre>'.print_r($defaultvalues,true).'</pre>'),'');
        
			// beim import kann es vorkommen, das wir in der neuen komponente
                    // zusätzliche felder haben, die mit abgespeichert werden müssen
                    $xmlfile = $defaultpath.DS.$template.'.xml';

                            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' xmlfile -> '.'<pre>'.print_r($xmlfile,true).'</pre>'),'');
							
                            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' template -> '.$template.''),'');
                            
                            //if( file_exists($xmlfile) && $template == 'ranking' ) 
                            if( file_exists($xmlfile) )
                            {

$newparams = array();
$xml = JFactory::getXML($xmlfile,true);
foreach ($xml->fieldset as $paramGroup)
		{
		//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' paramGroup -> '.$template.' <pre>'.print_r($paramGroup,true).'</pre>'),'');
		foreach ($paramGroup->field as $param)
			{
				//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' param -> '.$template.' <pre>'.print_r($param,true).'</pre>'),'');
                $newparams[(string)$param->attributes()->name] = (string)$param->attributes()->default;
			}
        } 

foreach ( $newparams as $key => $value )
{
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' key -> '.$template.' <pre>'.print_r($key,true).'</pre>'),'');
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' key ini -> '.$template.' <pre>'.print_r($ini->get($key),true).'</pre>'),'');
    
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
   
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ini -> '.$template.' <pre>'.print_r($ini,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' newparams -> '.$template.' <pre>'.print_r($newparams,true).'</pre>'),'');

$t_params = json_encode( $newparams ); 
      
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' t_params -> '.$template.' <pre>'.print_r($t_params,true).'</pre>'),'');              
                            
                            }
else
{
$ini = $parameter->toArray($ini);
$t_params = json_encode( $ini ); 
            }
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' import t_params -> '.'<pre>'.print_r($t_params,true).'</pre>'),'');
					
					$p_template->set('params',$t_params);
                    
             unset($t_params);
                  
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' template -> '.'<pre>'.print_r($p_template->template,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params -> '.'<pre>'.print_r($p_template->params,true).'</pre>'),'');
                    
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
							//$my_text .= "<br />Error: _importTemplate<br />#$my_text#<br />#<pre>".print_r($p_template,true).'</pre>#';
							$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
							//return false;
                            sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
						}
						else
						{
							$dTitle = (!empty($p_template->title)) ? JText::_($p_template->title) : $p_template->template;
							$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
							$my_text .= JText::sprintf('Created new template data from Import-Project: %1$ [%2$]',"</span><strong>".$p_template->template."</strong>","<strong>".$p_template->params."</strong>");
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
        $result = JFactory::getDbo()->updateObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project', $object, 'id');
        
//		$query="UPDATE #__".COM_SPORTSMANAGEMENT_TABLE."_project SET master_template=$this->_master_template WHERE id=$this->_project_id";
//        JFactory::getDbo()->setQuery($query);
//		sportsmanagementModeldatabasetool::runJoomlaQuery();
        
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		if ( $this->_master_template == 0 )
		{
			// check and create missing templates if needed
			//$this->_checklist();
			$my_text='<span style="color:green">';
			$my_text .= JText::_('Checked and created missing template data if needed');
			$my_text .= '</span><br />';
			$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] .= $my_text;
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
		$my_text='';
		if (!isset($this->_datas['division']) || count($this->_datas['division']) == 0 ){return true;}
		if (isset($this->_datas['division']))
		{
			foreach ($this->_datas['division'] as $key => $division)
			{
				
                $mdl = JModelLegacy::getInstance("division", "sportsmanagementModel");
                $p_division = $mdl->getTable();
                
				$oldId = (int)$division->id;
				$p_division->set('project_id',$this->_project_id);
				if ( $division->id == $this->_datas['division'][$key]->id )
				{
					$name=trim($this->_getDataFromObject($division,'name'));
					$p_division->set('name',$name);
					$p_division->set('shortname',$this->_getDataFromObject($division,'shortname'));
					$p_division->set('notes',$this->_getDataFromObject($division,'notes'));
					$p_division->set('parent_id',$this->_getDataFromObject($division,'parent_id'));
					if ( trim($p_division->alias) != '' )
					{
						$p_division->set('alias',$this->_getDataFromObject($division,'alias'));
					}
					else
					{
						$p_division->set('alias',JFilterOutput::stringURLSafe($name));
					}
				}
				if ($p_division->store()===false)
				{
					$my_text .= 'error on division import: ';
					$my_text .= '#'.$oldID.'#';
					//$my_text .= "<br />Error: _importDivisions<br />#$my_text#<br />#<pre>".print_r($p_division,true).'</pre>#';
					$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
					//return false;
                    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
				}
				else
				{
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf('Created new division data: %1$s',"</span><strong>$name</strong>");
					$my_text .= '<br />';
				}
				$insertID = JFactory::getDbo()->insertid();
				$this->_convertDivisionID[$oldId] = $insertID;
			}
			$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
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
	   $app = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        
//$this->dump_header("function _importProjectTeam");
		$my_text = '';
		if (!isset($this->_datas['projectteam']) || count($this->_datas['projectteam'])==0){return true;}

		if (!isset($this->_datas['team']) || count($this->_datas['team'])==0)
        {
            return true;
        }
		
        if ((!isset($this->_newteams) || count($this->_newteams)==0) &&
			(!isset($this->_dbteamsid) || count($this->_dbteamsid)==0))
            {
                return true;
            }

//$this->dump_variable(__FUNCTION__." projectteam", $this->_datas['projectteam']);
//$this->dump_variable(__FUNCTION__." _convertTeamID", $this->_convertTeamID);

		$my_text .= __FUNCTION__.' '.__LINE__.' _convertTeamID -> ';
		$my_text .= '<~<pre>'.print_r($this->_convertTeamID,true).'</pre>~>';
        $my_text .= '<br />';
            
        foreach ($this->_datas['projectteam'] as $key => $projectteam)
		{
			
  	        $my_text .= __FUNCTION__.' '.__LINE__.' projectteam -> ';
            $my_text .= '<~<pre>'.print_r($projectteam,true).'</pre>~>';
            $my_text .= '<br />';
            
            $mdl = JModelLegacy::getInstance("projectteam", "sportsmanagementModel");
            $p_projectteam = $mdl->getTable();
                
			$import_projectteam = $this->_datas['projectteam'][$key];
//$this->dump_variable("import_projectteam", $import_projectteam);
			$oldID = $this->_getDataFromObject($import_projectteam,'id');
			$p_projectteam->set('project_id',$this->_project_id);
            $p_projectteam->set('picture',$this->_getDataFromObject($projectteam,'picture'));
            
/**
* jetzt erfolgt die umsetzung der team_id in die neue struktur 
* $p_projectteam->set('team_id',$this->_convertTeamID[$this->_getDataFromObject($projectteam,'team_id')]);
*/            
			$new_team_id = 0;
            $team_id = $this->_convertTeamID[$this->_getDataFromObject($projectteam,'team_id')];
            
            $my_text .= __FUNCTION__.' '.__LINE__.' team_id -> ';
			$my_text .= $team_id;
            $my_text .= '<br />';
            
            if ( $team_id )
            {
            // ist das team schon durch ein anderes projekt angelegt ?
            $query = $db->getQuery(true);
		    $query->select('id');		
		    $query->from('#__sportsmanagement_season_team_id AS t');
		    $query->where('t.team_id = '.$team_id);
            $query->where('t.season_id = '.$this->_season_id);
		    $db->setQuery($query);
		    $new_team_id = $db->loadResult();
            
            $my_text .= __FUNCTION__.' '.__LINE__.' season_team_id -> ';
			$my_text .= $new_team_id;
            $my_text .= '<br />';
                
            if ( $new_team_id )
            {
                $p_projectteam->set('team_id',$new_team_id);
            }
            else
            {
            // Create a new query object.
            $insertquery = $db->getQuery(true);
            // Insert columns.
            $columns = array('team_id','season_id','picture');
            // Insert values.
            $values = array($team_id,$this->_season_id,'\''.$p_projectteam->picture.'\'');
            // Prepare the insert query.
            $insertquery
            ->insert($db->quoteName('#__sportsmanagement_season_team_id'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
            // Set the query using our newly populated query object and execute it.
            $db->setQuery($insertquery);
            
			if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
			{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__); 
			}
			else
			{
                // die neue id übergeben
                $new_team_id = $db->insertid();
                
                $my_text .= __FUNCTION__.' '.__LINE__.' season_team_id -> ';
			    $my_text .= $new_team_id;
                $my_text .= '<br />';
                
                $p_projectteam->set('team_id',$new_team_id);
			}
            }
            }
            
            
            $team_id = $this->_convertTeamID[$this->_getDataFromObject($projectteam,'team_id')];

//$this->dump_variable(__FUNCTION__." _convertTeamID -> team_id", $this->_convertTeamID[$this->_getDataFromObject($projectteam,'team_id')]);

			if ( isset($this->_convertDivisionID) )
            {
            if (count($this->_convertDivisionID) > 0)
			{
				$p_projectteam->set('division_id',$this->_convertDivisionID[$this->_getDataFromObject($projectteam,'division_id')]);
			}
            }

			$p_projectteam->set('start_points',$this->_getDataFromObject($projectteam,'start_points'));
			$p_projectteam->set('points_finally',$this->_getDataFromObject($projectteam,'points_finally'));
			$p_projectteam->set('neg_points_finally',$this->_getDataFromObject($projectteam,'neg_points_finally'));
			$p_projectteam->set('matches_finally',$this->_getDataFromObject($projectteam,'matches_finally'));
			$p_projectteam->set('won_finally',$this->_getDataFromObject($projectteam,'won_finally'));
			$p_projectteam->set('draws_finally',$this->_getDataFromObject($projectteam,'draws_finally'));
			$p_projectteam->set('lost_finally',$this->_getDataFromObject($projectteam,'lost_finally'));
			$p_projectteam->set('homegoals_finally',$this->_getDataFromObject($projectteam,'homegoals_finally'));
			$p_projectteam->set('guestgoals_finally',$this->_getDataFromObject($projectteam,'guestgoals_finally'));
			$p_projectteam->set('diffgoals_finally',$this->_getDataFromObject($projectteam,'diffgoals_finally'));
			$p_projectteam->set('is_in_score',$this->_getDataFromObject($projectteam,'is_in_score'));
			$p_projectteam->set('use_finally',$this->_getDataFromObject($projectteam,'use_finally'));
			$p_projectteam->set('admin',$this->_joomleague_admin);

			if ($this->import_version=='NEW')
			{
				if (isset($import_projectteam->mark))
				{
					$p_projectteam->set('mark',$this->_getDataFromObject($projectteam,'mark'));
				}
				$p_projectteam->set('info',$this->_getDataFromObject($projectteam,'info'));
				$p_projectteam->set('reason',$this->_getDataFromObject($projectteam,'reason'));
				$p_projectteam->set('notes',$this->_getDataFromObject($projectteam,'notes'));
			}
			else
			{
				$p_projectteam->set('notes',$this->_getDataFromObject($projectteam,'description'));
				$p_projectteam->set('reason',$this->_getDataFromObject($projectteam,'info'));
			}
			if ((isset($projectteam->standard_playground)) && ($projectteam->standard_playground > 0))
			{
				if (isset($this->_convertPlaygroundID[$this->_getDataFromObject($projectteam,'standard_playground')]))
				{
					$p_projectteam->set('standard_playground',$this->_convertPlaygroundID[$this->_getDataFromObject($projectteam,'standard_playground')]);
				}
			}

			if ( $p_projectteam->store() === false )
			{
				$my_text .= 'error on projectteam import: ';
				$my_text .= $oldID;
                $my_text .= '<br />';
                $my_text .= '<~<pre>'.print_r(JFactory::getDbo()->getErrorMsg(),true).'</pre>~>';
                $my_text .= '<br />';
				//$my_text .= '<br />Error: _importProjectTeam<br />~'.$my_text.'~<br />~<pre>'.print_r($p_projectteam,true).'</pre>~';
				$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
				//return false;
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
			}
			else
			{
				$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
				$my_text .= JText::sprintf(	'Created new projectteam data: %1$s - Team ID : %2$s',
											'</span><strong>'.$this->_getTeamName2($p_projectteam->team_id).'</strong>',
                                            '<strong>'.$team_id.'</strong>');
				$my_text .= '<br />';
			}
			$insertID = $p_projectteam->id;//JFactory::getDbo()->insertid();
			
            if ($this->import_version=='NEW')
			{
            $this->_convertProjectTeamID[$this->_getDataFromObject($projectteam,'id')] = $p_projectteam->id;
            }
            else
            {
            $this->_convertProjectTeamID[$this->_getDataFromObject($projectteam,'team_id')] = $p_projectteam->id;    
            }

//$this->dump_variable(__FUNCTION__." p_projectteam", $p_projectteam);

		}

//$this->dump_variable(__FUNCTION__." this->_convertProjectTeamID", $this->_convertProjectTeamID);

		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importProjectReferees()
	 * 
	 * @return
	 */
	private function _importProjectReferees()
	{
		$my_text='';
		if (!isset($this->_datas['projectreferee']) || count($this->_datas['projectreferee'])==0){return true;}

		if (!isset($this->_datas['person']) || count($this->_datas['person'])==0){return true;}
		if ((!isset($this->_newpersonsid) || count($this->_newpersonsid)==0) &&
			(!isset($this->_dbpersonsid) || count($this->_dbpersonsid)==0)){return true;}

		foreach ($this->_datas['projectreferee'] as $key => $projectreferee)
		{
			$import_projectreferee=$this->_datas['projectreferee'][$key];
			$oldID=$this->_getDataFromObject($import_projectreferee,'id');
			
            $mdl = JModelLegacy::getInstance("projectreferee", "sportsmanagementModel");
            $p_projectreferee = $mdl->getTable();

			$p_projectreferee->set('project_id',$this->_project_id);
			$p_projectreferee->set('person_id',$this->_convertPersonID[$this->_getDataFromObject($import_projectreferee,'person_id')]);
			$p_projectreferee->set('project_position_id',$this->_convertProjectPositionID[$this->_getDataFromObject($import_projectreferee,'project_position_id')]);

			$p_projectreferee->set('notes',$this->_getDataFromObject($import_projectreferee,'notes'));
			$p_projectreferee->set('picture',$this->_getDataFromObject($import_projectreferee,'picture'));
			$p_projectreferee->set('extended',$this->_getDataFromObject($import_projectreferee,'extended'));
            $p_projectreferee->set('published',1);

			if ($p_projectreferee->store()===false)
			{
				$my_text .= 'error on projectreferee import: ';
				$my_text .= $oldID;
				$my_text .= '<br />Error: _importProjectReferees<br />~'.$my_text.'~<br />~<pre>'.print_r($p_projectreferee,true).'</pre>~';
				$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
				return false;
			}
			else
			{
				$dPerson=$this->_getPersonName($p_projectreferee->person_id);
				$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
				$my_text .= JText::sprintf(	'Created new projectreferee data: %1$s,%2$s',"</span><strong>$dPerson->lastname","$dPerson->firstname</strong>");
				$my_text .= '<br />';
			}
			$insertID=$p_projectreferee->id;//JFactory::getDbo()->insertid();
			$this->_convertProjectRefereeID[$oldID]=$insertID;
		}
//$this->dump_variable("this->_convertProjectRefereeID", $this->_convertProjectRefereeID);
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importProjectPositions()
	 * 
	 * @return
	 */
	private function _importProjectPositions()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
//$this->dump_header("function _importProjectPositions");
		$my_text = '';
//$this->dump_variable("this->_datas['projectposition']", $this->_datas['projectposition']);
//$this->dump_variable("this->_newpositionsid", $this->_newpositionsid);
//$this->dump_variable("this->_dbpositionsid", JFactory::getDbo()positionsid);
		if (!isset($this->_datas['projectposition']) || count($this->_datas['projectposition'])==0){return true;}

		if (!isset($this->_datas['position']) || count($this->_datas['position'])==0){return true;}
		if ((!isset($this->_newpositionsid) || count($this->_newpositionsid)==0) &&
			(!isset($this->_dbpositionsid) || count($this->_dbpositionsid)==0)){return true;}

		foreach ($this->_datas['projectposition'] as $key => $projectposition)
		{
			$import_projectposition = $this->_datas['projectposition'][$key];
//$this->dump_variable("import_projectposition", $import_projectposition);
			$oldID=$this->_getDataFromObject($import_projectposition,'id');
			
            $mdl = JModelLegacy::getInstance("projectposition", "sportsmanagementModel");
            $p_projectposition = $mdl->getTable();
            
			$p_projectposition->set('project_id',$this->_project_id);
			$oldPositionID = $this->_getDataFromObject($import_projectposition,'position_id');
			
            if (!isset($this->_convertPositionID[$oldPositionID]))
			{
				$my_text .= '<span style="color:red">';
				$my_text .= JText::sprintf(	'Skipping import of ProjectPosition-ID %1$s. Old-PositionID: %2$s',
								"</span><strong>$oldID</strong><span style='color:red'>",
								"</span><strong>$oldPositionID</strong>").'<br />';
				continue;
			}
            
			$p_projectposition->set('position_id',$this->_convertPositionID[$oldPositionID]);
            
            $query->clear();
            // Select some fields
            $query->select('id');
		      // From the table
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
			if ( $p_projectposition->store() === false )
			{
				$my_text .= 'error on ProjectPosition import: ';
				$my_text .= '#'.$oldID.'#';
				//$my_text .= "<br />Error: _importProjectpositions<br />#$my_text#<br />#<pre>".print_r($p_projectposition,true).'</pre>#';
				$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
				//return false;
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
			}
			else
			{
				$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
				$my_text .= JText::sprintf(	'Created new projectposition data: %1$s - %2$s',
								"</span><strong>".JText::_($this->_getObjectName('position',$p_projectposition->position_id)).'</strong><span style="color:'.$this->storeSuccessColor.'">',
								"</span><strong>".$p_projectposition->position_id.'</strong>');
				$my_text .= '<br />';
			}
            }
//$this->dump_variable("p_projectposition", $p_projectposition);
			$insertID = $p_projectposition->id;//JFactory::getDbo()->insertid();
			$this->_convertProjectPositionID[$oldID] = $insertID;
		}
//$this->dump_variable("this->_convertProjectPositionID", $this->_convertProjectPositionID);
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importTeamPlayer()
	 * 
	 * @return
	 */
	private function _importTeamPlayer()
	{
	   $app = JFactory::getApplication();
//$this->dump_header("function _importTeamPlayer");
		$my_text='';
		if (!isset($this->_datas['teamplayer']) || count($this->_datas['teamplayer'])==0){return true;}

		if (!isset($this->_datas['person']) || count($this->_datas['person'])==0){return true;}
		if ((!isset($this->_newpersonsid) || count($this->_newpersonsid)==0) &&
			(!isset($this->_dbpersonsid) || count($this->_dbpersonsid)==0)){return true;}

//$this->dump_variable("_convertProjectTeamID", $this->_convertProjectTeamID);
		foreach ($this->_datas['teamplayer'] as $key => $teamplayer)
		{
			
            $mdl = JModelLegacy::getInstance("teamplayer", "sportsmanagementModel");
            $p_teamplayer = $mdl->getTable();
            
			$import_teamplayer=$this->_datas['teamplayer'][$key];
//$this->dump_variable("import_teamplayer", $import_teamplayer);
			$oldID=$this->_getDataFromObject($import_teamplayer,'id');
			$oldTeamID=$this->_getDataFromObject($import_teamplayer,'projectteam_id');
			$oldPersonID=$this->_getDataFromObject($import_teamplayer,'person_id');
			if (!isset($this->_convertProjectTeamID[$oldTeamID]) ||
				!isset($this->_convertPersonID[$oldPersonID]))
			{
				$my_text .= '<span style="color:red">';
				$my_text .= JText::sprintf(	'Skipping import of TeamPlayer-ID %1$s. Old-ProjectTeamID: %2$s - Old-PersonID: %3$s',
								"</span><strong>$oldID</strong><span style='color:red'>",
								"</span><strong>$oldTeamID</strong><span style='color:red'>",
								"</span><strong>$oldPersonID</strong>").'<br />';
				continue;
			}
			$p_teamplayer->set('projectteam_id',$this->_convertProjectTeamID[$oldTeamID]);
			$p_teamplayer->set('person_id',$this->_convertPersonID[$oldPersonID]);
			$oldPositionID=$this->_getDataFromObject($import_teamplayer,'project_position_id');
			if (isset($this->_convertProjectPositionID[$oldPositionID]))
			{
				$p_teamplayer->set('project_position_id',$this->_convertProjectPositionID[$oldPositionID]);
			}
			$p_teamplayer->set('active',$this->_getDataFromObject($import_teamplayer,'active'));
			$p_teamplayer->set('jerseynumber',$this->_getDataFromObject($import_teamplayer,'jerseynumber'));
			$p_teamplayer->set('notes',$this->_getDataFromObject($import_teamplayer,'notes'));
			$p_teamplayer->set('picture',$this->_getDataFromObject($import_teamplayer,'picture'));
			$p_teamplayer->set('extended',$this->_getDataFromObject($import_teamplayer,'extended'));
			$p_teamplayer->set('injury',$this->_getDataFromObject($import_teamplayer,'injury'));
			$p_teamplayer->set('injury_date',$this->_getDataFromObject($import_teamplayer,'injury_date'));
			$p_teamplayer->set('injury_end',$this->_getDataFromObject($import_teamplayer,'injury_end'));
			$p_teamplayer->set('injury_detail',$this->_getDataFromObject($import_teamplayer,'injury_detail'));
			$p_teamplayer->set('injury_date_start',$this->_getDataFromObject($import_teamplayer,'injury_date_start'));
			$p_teamplayer->set('injury_date_end',$this->_getDataFromObject($import_teamplayer,'injury_date_end'));
			$p_teamplayer->set('suspension',$this->_getDataFromObject($import_teamplayer,'suspension'));
			$p_teamplayer->set('suspension_date',$this->_getDataFromObject($import_teamplayer,'suspension_date'));
			$p_teamplayer->set('suspension_end',$this->_getDataFromObject($import_teamplayer,'suspension_end'));
			$p_teamplayer->set('suspension_detail',$this->_getDataFromObject($import_teamplayer,'suspension_detail'));
			$p_teamplayer->set('susp_date_start',$this->_getDataFromObject($import_teamplayer,'susp_date_start'));
			$p_teamplayer->set('susp_date_end',$this->_getDataFromObject($import_teamplayer,'susp_date_end'));
			$p_teamplayer->set('away',$this->_getDataFromObject($import_teamplayer,'away'));
			$p_teamplayer->set('away_date',$this->_getDataFromObject($import_teamplayer,'away_date'));
			$p_teamplayer->set('away_end',$this->_getDataFromObject($import_teamplayer,'away_end'));
			$p_teamplayer->set('away_detail',$this->_getDataFromObject($import_teamplayer,'away_detail'));
			$p_teamplayer->set('away_date_start',$this->_getDataFromObject($import_teamplayer,'away_date_start'));
			$p_teamplayer->set('away_date_end',$this->_getDataFromObject($import_teamplayer,'away_date_end'));
            $p_teamplayer->set('published',1);

			if ($p_teamplayer->store()===false)
			{
				$my_text .= 'error on teamplayer import: ';
				$my_text .= $oldID;
				//$my_text .= "<br />Error: _importTeamPlayer<br />#$my_text#<br />#<pre>".print_r($p_teamplayer,true).'</pre>#';
				$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
				//return false;
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
			}
			else
			{
				$dPerson = $this->_getPersonName($p_teamplayer->person_id);
				$project_position_id = $p_teamplayer->project_position_id;
				if($project_position_id>0) {
					$query ='SELECT *
								FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position
								WHERE	id='.$project_position_id;
					JFactory::getDbo()->setQuery($query);
					sportsmanagementModeldatabasetool::runJoomlaQuery();
					$object = JFactory::getDbo()->loadObject();
					$position_id = $object->position_id;
					$dPosName = JText::_($this->_getObjectName('position',$position_id));
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf(	'Created new teamplayer data. Team: %1$s - Person: %2$s,%3$s - Position: %4$s',
									'</span><strong>'.$this->_getTeamName($p_teamplayer->projectteam_id).'</strong><span style="color:'.$this->storeSuccessColor.'">',
									'</span><strong>'.$dPerson->lastname,$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
									"</span><strong>$dPosName</strong>");
					$my_text .= '<br />';
				} else {
					$dPosName='<span style="color:orange">'.JText::_('Has no position').'</span>';
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf(	'Created new teamplayer data. Team: %1$s - Person: %2$s,%3$s - Position: %4$s',
									'</span><strong>'.$this->_getTeamName($p_teamplayer->projectteam_id).'</strong><span style="color:'.$this->storeSuccessColor.'">',
									'</span><strong>'.$dPerson->lastname,$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
									"</span><strong>$dPosName</strong>");
					$my_text .= '<br />';
				}
			}
			$insertID = $p_teamplayer->id;//JFactory::getDbo()->insertid();
			$this->_convertTeamPlayerID[$oldID]=$insertID;
		}
//$this->dump_variable("this->_convertTeamPlayerID", $this->_convertTeamPlayerID);
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importTeamStaff()
	 * 
	 * @return
	 */
	private function _importTeamStaff()
	{
//$this->dump_header("function _importTeamStaff");
		$my_text='';
		if (!isset($this->_datas['teamstaff']) || count($this->_datas['teamstaff'])==0){return true;}

		if (!isset($this->_datas['person']) || count($this->_datas['person'])==0){return true;}
		if ((!isset($this->_newpersonsid) || count($this->_newpersonsid)==0) &&
			(!isset($this->_dbpersonsid) || count($this->_dbpersonsid)==0)){return true;}

//$this->dump_variable("this->_convertPersonID", $this->_convertPersonID);
		foreach ($this->_datas['teamstaff'] as $key => $teamstaff)
		{
			
            $mdl = JModelLegacy::getInstance("teamstaff", "sportsmanagementModel");
            $p_teamstaff = $mdl->getTable();
            
			$import_teamstaff=$this->_datas['teamstaff'][$key];
//$this->dump_variable("import_teamstaff", $import_teamstaff);
			$oldID = $this->_getDataFromObject($import_teamstaff,'id');
			$oldProjectTeamID = $this->_getDataFromObject($import_teamstaff,'projectteam_id');
			$oldPersonID = $this->_getDataFromObject($import_teamstaff,'person_id');
			if (!isset($this->_convertProjectTeamID[$oldProjectTeamID]) ||
				!isset($this->_convertPersonID[$oldPersonID]))
			{
				$my_text .= '<span style="color:red">';
				$my_text .= JText::sprintf(	'Skipping import of TeamStaff-ID %1$s. Old-ProjectTeamID: %2$s - Old-PersonID: %3$s',
								"</span><strong>$oldID</strong><span style='color:red'>",
								"</span><strong>$oldProjectTeamID</strong><span style='color:red'>",
								"</span><strong>$oldPersonID</strong>").'<br />';
				continue;
			}
			$p_teamstaff->set('projectteam_id',$this->_convertProjectTeamID[$oldProjectTeamID]);
			$p_teamstaff->set('person_id',$this->_convertPersonID[$oldPersonID]);
			$oldPositionID=$this->_getDataFromObject($import_teamstaff,'project_position_id');
			if (isset($this->_convertProjectPositionID[$oldPositionID]))
			{
				$p_teamstaff->set('project_position_id',$this->_convertProjectPositionID[$oldPositionID]);
			}
			$p_teamstaff->set('active',$this->_getDataFromObject($import_teamstaff,'active'));
			$p_teamstaff->set('notes',$this->_getDataFromObject($import_teamstaff,'notes'));
			$p_teamstaff->set('injury',$this->_getDataFromObject($import_teamstaff,'injury'));
			$p_teamstaff->set('injury_date',$this->_getDataFromObject($import_teamstaff,'injury_date'));
			$p_teamstaff->set('injury_end',$this->_getDataFromObject($import_teamstaff,'injury_end'));
			$p_teamstaff->set('injury_detail',$this->_getDataFromObject($import_teamstaff,'injury_detail'));
			$p_teamstaff->set('injury_date_start',$this->_getDataFromObject($import_teamstaff,'injury_date_start'));
			$p_teamstaff->set('injury_date_end',$this->_getDataFromObject($import_teamstaff,'injury_date_end'));
			$p_teamstaff->set('suspension',$this->_getDataFromObject($import_teamstaff,'suspension'));
			$p_teamstaff->set('suspension_date',$this->_getDataFromObject($import_teamstaff,'suspension_date'));
			$p_teamstaff->set('suspension_end',$this->_getDataFromObject($import_teamstaff,'suspension_end'));
			$p_teamstaff->set('suspension_detail',$this->_getDataFromObject($import_teamstaff,'suspension_detail'));
			$p_teamstaff->set('susp_date_start',$this->_getDataFromObject($import_teamstaff,'susp_date_start'));
			$p_teamstaff->set('susp_date_end',$this->_getDataFromObject($import_teamstaff,'susp_date_end'));
			$p_teamstaff->set('away',$this->_getDataFromObject($import_teamstaff,'away'));
			$p_teamstaff->set('away_date',$this->_getDataFromObject($import_teamstaff,'away_date'));
			$p_teamstaff->set('away_end',$this->_getDataFromObject($import_teamstaff,'away_end'));
			$p_teamstaff->set('away_detail',$this->_getDataFromObject($import_teamstaff,'away_detail'));
			$p_teamstaff->set('away_date_start',$this->_getDataFromObject($import_teamstaff,'away_date_start'));
			$p_teamstaff->set('away_date_end',$this->_getDataFromObject($import_teamstaff,'away_date_end'));
			$p_teamstaff->set('picture',$this->_getDataFromObject($import_teamstaff,'picture'));
			$p_teamstaff->set('extended',$this->_getDataFromObject($import_teamstaff,'extended'));
            $p_teamstaff->set('published',1);

			if ($p_teamstaff->store()===false)
			{
				$my_text .= 'error on teamstaff import: ';
				$my_text .= $oldID;
				//$my_text .= "<br />Error: _importTeamStaff<br />#$my_text#<br />#<pre>".print_r($p_teamstaff,true).'</pre>#';
				$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
				//return false;
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
			}
			else
			{
				$dPerson=$this->_getPersonName($p_teamstaff->person_id);
				$project_position_id = $p_teamstaff->project_position_id;
				if($project_position_id>0) 
                {
					$query ='SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position WHERE id='.$project_position_id;
					JFactory::getDbo()->setQuery($query);
					sportsmanagementModeldatabasetool::runJoomlaQuery();
					$object=JFactory::getDbo()->loadObject();
					$position_id = $object->position_id;
					$dPosName=JText::_($this->_getObjectName('position',$position_id));
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf(	'Created new teamstaff data. Team: %1$s - Person: %2$s,%3$s - Position: %4$s',
									'</span><strong>'.$this->_getTeamName($p_teamstaff->projectteam_id).'</strong><span style="color:'.$this->storeSuccessColor.'">',
									'</span><strong>'.$dPerson->lastname,
									$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
									"</span><strong>$dPosName</strong>");
					$my_text .= '<br />';
				} 
                else 
                {
					$dPosName='<span style="color:orange">'.JText::_('Has no position').'</span>';
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf(	'Created new teamstaff data. Team: %1$s - Person: %2$s,%3$s - Position: %4$s',
									'</span><strong>'.$this->_getTeamName($p_teamstaff->projectteam_id).'</strong><span style="color:'.$this->storeSuccessColor.'">',
									'</span><strong>'.$dPerson->lastname,$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
									"</span><strong>$dPosName</strong>");
					$my_text .= '<br />';
				}
			}
//$this->dump_variable("p_teamstaff", $p_teamstaff);
			$insertID=$p_teamstaff->id;//JFactory::getDbo()->insertid();
			$this->_convertTeamStaffID[$oldID]=$insertID;
		}
//$this->dump_variable("this->_convertTeamStaffID", $this->_convertTeamStaffID);
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importTeamTraining()
	 * 
	 * @return
	 */
	private function _importTeamTraining()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
		$my_text='';
		if (!isset($this->_datas['teamtraining']) || count($this->_datas['teamtraining'])==0)
        {
        return true;
        }

		foreach ($this->_datas['teamtraining'] as $key => $teamtraining)
		{
			
            $mdl = JModelLegacy::getInstance("trainingdata", "sportsmanagementModel");
            $p_teamtraining = $mdl->getTable();
            
			$import_teamtraining = $this->_datas['teamtraining'][$key];
			$oldID = $this->_getDataFromObject($import_teamtraining,'id');
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' teamtraining<br><pre>'.print_r($import_teamtraining,true).'</pre>'),'');

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
		JFactory::getDbo()->setQuery($query);
        sportsmanagementModeldatabasetool::runJoomlaQuery();
        $team_id = JFactory::getDbo()->loadResult();
       
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
            JFactory::getDbo()->setQuery($query);
            sportsmanagementModeldatabasetool::runJoomlaQuery();
//            $team_tr_id = JFactory::getDbo()->loadResult();
            if ( !JFactory::getDbo()->loadResult() )
            {
            if ($p_teamtraining->store()===false)
			{
				$my_text .= 'error on teamtraining import: ';
				$my_text .= $oldID;
				//$my_text .= "<br />Error: _importTeamTraining<br />#$my_text#<br />#<pre>".print_r($p_teamtraining,true).'</pre>#';
				$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML_IMPORT_TEAMTRAINING_0')] = $my_text;
				//return false;
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
			}
			else
			{
				$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
				$my_text .= JText::sprintf(	'Created new teamtraining data. Team: [%1$s]',
								'</span><strong>'.$this-> _getTeamName($p_teamtraining->project_team_id).'</strong>');
				$my_text .= '<br />';
			}
            }
		}
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')] = $my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importRounds()
	 * 
	 * @return
	 */
	private function _importRounds()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
		$my_text='';
		if (!isset($this->_datas['round']) || count($this->_datas['round'])==0)
        {
        return true;
        }

		foreach ($this->_datas['round'] as $key => $round)
		{
			
            $mdl = JModelLegacy::getInstance("round", "sportsmanagementModel");
            $p_round = $mdl->getTable();
            
			$oldId=(int)$round->id;
			$name=trim($this->_getDataFromObject($round,'name'));
			$alias=trim($this->_getDataFromObject($round,'alias'));
			// if the roundcode field is empty,it is an old .jlg-Import file
			$roundnumber=$this->_getDataFromObject($round,'roundcode');
			if (empty($roundnumber))
			{
				$roundnumber=$this->_getDataFromObject($round,'matchcode');
			}
			$p_round->set('roundcode',$roundnumber);
			$p_round->set('name',$name);
			if ($alias!='')
			{
				$p_round->set('alias',$alias);
			}
			else
			{
				$p_round->set('alias',JFilterOutput::stringURLSafe($name));
			}
			$p_round->set('round_date_first',$this->_getDataFromObject($round,'round_date_first'));
			$round_date_last=trim($this->_getDataFromObject($round,'round_date_last'));
			if (($round_date_last=='') || ($round_date_last=='0000-00-00'))
			{
				$round_date_last=$this->_getDataFromObject($round,'round_date_first');
			}
			$p_round->set('round_date_last',$round_date_last);
			$p_round->set('project_id',$this->_project_id);
			if ($p_round->store()===false)
			{
				$my_text .= 'error on round import: ';
				$my_text .= $oldID;
				//$my_text .= "<br />Error: _importRounds<br />#$my_text#<br />#<pre>".print_r($p_round,true).'</pre>#';
				$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
				//return false;
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
			}
			else
			{
				$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
				$my_text .= JText::sprintf('Created new round: %1$s',"</span><strong>$name</strong>");
				$my_text .= '<br />';
			}
			$insertID=JFactory::getDbo()->insertid();
			$this->_convertRoundID[$oldId]=$insertID;
		}
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importMatches()
	 * 
	 * @return
	 */
	private function _importMatches()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
       
       //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__. ' _convertTeamID <br><pre>'.print_r($this->_convertTeamID,true).'</pre>'),'');
       //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__. ' _convertProjectTeamID <br><pre>'.print_r($this->_convertProjectTeamID,true).'</pre>'),'');
 
       
//$this->dump_header("function _importMatches");
		$my_text='';
		if (!isset($this->_datas['match']) || count($this->_datas['match'])==0)
        {
        return true;
        }

		if (!isset($this->_datas['team']) || count($this->_datas['team'])==0)
        {
        return true;
        }
		if ((!isset($this->_newteams) || count($this->_newteams)==0) &&
			(!isset($this->_dbteamsid) || count($this->_dbteamsid)==0))
            {
            return true;
            }

		foreach ($this->_datas['match'] as $key => $match)
		{
//$this->dump_variable("match", $match);
			
            $mdl = JModelLegacy::getInstance("match", "sportsmanagementModel");
            $p_match = $mdl->getTable();
            
			$oldId=(int)$match->id;
			if ($this->import_version=='NEW')
			{
				$p_match->set('round_id',$this->_convertRoundID[$this->_getDataFromObject($match,'round_id')]);
				$p_match->set('match_number',$this->_getDataFromObject($match,'match_number'));

				if ($match->projectteam1_id > 0)
				{
				    if ( isset($this->_convertProjectTeamID[intval($this->_getDataFromObject($match,'projectteam1_id'))]) )
                    {
					$team1 = $this->_convertProjectTeamID[intval($this->_getDataFromObject($match,'projectteam1_id'))];
                    }
                    else
                    {
                    $team1 = 0;    
                    }
				}
				else
				{
					$team1 = 0;
				}
				$p_match->set('projectteam1_id',$team1);

				if ($match->projectteam2_id > 0)
				{
				    if ( isset($this->_convertProjectTeamID[intval($this->_getDataFromObject($match,'projectteam2_id'))]) )
                    {
					$team2 = $this->_convertProjectTeamID[intval($this->_getDataFromObject($match,'projectteam2_id'))];
                    }
                    else
                    {
                    $team2 = 0;    
                    }
				}
				else
				{
					$team2 = 0;
				}
				$p_match->set('projectteam2_id',$team2);

				if (!empty($this->_convertPlaygroundID))
				{
					if (array_key_exists((int)$this->_getDataFromObject($match,'playground_id'),$this->_convertPlaygroundID))
					{
						$p_match->set('playground_id',$this->_convertPlaygroundID[$this->_getDataFromObject($match,'playground_id')]);
					}
					else
					{
						$p_match->set('playground_id',0);
					}
				}
				if ($p_match->playground_id ==0)
				{
					$p_match->set('playground_id',NULL);
				}

				$p_match->set('match_date',$this->_getDataFromObject($match,'match_date'));

/**
 * hier muss noch der timestamp übergeben werden, da sonst
 * die spiele nicht angezeigt weden. 
 */                
				$p_match->set('match_timestamp', sportsmanagementHelper::getTimestamp($this->_getDataFromObject($match,'match_date')));
                $p_match->set('time_present',$this->_getDataFromObject($match,'time_present'));

				$team1_result=$this->_getDataFromObject($match,'team1_result');
				if (isset($team1_result) && ($team1_result !=NULL)) { $p_match->set('team1_result',$team1_result); }

				$team2_result=$this->_getDataFromObject($match,'team2_result');
				if (isset($team2_result) && ($team2_result !=NULL)) { $p_match->set('team2_result',$team2_result); }

				$team1_bonus=$this->_getDataFromObject($match,'team1_bonus');
				if (isset($team1_bonus) && ($team1_bonus !=NULL)) { $p_match->set('team1_bonus',$team1_bonus); }

				$team2_bonus=$this->_getDataFromObject($match,'team2_bonus');
				if (isset($team2_bonus) && ($team2_bonus !=NULL)) { $p_match->set('team2_bonus',$team2_bonus); }

				$team1_legs=$this->_getDataFromObject($match,'team1_legs');
				if (isset($team1_legs) && ($team1_legs !=NULL)) { $p_match->set('team1_legs',$team1_legs); }

				$team2_legs=$this->_getDataFromObject($match,'team2_legs');
				if (isset($team2_legs) && ($team2_legs !=NULL)) { $p_match->set('team2_legs',$team2_legs); }

				$p_match->set('team1_result_split',$this->_getDataFromObject($match,'team1_result_split'));
				$p_match->set('team2_result_split',$this->_getDataFromObject($match,'team2_result_split'));
				$p_match->set('match_result_type',$this->_getDataFromObject($match,'match_result_type'));

				$team1_result_ot=$this->_getDataFromObject($match,'team1_result_ot');
				if (isset($team1_result_ot) && ($team1_result_ot !=NULL)) { $p_match->set('team1_result_ot',$team1_result_ot); }

				$team2_result_ot=$this->_getDataFromObject($match,'team2_result_ot');
				if (isset($team2_result_ot) && ($team2_result_ot !=NULL)) { $p_match->set('team2_result_ot',$team2_result_ot); }

				$team1_result_so=$this->_getDataFromObject($match,'team1_result_so');
				if (isset($team1_result_so) && ($team1_result_so !=NULL)) { $p_match->set('team1_result_so',$team1_result_so); }

				$team2_result_so=$this->_getDataFromObject($match,'team2_result_so');
				if (isset($team2_result_so) && ($team2_result_so !=NULL)) { $p_match->set('team2_result_so',$team2_result_so); }

				$p_match->set('alt_decision',$this->_getDataFromObject($match,'alt_decision'));

				$team1_result_decision=$this->_getDataFromObject($match,'team1_result_decision');
				if (isset($team1_result_decision) && ($team1_result_decision !=NULL)) { $p_match->set('team1_result_decision',$team1_result_decision); }

				$team2_result_decision=$this->_getDataFromObject($match,'team2_result_decision');
				if (isset($team2_result_decision) && ($team2_result_decision !=NULL)) { $p_match->set('team2_result_decision',$team2_result_decision); }

				$p_match->set('decision_info',$this->_getDataFromObject($match,'decision_info'));
				$p_match->set('cancel',$this->_getDataFromObject($match,'cancel'));
				$p_match->set('cancel_reason',$this->_getDataFromObject($match,'cancel_reason'));
				$p_match->set('count_result',$this->_getDataFromObject($match,'count_result'));
				$p_match->set('crowd',$this->_getDataFromObject($match,'crowd'));
				$p_match->set('summary',$this->_getDataFromObject($match,'summary'));
				$p_match->set('show_report',$this->_getDataFromObject($match,'show_report'));
				$p_match->set('preview',$this->_getDataFromObject($match,'preview'));
				$p_match->set('match_result_detail',$this->_getDataFromObject($match,'match_result_detail'));
				$p_match->set('new_match_id',$this->_getDataFromObject($match,'new_match_id'));
				$p_match->set('old_match_id',$this->_getDataFromObject($match,'old_match_id'));
				$p_match->set('extended',$this->_getDataFromObject($match,'extended'));
				$p_match->set('published',$this->_getDataFromObject($match,'published'));
                
/**
 * diddipoeler
 */
                $p_match->set('import_match_id',$this->_getDataFromObject($match,'id'));
                
                if ( isset($this->_convertDivisionID) )
                {
                $p_match->set('division_id',$this->_convertDivisionID[$this->_getDataFromObject($match,'division_id')]);
                }
                
                
			}
			else // ($this->import_version=='OLD')
			{
				$p_match->set('round_id',$this->_convertRoundID[intval($match->round_id)]);
				$p_match->set('match_number',$this->_getDataFromObject($match,'match_number'));
                
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__. ' matchparts -> '.$match->matchpart1.' - '.$match->matchpart2),'');

				if ($match->matchpart1 > 0)
				{
					//$team1 = $this->_convertTeamID[intval($match->matchpart1)];
					//$p_match->set('projectteam1_id',$this->_convertProjectTeamID[$team1]);
                    $p_match->set('projectteam1_id',$this->_convertProjectTeamID[intval($match->matchpart1)]);
				}
				else
				{
					$p_match->set('projectteam1_id',0);
				}

				if ($match->matchpart2 > 0)
				{
					//$team2 = $this->_convertTeamID[intval($match->matchpart2)];
					//$p_match->set('projectteam2_id',$this->_convertProjectTeamID[$team2]);
                    $p_match->set('projectteam2_id',$this->_convertProjectTeamID[intval($match->matchpart2)]);
				}
				else
				{
					$p_match->set('projectteam2_id',0);
				}
                
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__. ' teams -> '.$team1.' - '.$team2),'');

				$matchdate=(string)$match->match_date;
				$p_match->set('match_date',$matchdate);

				$team1_result=$this->_getDataFromObject($match,'matchpart1_result');
				if (isset($team1_result) && ($team1_result !=NULL)) { $p_match->set('team1_result',$team1_result); }

				$team2_result=$this->_getDataFromObject($match,'matchpart2_result');
				if (isset($team2_result) && ($team2_result !=NULL)) { $p_match->set('team2_result',$team2_result); }

				$team1_bonus=$this->_getDataFromObject($match,'matchpart1_bonus');
				if (isset($team1_bonus) && ($team1_bonus !=NULL)) { $p_match->set('team1_bonus',$team1_bonus); }

				$team2_bonus=$this->_getDataFromObject($match,'matchpart2_bonus');
				if (isset($team2_bonus) && ($team2_bonus !=NULL)) { $p_match->set('team2_bonus',$team2_bonus); }

				$team1_legs=$this->_getDataFromObject($match,'matchpart1_legs');
				if (isset($team1_legs) && ($team1_legs !=NULL)) { $p_match->set('team1_legs',$team1_legs); }

				$team2_legs=$this->_getDataFromObject($match,'matchpart2_legs');
				if (isset($team2_legs) && ($team2_legs !=NULL)) { $p_match->set('team2_legs',$team2_legs); }

				$p_match->set('team1_result_split',$this->_getDataFromObject($match,'matchpart1_result_split'));//NULL
				$p_match->set('team2_result_split',$this->_getDataFromObject($match,'matchpart2_result_split'));//NULL
				$p_match->set('match_result_type',$this->_getDataFromObject($match,'match_result_type'));

				$team1_result_ot=$this->_getDataFromObject($match,'matchpart1_result_ot');
				if (isset($team1_result_ot) && ($team1_result_ot !=NULL)) { $p_match->set('team1_result_ot',$team1_result_ot); }

				$team2_result_ot=$this->_getDataFromObject($match,'matchpart2_result_ot');
				if (isset($team2_result_ot) && ($team2_result_ot !=NULL)) { $p_match->set('team2_result_ot',$team2_result_ot); }

				$p_match->set('alt_decision',$this->_getDataFromObject($match,'alt_decision'));

				$team1_result_decision=$this->_getDataFromObject($match,'matchpart1_result_decision');
				if (isset($team1_result_decision) && ($team1_result_decision !=NULL)) { $p_match->set('team1_result_decision',$team1_result_decision); }

				$team2_result_decision=$this->_getDataFromObject($match,'matchpart2_result_decision');
				if (isset($team2_result_decision) && ($team2_result_decision !=NULL)) { $p_match->set('team2_result_decision',$team2_result_decision); }

				$p_match->set('decision_info',$this->_getDataFromObject($match,'decision_info'));
				$p_match->set('count_result',$this->_getDataFromObject($match,'count_result'));
				$p_match->set('crowd',$this->_getDataFromObject($match,'crowd'));
				$p_match->set('summary',$this->_getDataFromObject($match,'summary'));
				$p_match->set('show_report',$this->_getDataFromObject($match,'show_report'));
				$p_match->set('match_result_detail',$this->_getDataFromObject($match,'match_result_detail'));
				$p_match->set('published',$this->_getDataFromObject($match,'published'));
                
                // diddipoeler
                $p_match->set('import_match_id',$this->_getDataFromObject($match,'id'));
			}

			if ($p_match->store()===false)
			{
				$my_text .= 'error on match import: ';
				$my_text .= $oldID;
				//$my_text .= "<br />Error: _importMatches<br />#$my_text#<br />#<pre>".print_r($p_match,true).'</pre>#';
				$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
				//return false;
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
			}
			else
			{
				if ($this->import_version=='NEW')
				{
					if ($match->projectteam1_id > 0)
					{
						$teamname1 = $this->_getTeamName($p_match->projectteam1_id);
					}
					else
					{
						$teamname1 = '<span style="color:orange">'.JText::_('Home-Team not asigned').'</span>';
					}
					if ($match->projectteam2_id > 0)
					{
						$teamname2 = $this->_getTeamName($p_match->projectteam2_id);
					}
					else
					{
						$teamname2='<span style="color:orange">'.JText::_('Guest-Team not asigned').'</span>';
					}

					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf(	'Added to round: %1$s / Match: %2$s - %3$s / ProjectTeamID Old: %4$s - %5$s / ProjectTeamID New: %6$s - %7$s',
									'</span><strong>'.$this->_getRoundName($this->_convertRoundID[$this->_getDataFromObject($match,'round_id')]).'</strong><span style="color:'.$this->storeSuccessColor.'">',
									"</span><strong>$teamname1</strong>",
                                    "<strong>$teamname2</strong>",
									"<strong>$match->projectteam1_id</strong>",
                                    "<strong>$match->projectteam2_id</strong>",
                                    "<strong>$team1</strong>",
                                    "<strong>$team2</strong>"
                                    );
					$my_text .= '<br />';
				}

				if ($this->import_version == 'OLD')
				{
					if ($match->matchpart1 > 0)
					{
						$teamname1 = $this->_getTeamName2($this->_convertTeamID[intval($match->matchpart1)]);
					}
					else
					{
						$teamname1 = '<span style="color:orange">'.JText::_('Home-Team not asigned').'</span>';
					}
					if ($match->matchpart2 > 0)
					{
						$teamname2 = $this->_getTeamName2($this->_convertTeamID[intval($match->matchpart2)]);
					}
					else
					{
						$teamname2 = '<span style="color:orange">'.JText::_('Guest-Team not asigned').'</span>';
					}

					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf(	'Added to round: %1$s / Match: %2$s - %3$s',
									'</span><strong>'.$this->_getRoundName($this->_convertRoundID[$this->_getDataFromObject($match,'round_id')]).'</strong><span style="color:'.$this->storeSuccessColor.'">',
									"</span><strong>$teamname1</strong>",
									"<strong>$teamname2</strong>");
					$my_text .= '<br />';
				}
			}
//$this->dump_variable("p_match", $p_match);

			$insertID=$p_match->id;//JFactory::getDbo()->insertid();
			$this->_convertMatchID[$oldId]=$insertID;
		}
//$this->dump_variable("this->_convertMatchID", $this->_convertMatchID);
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importMatchPlayer()
	 * 
	 * @return
	 */
	private function _importMatchPlayer()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
       
//$this->dump_header("function _importMatchPlayer");
		$my_text='';
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

//$this->dump_variable("this->_convertTeamPlayerID", $this->_convertTeamPlayerID);
//$this->dump_variable("this->_convertProjectPositionID", $this->_convertProjectPositionID);
		foreach ($this->_datas['matchplayer'] as $key => $matchplayer)
		{
			$import_matchplayer = $this->_datas['matchplayer'][$key];
//$this->dump_variable("import_matchplayer", $import_matchplayer);
			$oldID = $this->_getDataFromObject($import_matchplayer,'id');
			
            $mdl = JModelLegacy::getInstance("matchplayer", "sportsmanagementModel");
            $p_matchplayer = $mdl->getTable();
            
			$oldMatchID = $this->_getDataFromObject($import_matchplayer,'match_id');
			$oldTeamPlayerID = $this->_getDataFromObject($import_matchplayer,'teamplayer_id');
			if (!isset($this->_convertMatchID[$oldMatchID]) ||
				!isset($this->_convertTeamPlayerID[$oldTeamPlayerID]))
			{
				$my_text .= '<span style="color:red">';
				$my_text .= JText::sprintf(	'Skipping import of MatchPlayer-ID [%1$s]. Old-MatchID: [%2$s] - Old-TeamPlayerID: [%3$s]',
								"</span><strong>$oldID</strong><span style='color:red'>",
								"</span><strong>$oldMatchID</strong><span style='color:red'>",
								"</span><strong>$oldTeamPlayerID</strong>").'<br />';
				continue;
			}
			$p_matchplayer->set('match_id',$this->_convertMatchID[$oldMatchID]);
			$p_matchplayer->set('teamplayer_id',$this->_convertTeamPlayerID[$oldTeamPlayerID]);
            $newTeamPlayerID = $this->_convertTeamPlayerID[$oldTeamPlayerID];
            
			$oldPositionID = $this->_getDataFromObject($import_matchplayer,'project_position_id');
			if (isset($this->_convertProjectPositionID[$oldPositionID]))
			{
				$p_matchplayer->set('project_position_id',$this->_convertProjectPositionID[$oldPositionID]);
			}
			$p_matchplayer->set('came_in',$this->_getDataFromObject($import_matchplayer,'came_in'));
			if ($import_matchplayer->in_for > 0)
			{
				$oldTeamPlayerID = $this->_getDataFromObject($import_matchplayer,'in_for');
				if (isset($this->_convertTeamPlayerID[$oldTeamPlayerID]))
				{
					$p_matchplayer->set('in_for',$this->_convertTeamPlayerID[$oldTeamPlayerID]);
				}
			}
			$p_matchplayer->set('out',$this->_getDataFromObject($import_matchplayer,'out'));
			$p_matchplayer->set('in_out_time',$this->_getDataFromObject($import_matchplayer,'in_out_time'));
			$p_matchplayer->set('ordering',$this->_getDataFromObject($import_matchplayer,'ordering'));

			if ($p_matchplayer->store()===false)
			{
				$my_text .= 'error on matchplayer import: ';
				$my_text .= $oldID;
				//$my_text .= "<br />Error: _importMatchPlayer<br />#$my_text#<br />#<pre>".print_r($p_matchplayer,true).'</pre>#';
				$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
				//return false;
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
			}
			else
			{
				$dPerson = $this->_getPersonFromTeamPlayer($p_matchplayer->teamplayer_id);
				$dPosName = (($p_matchplayer->project_position_id==0) ?
							'<span style="color:orange">'.JText::_('Has no position').'</span>' :
							$this->_getProjectPositionName($p_matchplayer->project_position_id));
				$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
				$my_text .= JText::sprintf(	'Created new matchplayer data. MatchID: %1$s - Player: %2$s,%3$s - Position: %4$s - oldTeamPlayerID : %5$s - newTeamPlayerID : %6$s',
								'</span><strong>'.$p_matchplayer->match_id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$dPerson->lastname,
								$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								"</span><strong>$dPosName</strong>",
                                "</span><strong>$oldTeamPlayerID</strong>",
                                "</span><strong>$newTeamPlayerID</strong>");
				$my_text .= '<br />';
			}
//$this->dump_variable("p_matchplayer", $p_matchplayer);
		}
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importMatchStaff()
	 * 
	 * @return
	 */
	private function _importMatchStaff()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
//$this->dump_header("function _importMatchStaff");
		$my_text='';
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

//$this->dump_variable("this->_convertMatchID", $this->_convertMatchID);
//$this->dump_variable("this->_convertTeamStaffID", $this->_convertTeamStaffID);
//$this->dump_variable("this->_convertProjectPositionID", $this->_convertProjectPositionID);
		foreach ($this->_datas['matchstaff'] as $key => $matchstaff)
		{
			$import_matchstaff=$this->_datas['matchstaff'][$key];
//$this->dump_variable("import_matchstaff", $import_matchstaff);
			$oldID=$this->_getDataFromObject($import_matchstaff,'id');
			
            $mdl = JModelLegacy::getInstance("matchstaff", "sportsmanagementModel");
            $p_matchstaff = $mdl->getTable();
            
			$oldMatchID=$this->_getDataFromObject($import_matchstaff,'match_id');
			$oldTeamStaffID=$this->_getDataFromObject($import_matchstaff,'team_staff_id');
			if (!isset($this->_convertMatchID[$oldMatchID]) ||
				!isset($this->_convertTeamStaffID[$oldTeamStaffID]))
			{
				$my_text .= '<span style="color:red">';
				$my_text .= JText::sprintf(	'Skipping import of MatchStaff-ID [%1$s]. Old-MatchID: [%2$s] - Old-StaffID: [%3$s]',
								"</span><strong>$oldID</strong><span style='color:red'>",
								"</span><strong>$oldMatchID</strong><span style='color:red'>",
								"</span><strong>$oldTeamStaffID</strong>").'<br />';
				continue;
			}
			$p_matchstaff->set('match_id',$this->_convertMatchID[$oldMatchID]);
			$p_matchstaff->set('team_staff_id',$this->_convertTeamStaffID[$oldTeamStaffID]);
			$oldPositionID=$this->_getDataFromObject($import_matchstaff,'project_position_id');
			if (isset($this->_convertProjectPositionID[$oldPositionID]))
			{
				$p_matchstaff->set('project_position_id',$this->_convertProjectPositionID[$oldPositionID]);
			}
			$p_matchstaff->set('ordering',$this->_getDataFromObject($import_matchstaff,'ordering'));
			if ($p_matchstaff->store()===false)
			{
				$my_text .= 'error on matchstaff import: ';
				$my_text .= $oldID;
				//$my_text .= "<br />Error: _importMatchStaff<br />#$my_text#<br />#<pre>".print_r($p_matchstaff,true).'</pre>#';
				$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
				//return false;
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
			}
			else
			{
				$dPerson=$this->_getPersonFromTeamStaff($p_matchstaff->team_staff_id);
				$dPosName=(($p_matchstaff->project_position_id==0) ?
							'<span style="color:orange">'.JText::_('Has no position').'</span>' :
							$this->_getProjectPositionName($p_matchstaff->project_position_id));
				$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
				$my_text .= JText::sprintf(	'Created new matchstaff data. MatchID: %1$s - Staff: %2$s,%3$s - Position: %4$s',
								'</span><strong>'.$p_matchstaff->match_id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$dPerson->lastname,
								$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								"</span><strong>$dPosName</strong>");
				$my_text .= '<br />';
			}
//$this->dump_variable("p_matchstaff", $p_matchstaff);
		}
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importMatchReferee()
	 * 
	 * @return
	 */
	private function _importMatchReferee()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
//$this->dump_header("function _importMatchStaff");
		$my_text='';
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

//$this->dump_variable("this->_convertMatchID", $this->_convertMatchID);
//$this->dump_variable("this->_convertProjectRefereeID", $this->_convertProjectRefereeID);
//$this->dump_variable("this->_convertProjectPositionID", $this->_convertProjectPositionID);
		foreach ($this->_datas['matchreferee'] as $key => $matchreferee)
		{
			$import_matchreferee=$this->_datas['matchreferee'][$key];
//$this->dump_variable("import_matchreferee", $import_matchreferee);
			$oldID=$this->_getDataFromObject($import_matchreferee,'id');
			
            $mdl = JModelLegacy::getInstance("matchreferee", "sportsmanagementModel");
            $p_matchreferee = $mdl->getTable();
            
			$oldMatchID=$this->_getDataFromObject($import_matchreferee,'match_id');
			$oldProjectRefereeID=$this->_getDataFromObject($import_matchreferee,'project_referee_id');
			if (!isset($this->_convertMatchID[$oldMatchID]) ||
				!isset($this->_convertProjectRefereeID[$oldProjectRefereeID]))
			{
				$my_text .= '<span style="color:red">';
				$my_text .= JText::sprintf(	'Skipping import of MatchReferee-ID [%1$s]. Old-MatchID: [%2$s] - Old-RefereeID: [%3$s]',
								"</span><strong>$oldID</strong><span style='color:red'>",
								"</span><strong>$oldMatchID</strong><span style='color:red'>",
								"</span><strong>$oldProjectRefereeID</strong>").'<br />';
				continue;
			}
			$p_matchreferee->set('match_id',$this->_convertMatchID[$oldMatchID]);
			$p_matchreferee->set('project_referee_id',$this->_convertProjectRefereeID[$oldProjectRefereeID]);
			$oldPositionID=$this->_getDataFromObject($import_matchreferee,'project_position_id');
			if (isset($this->_convertProjectPositionID[$oldPositionID]))
			{
				$p_matchreferee->set('project_position_id',$this->_convertProjectPositionID[$oldPositionID]);
			}
			$p_matchreferee->set('ordering',$this->_getDataFromObject($import_matchreferee,'ordering'));
			if ($p_matchreferee->store()===false)
			{
				$my_text .= 'error on matchreferee import: ';
				$my_text .= $oldID;
				//$my_text .= "<br />Error: _importMatchReferee<br />#$my_text#<br />#<pre>".print_r($p_matchreferee,true).'</pre>#';
				$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
				//return false;
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
			}
			else
			{
				$dPerson=$this->_getPersonFromProjectReferee($p_matchreferee->project_referee_id);
				$dPosName=(($p_matchreferee->project_position_id==0) ?
							'<span style="color:orange">'.JText::_('Has no position').'</span>' :
							$this->_getProjectPositionName($p_matchreferee->project_position_id));
				$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
				$my_text .= JText::sprintf(	'Created new matchreferee data. MatchID: %1$s - Referee: %2$s,%3$s - Position: %4$s',
								'</span><strong>'.$p_matchreferee->match_id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$dPerson->lastname,
								$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								"</span><strong>$dPosName</strong>");
				$my_text .= '<br />';
			}
//$this->dump_variable("p_matchreferee", $p_matchreferee);
		}
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importMatchEvent()
	 * 
	 * @return
	 */
	private function _importMatchEvent()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
		$my_text='';
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

			
            $mdl = JModelLegacy::getInstance("matchevent", "sportsmanagementModel");
            $p_matchevent = $mdl->getTable();

			$p_matchevent->set('match_id',$this->_convertMatchID[$this->_getDataFromObject($import_matchevent,'match_id')]);
			$p_matchevent->set('projectteam_id',$this->_convertProjectTeamID[$this->_getDataFromObject($import_matchevent,'projectteam_id')]);
			if ($import_matchevent->teamplayer_id > 0)
			{
				$p_matchevent->set('teamplayer_id',$this->_convertTeamPlayerID[$this->_getDataFromObject($import_matchevent,'teamplayer_id')]);
			}
			else
			{
				$p_matchevent->set('teamplayer_id',0);
			}
			if ($import_matchevent->teamplayer_id2 > 0)
			{
				$p_matchevent->set('teamplayer_id2',$this->_convertTeamPlayerID[$this->_getDataFromObject($import_matchevent,'teamplayer_id2')]);
			}
			else
			{
				$p_matchevent->set('teamplayer_id2',0);
			}
			$p_matchevent->set('event_time',$this->_getDataFromObject($import_matchevent,'event_time'));
			$p_matchevent->set('event_type_id',$this->_convertEventID[$this->_getDataFromObject($import_matchevent,'event_type_id')]);
			$p_matchevent->set('event_sum',$this->_getDataFromObject($import_matchevent,'event_sum'));
			$p_matchevent->set('notice',$this->_getDataFromObject($import_matchevent,'notice'));
			$p_matchevent->set('notes',$this->_getDataFromObject($import_matchevent,'notes'));

			if ($p_matchevent->store()===false)
			{
				$my_text .= 'error on matchevent import: ';
				$my_text .= $oldID;
				//$my_text .= "<br />Error: _importMatchEvent<br />#$my_text#<br />#<pre>".print_r($p_matchevent,true).'</pre>#';
				$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
				//return false;
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
			}
			else
			{
				$dPerson=$this->_getPersonFromTeamPlayer($p_matchevent->teamplayer_id);
				$dEventName=(($p_matchevent->event_type_id==0) ?
							'<span style="color:orange">'.JText::_('Has no event').'</span>' :
							JText::_($this->_getObjectName('eventtype',$p_matchevent->event_type_id)));
				$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
				$my_text .= JText::sprintf(	'Created new matchevent data. MatchID: %1$s - Player: %2$s,%3$s - Eventtime: %4$s - Event: %5$s',
								'</span><strong>'.$p_matchevent->match_id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$dPerson->lastname,
								$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$p_matchevent->event_time.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								"</span><strong>$dEventName</strong>");
				$my_text .= '<br />';
			}
		}
		$this->_success_text[JText::_('COM_SPORTSMANAGEMENT_XML'.strtoupper(__FUNCTION__).'_0')]=$my_text;
		return true;
	}

	/**
	 * sportsmanagementModelJLXMLImport::_importPositionStatistic()
	 * 
	 * @return
	 */
	private function _importPositionStatistic()
	{
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
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
			$my_text .= JText::sprintf('Warning: Skipped %1$s records for position statistic data because there is no statistic data included!',count($this->_datas['positionstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing position statistic data:']=$my_text;
			return true;
		}

		if (!isset($this->_datas['position']) || count($this->_datas['position'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= JText::sprintf('Warning: Skipped %1$s records for position statistic data because there is no position data included!',count($this->_datas['positionstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing position statistic data:']=$my_text;
			return true;
		}

		foreach ($this->_datas['positionstatistic'] as $key => $positionstatistic)
		{
			$import_positionstatistic=$this->_datas['positionstatistic'][$key];
			$oldID=$this->_getDataFromObject($import_positionstatistic,'id');

			
            $mdl = JModelLegacy::getInstance("positionstatistic", "sportsmanagementModel");
            $p_positionstatistic = $mdl->getTable();

			$p_positionstatistic->set('position_id',$this->_convertPositionID[$this->_getDataFromObject($import_positionstatistic,'position_id')]);
			$p_positionstatistic->set('statistic_id',$this->_convertStatisticID[$this->_getDataFromObject($import_positionstatistic,'statistic_id')]);
			//$p_positionstatistic->set('ordering',$this->_getDataFromObject($import_positionstatistic,'ordering'));

$query->clear();
			// Select some fields
        $query->select('id');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_position_statistic');
        $query->where('position_id = '.(int)$p_positionstatistic->position_id);
        $query->where('statistic_id = '.(int)$p_positionstatistic->statistic_id);

			JFactory::getDbo()->setQuery($query); 
            sportsmanagementModeldatabasetool::runJoomlaQuery();
			if ($object=JFactory::getDbo()->loadObject())
			{
				$my_text .= '<span style="color:'.$this->existingInDbColor.'">';
				$my_text .= JText::sprintf(	'Using existing positionstatistic data. Position: %1$s - Statistic: %2$s',
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
					//$my_text .= "<br />Error: _importPositionStatistic<br />#$my_text#<br />#<pre>".print_r($p_positionstatistic,true).'</pre>#';
					$this->_success_text['Importing position statistic data:']=$my_text;
					//return false;
                    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
				}
				else
				{
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf(	'Created new position statistic data. Position: %1$s - Statistic: %2$s',
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
	   $app = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
		$my_text='';
		if (!isset($this->_datas['matchstaffstatistic']) || count($this->_datas['matchstaffstatistic'])==0){return true;}

		if ((!isset($this->_newstatisticsid) || count($this->_newstatisticsid)==0) &&
			(!isset($this->_dbstatisticsid) || count($this->_dbstatisticsid)==0)){return true;}

		if (!isset($this->_datas['statistic']) || count($this->_datas['statistic'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= JText::sprintf('Warning: Skipped %1$s records for match staff statistic data because there is no statistic data included!',count($this->_datas['matchstaffstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match staff statistic data:']=$my_text;
			return true;
		}
		if (!isset($this->_datas['match']) || count($this->_datas['match'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= JText::sprintf('Warning: Skipped %1$s records for match statistic data because there is no match data included!',count($this->_datas['matchstaffstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match staff statistic data:']=$my_text;
			return true;
		}
		if (!isset($this->_datas['projectteam']) || count($this->_datas['projectteam'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= JText::sprintf('Warning: Skipped %1$s records for match statistic data because there is no projectteam data included!',count($this->_datas['matchstaffstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match staff statistic data:']=$my_text;
			return true;
		}
		if (!isset($this->_datas['teamstaff']) || count($this->_datas['teamstaff'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= JText::sprintf('Warning: Skipped %1$s records for match statistic data because there is no teamstaff data included!',count($this->_datas['matchstaffstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match staff statistic data:']=$my_text;
			return true;
		}
		foreach ($this->_datas['matchstaffstatistic'] as $key => $matchstaffstatistic)
		{
			$import_matchstaffstatistic=$this->_datas['matchstaffstatistic'][$key];
			$oldID=$this->_getDataFromObject($import_matchstaffstatistic,'id');

			
            $mdl = JModelLegacy::getInstance("matchstaffstatistic", "sportsmanagementModel");
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
				//$my_text .= "<br />Error: _importMatchStaffStatistic<br />#$my_text#<br />#<pre>".print_r($p_matchstaffstatistic,true).'</pre>#';
				$this->_success_text['Importing match staff statistic data:']=$my_text;
				//return false;
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
			}
			else
			{
				$dPerson=$this->_getPersonFromTeamStaff($p_matchstaffstatistic->team_staff_id);
				$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
				$my_text .= JText::sprintf(	'Created new match staff statistic data. StatisticID: %1$s - MatchID: %2$s - Player: %3$s,%4$s - Team: %5$s - Value: %6$s',
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

	private function _importMatchStatistic()
	{
//$this->dump_header("function _importMatchStatistic");
		$my_text='';
		if (!isset($this->_datas['matchstatistic']) || count($this->_datas['matchstatistic'])==0){return true;}

		if ((!isset($this->_newstatisticsid) || count($this->_newstatisticsid)==0) &&
			(!isset($this->_dbstatisticsid) || count($this->_dbstatisticsid)==0)){return true;}

		if (!isset($this->_datas['statistic']) || count($this->_datas['statistic'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= JText::sprintf('Warning: Skipped %1$s records for match statistic data because there is no statistic data included!',count($this->_datas['matchstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match statistic data:']=$my_text;
			return true;
		}
		if (!isset($this->_datas['match']) || count($this->_datas['match'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= JText::sprintf('Warning: Skipped %1$s records for match statistic data because there is no match data included!',count($this->_datas['matchstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match statistic data:']=$my_text;
			return true;
		}
		if (!isset($this->_datas['projectteam']) || count($this->_datas['projectteam'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= JText::sprintf('Warning: Skipped %1$s records for match statistic data because there is no projectteam data included!',count($this->_datas['matchstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match statistic data:']=$my_text;
			return true;
		}
		if (!isset($this->_datas['teamplayer']) || count($this->_datas['teamplayer'])==0)
		{
			$my_text .= '<span style="color:red">';
			$my_text .= JText::sprintf('Warning: Skipped %1$s records for match statistic data because there is no teamplayer data included!',count($this->_datas['matchstatistic']));
			$my_text .= '</span>';
			$this->_success_text['Importing match statistic data:']=$my_text;
			return true;
		}
		foreach ($this->_datas['matchstatistic'] as $key => $matchstatistic)
		{
			$import_matchstatistic=$this->_datas['matchstatistic'][$key];
//$this->dump_variable("import_matchstatistic", $import_matchstatistic);
			$oldID=$this->_getDataFromObject($import_matchstatistic,'id');

			
            $mdl = JModelLegacy::getInstance("matchstatistic", "sportsmanagementModel");
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
				//$my_text .= "<br />Error: _importMatchStatistic<br />#$my_text#<br />#<pre>".print_r($p_matchstatistic,true).'</pre>#';
				$this->_success_text['Importing match statistic data:']=$my_text;
				//return false;
                sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
			}
			else
			{
				$dPerson=$this->_getPersonFromTeamPlayer($p_matchstatistic->teamplayer_id);
				$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
				$my_text .= JText::sprintf(	'Created new match statistic data. StatisticID: %1$s - MatchID: %2$s - Player: %3$s,%4$s - Team: %5$s - Value: %6$s',
								'</span><strong>'.$p_matchstatistic->statistic_id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$p_matchstatistic->match_id.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$dPerson->lastname,
								$dPerson->firstname.'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$this->_getTeamName($p_matchstatistic->projectteam_id).'</strong><span style="color:'.$this->storeSuccessColor.'">',
								'</span><strong>'.$p_matchstatistic->value.'</strong>');
				$my_text .= '<br />';
			}
//$this->dump_variable("p_matchstatistic", $p_matchstatistic);
		}
		$this->_success_text['Importing match statistic data:']=$my_text;
		return true;
	}

	private function _importTreetos()
	{
		$my_text='';
		if (!isset($this->_datas['treeto']) || count($this->_datas['treeto'])==0){return true;}
		if (isset($this->_datas['treeto']))
		{
			foreach ($this->_datas['treeto'] as $key => $treeto)
			{
				
                $mdl = JModelLegacy::getInstance("treeto", "sportsmanagementModel");
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
					//$my_text .= "<br />Error: _importTreetos<br />#$my_text#<br />#<pre>".print_r($p_treeto,true).'</pre>#';
					$this->_success_text['Importing treeto data:']=$my_text;
					//return false;
                    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
				}
				else
				{
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf('Created new treeto data: %1$s','</span><strong>'.$p_treeto->name.'</strong>');
					$my_text .= '<br />';
				}
				$insertID=JFactory::getDbo()->insertid();
				$this->_convertTreetoID[$oldId]=$insertID;
			}
			$this->_success_text['Importing treeto data:']=$my_text;
			return true;
		}
	}

	private function _importTreetonode()
	{
		$my_text='';
		if (!isset($this->_datas['treetonode']) || count($this->_datas['treetonode'])==0){return true;}
		if (isset($this->_datas['treetonode']))
		{
			foreach ($this->_datas['treetonode'] as $key => $treetonode)
			{
				
                $mdl = JModelLegacy::getInstance("treetonode", "sportsmanagementModel");
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
					//$my_text .= "<br />Error: _importTreetonode<br />#$my_text#<br />#<pre>".print_r($p_treetonode,true).'</pre>#';
					$this->_success_text['Importing treetonode data:']=$my_text;
					//return false;
                    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
				}
				else
				{
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf('Created new treetonode data: %1$s','</span><strong>'.$p_treetonode->id.'</strong>');
					$my_text .= '<br />';
				}
				$insertID=JFactory::getDbo()->insertid();
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
				
                $mdl = JModelLegacy::getInstance("treetomatch", "sportsmanagementModel");
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
					//$my_text .= "<br />Error: _importTreetomatch<br />#$my_text#<br />#<pre>".print_r($p_treetomatch,true).'</pre>#';
					$this->_success_text['Importing treetomatch data:']=$my_text;
					//return false;
                    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
				}
				else
				{
					$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
					$my_text .= JText::sprintf('Created new treetomatch data: %1$s','</span><strong>'.$p_treetomatch->id.'</strong>');
					$my_text .= '<br />';
				}
				$insertID=JFactory::getDbo()->insertid();
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
		JFactory::getDbo()->setQuery($query);
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
	   $app = JFactory::getApplication();
//       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post <br><pre>'.print_r($post,true).'</pre>'),'');
       
		$option = JFactory::getApplication()->input->getCmd('option');
        $this->show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        $this->_datas=$this->getData();

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
/*
		//tracking of old -> new ids
		// The 0 entry is needed to translate an input with ID 0 to an output with ID 0;
		// this can happen when the exported file contains a field with ID equal to 0

		$standard_translation = array(0 => 0);
		$this->_convertProjectTeamID=$standard_translation;
		$this->_convertProjectRefereeID=$standard_translation;
		$this->_convertTeamPlayerID=$standard_translation;
		$this->_convertTeamStaffID=$standard_translation;
		$this->_convertProjectPositionID=$standard_translation;
		$this->_convertClubID=$standard_translation;
		$this->_convertPersonID=$standard_translation;
		$this->_convertTeamID=$standard_translation;
		$this->_convertRoundID=$standard_translation;
		$this->_convertDivisionID=$standard_translation;
		$this->_convertCountryID=$standard_translation;
		$this->_convertPlaygroundID=$standard_translation;
		$this->_convertEventID=$standard_translation;
		$this->_convertPositionID=$standard_translation;
		$this->_convertParentPositionID=$standard_translation;
		$this->_convertMatchID=$standard_translation;
		$this->_convertStatisticID=$standard_translation;
		$this->_convertTreetoID=$standard_translation;
		$this->_convertTreetonodeID=$standard_translation;
		$this->_convertTreetomatchID=$standard_translation;
*/

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
					JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Missing projectname'));
					echo "<script> alert('".JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Missing projectname')."'); window.history.go(-1); </script>\n";
				}

				if (empty($this->_datas['project']))
				{
					JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Project object is missing inside import file!!!'));
					echo "<script> alert('".JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Project object is missing inside import file!!!')."'); window.history.go(-1); </script>\n";
					return false;
				}

				if ($this->_checkProject()===false)
				{
					JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Projectname already exists'));
					echo "<script> alert('".JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Projectname already exists')."'); window.history.go(-1); </script>\n";
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
						JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Missing sportstype'));
						echo "<script> alert('".JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Missing sportstype')."'); window.history.go(-1); </script>\n";
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
						JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Missing league'));
						echo "<script> alert('".JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Missing league')."'); window.history.go(-1); </script>\n";
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
						JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Missing season'));
						echo "<script> alert('".JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Missing season')."'); window.history.go(-1); </script>\n";
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
             $step = JComponentHelper::getParams('com_sportsmanagement')->get('backend_xmlimport_step',1);
             
			if ( $post['importProject'] || $post['importType']=='events' || $post['importType'] == 'positions' )
			{
				// import sportstype
                if( version_compare($step,'1','ge') ) 
        {
				if ( $this->_importSportsType() === false)
				{
					JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','sports-type'));
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
					JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','league'));
					return $this->_success_text;
				}
}
				// import season
                if( version_compare($step,'3','ge')) 
        {
				if ( $this->_importSeason()===false)
				{
					JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','season'));
					return $this->_success_text;
				}
                }
			}

			// import events / should also work with exported events-XML without problems
            if( version_compare($step,'4','ge') ) 
        {
			if ( $this->_importEvents() === false )
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','event'));
				return $this->_success_text;
			}
}
			// import Statistic
            if(version_compare($step,'5','ge')) 
        {
			if ($this->_importStatistics()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','statistic'));
				return $this->_success_text;
			}
}
			// import parent positions
            if(version_compare($step,'6','ge')) 
        {
			if ($this->_importParentPositions()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','parent-position'));
				return $this->_success_text;
			}
}
			// import positions
            if(version_compare($step,'7','ge')) 
        {
			if ($this->_importPositions()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','position'));
				return $this->_success_text;
			}
}
			// import PositionEventType
            if(version_compare($step,'8','ge')) 
        {
			if ($this->_importPositionEventType()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','position-eventtype'));
				return $this->_success_text;
			}
}
			// import playgrounds
            if(version_compare($step,'9','ge')) 
        {
			if ($this->_importPlayground()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','playground'));
				return $this->_success_text;
			}
}
			// import clubs
            if(version_compare($step,'10','ge')) 
        {
			if ($this->_importClubs()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','club'));
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
					JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','conversion of playground club-id'));
					return $this->_success_text;
				}
                }
			}

			// import teams
            if(version_compare($step,'12','ge')) 
        {
			if ($this->_importTeams()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','team'));
				return $this->_success_text;
			}
}
			// import persons
            if(version_compare($step,'13','ge')) 
        {
			if ($this->_importPersons()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','person'));
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
					JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','project'));
					return $this->_success_text;
				}
}

//				// import template
//                if(version_compare($step,'15','ge')) 
//        {
//				if ($this->_importTemplate()===false)
//				{
//					JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','template'));
//					return $this->_success_text;
//				}
//                }
                
			}
// TO BE FIXED: Check correct function
			// import divisions
            if(version_compare($step,'16','ge')) 
        {
			if ($this->_importDivisions()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','division'));
				return $this->_success_text;
			}
}
			// import project positions
            if(version_compare($step,'17','ge')) 
        {
			if ($this->_importProjectPositions()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','projectpositions'));
				return $this->_success_text;
			}
}
			// import project referees
            if(version_compare($step,'18','ge')) 
        {
			if ($this->_importProjectReferees()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','projectreferees'));
				return $this->_success_text;
			}
}

			// import projectteam
            if(version_compare($step,'19','ge')) 
        {
			if ($this->_importProjectTeam()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','projectteam'));
				return $this->_success_text;
			}
}
			// import teamplayers
            if(version_compare($step,'21','ge')) 
        {
			if ($this->_importTeamPlayer()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','teamplayer'));
				return $this->_success_text;
			}
}
			// import teamstaffs
            if(version_compare($step,'22','ge')) 
        {
			if ($this->_importTeamStaff()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','teamstaff'));
				return $this->_success_text;
			}
}
			// import teamtrainingdata
            if(version_compare($step,'23','ge')) 
        {
			if ($this->_importTeamTraining()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','teamtraining'));
				return $this->_success_text;
			}
}
			// import rounds
            if( version_compare($step,'24','ge') ) 
        {
			if ( $this->_importRounds() === false )
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','round'));
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
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','match'));
				return $this->_success_text;
			}
}
			// import MatchPlayer
            if(version_compare($step,'26','ge')) 
        {
			if ($this->_importMatchPlayer()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','matchplayer'));
				return $this->_success_text;
			}
}
			// import MatchStaff
            if(version_compare($step,'27','ge')) 
        {
			if ($this->_importMatchStaff()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','matchstaff'));
				return $this->_success_text;
			}
}
			// import MatchReferee
            if(version_compare($step,'28','ge')) 
        {
			if ($this->_importMatchReferee()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','matchreferee'));
				return $this->_success_text;
			}
}
			// import MatchEvent
            if(version_compare($step,'29','ge')) 
        {
			if ($this->_importMatchEvent()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','matchevent'));
				return $this->_success_text;
			}
}
			// import PositionStatistic
            if(version_compare($step,'30','ge')) 
        {
			if ($this->_importPositionStatistic()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','positionstatistic'));
				return $this->_success_text;
			}
}
			// import MatchStaffStatistic
            if(version_compare($step,'31','ge')) 
        {
			if ($this->_importMatchStaffStatistic()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','matchstaffstatistic'));
				return $this->_success_text;
			}
}
			// import MatchStatistic
            if(version_compare($step,'32','ge')) 
        {
			if ($this->_importMatchStatistic()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','matchstatistic'));
				return $this->_success_text;
			}
            }
			// import Treeto
            if(version_compare($step,'33','ge')) 
        {
			if ($this->_importTreetos()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','treeto'));
				return $this->_success_text;
			}
}
			// import Treetonode
            if(version_compare($step,'34','ge')) 
        {
			if ($this->_importTreetonode()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','treetonode'));
				return $this->_success_text;
			}
}
			// import Treetomatch
            if(version_compare($step,'35','ge')) 
        {
			if ($this->_importTreetomatch()===false)
			{
				JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_ERROR_DURING','treetomatch'));
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
$mdl = JModelLegacy::getInstance("databasetool", "sportsmanagementModel"); 
$mdl->setNewPicturePath();            

            return $this->_success_text;
		}
		else
		{
			$this->_deleteImportFile();
			JError::raiseWarning(500,JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR','Missing import data'));
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
		echo "<b>$description</b><pre>".print_r($variable,true)."</pre>";
	}
    
    /**
     * sportsmanagementModelJLXMLImport::setNewDataStructur()
     * 
     * @return void
     */
    function setNewDataStructur()
    {
        $app = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        // $this->_project_id
        // $this->_season_id 
        $my_text='';
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
		JFactory::getDbo()->setQuery($query);
		$result_pr = JFactory::getDbo()->loadObjectList();
        
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
			    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__); 
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
                $query->update($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee'))->set($fields)->where($conditions);
                $db->setQuery($query);
                $result = sportsmanagementModeldatabasetool::runJoomlaQuery();  
                if (!$result)
			    {
			    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__); 
			    } 
                
                
        
        }
        
        // die teams verarbeiten
        $query = 'SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team where project_id = '.$this->_project_id ;
		JFactory::getDbo()->setQuery($query);
		$result_pt = JFactory::getDbo()->loadObjectList();
        
        //echo "<b>setNewDataStructur</b><pre>".print_r($result_pt,true)."</pre>";
         
        foreach ( $result_pt as $proteam )
        {
            //echo "<b>seasonteam</b><pre>".print_r($table_ST,true)."</pre>";
            
            
            // ist das team schon durch ein anderes projekt angelegt ?
            $query = $db->getQuery(true);
		    /*
            $query->select('id');		
		    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS t');
		    $query->where('t.team_id = '.$proteam->team_id);
            $query->where('t.season_id = '.$this->_season_id);
		    $db->setQuery($query);
		    $new_team_id = $db->loadResult();
            */
            $query->select('team_id');		
		    $query->from('#__sportsmanagement_season_team_id AS t');
		    $query->where('t.id = '.$proteam->team_id);
            $query->where('t.season_id = '.$this->_season_id);
		    $db->setQuery($query);
		    $new_team_id = $db->loadResult();
            
            
/**
 * das musste ich aussternen, da die aktualisierung in die neuen strukturen hier
 * zu spät ist. ein update der projectteam tabelle ist nicht so einfach möglich. 
 */
/*                        
            if ( $new_team_id )
            {
                $my_text .= '<span style="color:'.$this->existingInDbColor.'">';
						$my_text .= JText::sprintf(	'mannschaft vorhanden alte id: %1$s - saison: %2$s - neue id: %3$s',
													"</span><strong>$proteam->team_id</strong>",
													"<strong>$this->_season_id</strong>",
                                                    "<strong>$new_team_id</strong>"
													);
						$my_text .= '<br />';
                        
               
            }
            else
            {
            // Create a new query object.
            $insertquery = $db->getQuery(true);
            // Insert columns.
            $columns = array('team_id','season_id','picture');
            // Insert values.
            $values = array($proteam->team_id,$this->_season_id,'\''.$proteam->picture.'\'');
            // Prepare the insert query.
            $insertquery
            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
            // Set the query using our newly populated query object and execute it.
            $db->setQuery($insertquery);
            
			if (!$db->execute())
			{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__); 
			}
			else
			{
                // die project team tabelle updaten
                $new_team_id = $db->insertid();
			}
            
            $my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= JText::sprintf(	'mannschaft nicht vorhanden und angelegt alte id: %1$s - saison: %2$s - neue id: %3$s',
													"</span><strong>$proteam->team_id</strong>",
													"<strong>$this->_season_id</strong>",
                                                    "<strong>$new_team_id</strong>"
													);
						$my_text .= '<br />';
            
            }
            // die project team tabelle updaten
            if ( $new_team_id )
            {
            
            $mdl = JModelLegacy::getInstance("projectteam", "sportsmanagementModel");
            $row = $mdl->getTable();
            $row->load($proteam->id);
            $row->team_id = $new_team_id;

            if ( !$row->store() )
            {
            $app->enqueueMessage(JText::_(get_class($this).__FUNCTION__.' -> '.'<pre>'.print_r(JFactory::getDbo()->getErrorMsg(),true).'</pre>'),'Error');    
            }
            else
            {
            $my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= JText::sprintf(	'mannschaft updaten _project_team new_team_id: %1$s - proteam->team_id: %2$s - projekt: %3$s',
													"</span><strong>$new_team_id</strong>",
													"<strong>$proteam->team_id</strong>",
                                                    "<strong>$this->_project_id</strong>"
													);
						$my_text .= '<br />';
            }            
            }    
*/
            
            // die spieler verarbeiten
            $query = $db->getQuery(true);
            $query->select('tp.*');
            $query->select('p.position_id');
            $query->from('#__sportsmanagement_team_player AS tp');
            $query->join('INNER','#__sportsmanagement_person as p ON p.id = tp.person_id ');
            $query->where('tp.projectteam_id = '.$proteam->id);
            //$query = 'SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player where projectteam_id = '.$proteam->id ;
            JFactory::getDbo()->setQuery($query);
		    $result_tp = JFactory::getDbo()->loadObjectList();
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
						$my_text .= JText::sprintf(	'spieler vorhanden season_person_id: alte id: %1$s - saison: %2$s - neue id: %3$s',
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
			    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__); 
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
						$my_text .= JText::sprintf(	'spieler vorhanden season_team_person_id: alte id: %1$s - saison: %2$s - neue id: %3$s - team id: %4$s',
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
			    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__); 
			    }
			    else
			    {
			    // die match player tabelle updaten
                $new_match_player_id = $db->insertid();
                $my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= JText::sprintf(	'spieler nicht vorhanden und angelegt season_team_person_id: alte id: %1$s - saison: %2$s - neue id: %3$s - team id: %4$s',
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
						$my_text .= JText::sprintf(	'spieler vorhanden _person_project_position: person id: %1$s - projekt: %2$s - project_position_id: %3$s',
													"</span><strong>$team_member->person_id</strong>",
													"<strong>$this->_project_id</strong>",
                                                    "<strong>$team_member->project_position_id</strong>"
													);
						$my_text .= '<br />'; 
			    }
			    else
			    {
                $my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= JText::sprintf(	'spieler nicht vorhanden und angelegt _person_project_position: person id: %1$s - projekt: %2$s - project_position_id: %3$s',
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
			    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__); 
			    }
                $query->update($db->quoteName('#__sportsmanagement_match_event'))->set($fields)->where($conditions);
                $db->setQuery($query);
                $result = sportsmanagementModeldatabasetool::runJoomlaQuery();
                if (!$result)
			    {
			    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__); 
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
			    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__); 
			    } 
			    
            }
            
            // staffs verarbeiten
            $query = $db->getQuery(true);
            $query->select('tp.*');
            $query->select('p.position_id');
            $query->from('#__sportsmanagement_team_staff AS tp');
            $query->join('INNER','#__sportsmanagement_person as p ON p.id = tp.person_id ');
            $query->where('tp.projectteam_id = '.$proteam->id);
            //$query = 'SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff where projectteam_id = '.$proteam->id ;
            JFactory::getDbo()->setQuery($query);
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
		    $result_tp = JFactory::getDbo()->loadObjectList();
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
						$my_text .= JText::sprintf(	'trainer vorhanden season_person_id: alte id: %1$s - saison: %2$s - neue id: %3$s',
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
			    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r(JFactory::getDbo()->getErrorMsg(),true).'</pre>'),'Notice');  
			    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__); 
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
						$my_text .= JText::sprintf(	'trainer vorhanden season_team_person_id: alte id: %1$s - saison: %2$s - neue id: %3$s - team id: %4$s',
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
			    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r(JFactory::getDbo()->getErrorMsg(),true).'</pre>'),'Notice'); 
			    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__); 
			    }
			    else
			    {
			    // die match staff tabelle updaten
                $new_match_staff_id = $db->insertid();
                $my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
						$my_text .= JText::sprintf(	'trainer nicht vorhanden und angelegt season_team_person_id: alte id: %1$s - saison: %2$s - neue id: %3$s - team id: %4$s',
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
						$my_text .= JText::sprintf(	'trainer vorhanden _person_project_position: person id: %1$s - projekt: %2$s - project_position_id: %3$s',
													"</span><strong>$team_member->person_id</strong>",
													"<strong>$this->_project_id</strong>",
                                                    "<strong>$team_member->project_position_id</strong>"
													);
						$my_text .= '<br />'; 
			    }
			    else
			    {
                $my_text .= '<span style="color:'.$this->existingStaff.'">';
						$my_text .= JText::sprintf(	'trainer nicht vorhanden und angelegt _person_project_position: person id: %1$s - projekt: %2$s - project_position_id: %3$s',
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
			    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__); 
			    }

            }
            
            

        }
        
        // zum schluss den inhalt der alten tabellen löschen
        // wegen speicherplatz
        $databasetool = JModelLegacy::getInstance("databasetool", "sportsmanagementModel");
        $successTable = $databasetool->setSportsManagementTableQuery('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff', 'truncate');
        $successTable = $databasetool->setSportsManagementTableQuery('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player', 'truncate');
        
        $this->_success_text[__FUNCTION__] = $my_text;
        
        self::setNewRoundDates();
        
    }
    
    /**
     * sportsmanagementModelJLXMLImport::setNewRoundDates()
     * 
     * @return void
     */
    function setNewRoundDates()
    {
        $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        // Get a db connection.
        $db = JFactory::getDbo();
        
        $query = "SELECT r.id as round_id
FROM #__".COM_SPORTSMANAGEMENT_TABLE."_round AS r
WHERE r.project_id = " . $this->_project_id .' ORDER by roundcode DESC';

JFactory::getDbo()->setQuery( $query );
$rounds = JFactory::getDbo()->loadObjectList();

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

JFactory::getDbo()->setQuery( $query );
$von = JFactory::getDbo()->loadResult();
$teile = explode(" ",$von);
$von = $teile[0];

$query = "SELECT max(m.match_date)
from #__".COM_SPORTSMANAGEMENT_TABLE."_match as m
where m.round_id = '$rounddate->round_id'
";

JFactory::getDbo()->setQuery( $query );
$bis = JFactory::getDbo()->loadResult();
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
$result = JFactory::getDbo()->updateObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round', $object, 'id');                

if (!$result)
		{
		    $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' update round<br><pre>'.print_r(JFactory::getDbo()->getErrorMsg(),true).'</pre>'),'Error');
		}
        else
        {
//            $app->enqueueMessage(JText::_('update round<br><pre>'.print_r($rounddate->round_id,true).'</pre>'),'');
        }
        
        
// gibt es noch nicht ausgetragene spiele in der runde ?
// Create a new query object.
        $query = JFactory::getDbo()->getQuery(true);
        $query->select(array('id'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match')
        ->where('team1_result IS NULL ')
        ->where('round_id = '.$rounddate->round_id);    
        JFactory::getDbo()->setQuery($query);
		$match_id = JFactory::getDbo()->loadResult();

//if ( $match_id && empty($current_round) )
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

//$app->enqueueMessage(JText::_('current_round<br><pre>'.print_r($current_round,true).'</pre>'),'');
//$app->enqueueMessage(JText::_('_project_id<br><pre>'.print_r($this->_project_id,true).'</pre>'),'');        


// Create an object for the record we are going to update.
$object = new stdClass();
 
// Must be a valid primary key value.
$object->id = $this->_project_id;
$object->current_round = $current_round;
 
// Update their details in the users table using id as the primary key.
$result = JFactory::getDbo()->updateObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project', $object, 'id');                

if (!$result)
		{
		    $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' update projekt<br><pre>'.print_r(JFactory::getDbo()->getErrorMsg(),true).'</pre>'),'Error');
		}
        else
        {
//            $app->enqueueMessage(JText::_('update _project_id<br><pre>'.print_r($this->_project_id,true).'</pre>'),'');
        }
                
        
    }    
}
?>
