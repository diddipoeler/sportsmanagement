<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage predictionuser
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;
JLoader::import('components.com_sportsmanagement.models.predictionusers', JPATH_SITE);

/**
 * sportsmanagementViewPredictionUser
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewPredictionUser extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewPredictionUser::init()
	 * 
	 * @return void
	 */
	function init()
	{
		$js = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.js';
        $this->document->addScript($js); 
        $rounds	= sportsmanagementModelProject::getRounds('ASC',$this->jinput->getint( "cfg_which_database", 0 ));
$this->round_labels = array();
foreach ($rounds as $r) 
{
$this->round_labels[] = '"'.$r->name.'"';
}
   
//		$this->document->addScript(Uri::root().'components/com_sportsmanagement/assets/js/json2.js');
//		$this->document->addScript(Uri::root().'components/com_sportsmanagement/assets/js/swfobject.js');
		
    $mdlPredUsers = BaseDatabaseModel::getInstance("predictionusers", "sportsmanagementModel");
    
		$this->predictionGame = sportsmanagementModelPrediction::getPredictionGame();
        
        $tippAllowed = false;

		if (isset($this->predictionGame))
		{
			$config				= sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
			$overallConfig		= sportsmanagementModelPrediction::getPredictionOverallConfig();
			$tipprankingconfig	= sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionranking');
			//$flashconfig 		= sportsmanagementModelPrediction::getPredictionTemplateConfig( "predictionflash" );
			
			$configavatar			= sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
      
			$this->model = $mdlPredUsers;
			$this->roundID = sportsmanagementModelPrediction::$roundID;
			$this->config = array_merge($overallConfig,$tipprankingconfig,$config);
			$this->configavatar = $configavatar;
			
			$this->predictionMember = sportsmanagementModelPrediction::getPredictionMember($configavatar);
			
      if (!isset($this->predictionMember->id))
			{
				$this->predictionMember->id=0;
				$this->predictionMember->pmID=0;
			}
			
			$this->predictionProjectS = sportsmanagementModelPrediction::getPredictionProjectS();

			$this->actJoomlaUser = Factory::getUser();
			$this->isPredictionMember = sportsmanagementModelPrediction::checkPredictionMembership();
			$this->memberData = sportsmanagementModelPredictionUsers::memberPredictionData();
			$this->allowedAdmin = sportsmanagementModelPrediction::getAllowed();
			
			if (!empty($this->predictionMember->user_id)) 
            {
				$this->showediticon = sportsmanagementModelPrediction::getAllowed($this->predictionMember->user_id);
			}
			
			$this->_setPointsChartdata(array_merge($config));
			$this->_setRankingChartdata(array_merge($config));
            
if ( ComponentHelper::getParams($this->option)->get('show_debug_info_frontend') )
{
 
}
			$lists = array();

			if ($this->predictionMember->pmID > 0){$dMemberID=$this->predictionMember->pmID;}else{$dMemberID=0;}
			if (!$this->allowedAdmin){$userID=$this->actJoomlaUser->id;}else{$userID=null;}

			$predictionMembers[] = HTMLHelper::_('select.option','0',Text::_('COM_SPORTSMANAGEMENT_PRED_SELECT_MEMBER'),'value','text');
			if ($res = sportsmanagementModelPrediction::getPredictionMemberList($this->config,$userID))
            {
                $predictionMembers = array_merge($predictionMembers,$res);
                }
                
			$lists['predictionMembers'] = HTMLHelper::_('select.genericList',$predictionMembers,'uid','class="inputbox" onchange="this.form.submit(); "','value','text',$dMemberID);
			unset($res);
			unset($predictionMembers);

			if (empty($this->predictionMember->fav_team))
            {
                $this->predictionMember->fav_team='0,0';
                }
			$sFavTeamsList = explode(';',$this->predictionMember->fav_team);
			foreach($sFavTeamsList AS $key => $value)
            {
                $dFavTeamsList[] = explode(',',$value);
                }
			foreach($dFavTeamsList AS $key => $value)
            {
                $favTeamsList[$value[0]] = $value[1];
                }

			if (empty($this->predictionMember->champ_tipp))
            {
                $this->predictionMember->champ_tipp = '0,0';
                }
			$sChampTeamsList = explode(';',$this->predictionMember->champ_tipp);
			foreach($sChampTeamsList AS $key => $value)
            {
                $dChampTeamsList[] = explode(',',$value);
                }
			foreach($dChampTeamsList AS $key => $value)
            {
                $champTeamsList[$value[0]] = $value[1];
                }

			if ( $this->getLayout() == 'edit' )
			{
				$dArray[] = HTMLHelper::_('select.option',0,Text::_('JNO'));
				$dArray[] = HTMLHelper::_('select.option',1,Text::_('JYES'));

				$lists['show_profile'] = HTMLHelper::_('select.radiolist',$dArray,'show_profile','class="inputbox" size="1"','value','text',$this->predictionMember->show_profile);
				$lists['reminder'] = HTMLHelper::_('select.radiolist',$dArray,'reminder','class="inputbox" size="1"','value','text',$this->predictionMember->reminder);
				$lists['receipt'] = HTMLHelper::_('select.radiolist',$dArray,'receipt','class="inputbox" size="1"','value','text',$this->predictionMember->receipt);
				$lists['admintipp'] = HTMLHelper::_('select.radiolist',$dArray,'admintipp','class="inputbox" size="1"','value','text',$this->predictionMember->admintipp);
				$lists['approvedForGame'] = HTMLHelper::_('select.radiolist',$dArray,'approved','class="inputbox" size="1" disabled="disabled"','value','text',$this->predictionMember->approved);
				unset($dArray);
                /**
                 * schleife über die projekte
                 */
				foreach($this->predictionProjectS AS $predictionProject)
				{
         
					$projectteams[] = HTMLHelper::_('select.option','0',Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_SELECT_TEAM'),'value','text');
					if ($res = sportsmanagementModelPredictionUsers::getPredictionProjectTeams($predictionProject->project_id))
					{
						$projectteams = array_merge($projectteams,$res);
					}
					if (!isset($favTeamsList[$predictionProject->project_id]))
          {
          $favTeamsList[$predictionProject->project_id] = 0;
          }
          
					$lists['fav_team'][$predictionProject->project_id] = HTMLHelper::_('select.genericList',$projectteams,'fav_team['.$predictionProject->project_id.']','class="inputbox"','value','text',$favTeamsList[$predictionProject->project_id]);

		/**
		 * kann champion ausgewaehlt werden ?
		 */
		if ( $predictionProject->champ )
          {
          $disabled = '';
          /**
           * ist überhaupt das startdatum gesetzt ?
           */
          if ( $predictionProject->start_date == '0000-00-00' )
          {
          $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING_STARTDATE'),'Error');  
          $disabled=' disabled="disabled" ';
          }
          else
          {
          /**
           * ist die saison beendet ?
           */  
          $predictionProjectSettings = sportsmanagementModelPrediction::getPredictionProject($predictionProject->project_id);
          $time = strtotime($predictionProject->start_date);
          $time += 86400; // Ein Tag in Sekunden
          $showDate = date("Y-m-d",$time);
          $thisTimeDate = sportsmanagementHelper::getTimestamp(date("Y-m-d H:i:s"),1,$predictionProjectSettings->timezone);
          $competitionStartTimeDate = sportsmanagementHelper::getTimestamp($showDate,1,$predictionProjectSettings->timezone);
          $tippAllowed =	( ( $thisTimeDate < $competitionStartTimeDate ) ) ;
		if (!$tippAllowed)
        {
            $disabled = ' disabled="disabled" ';
            }
            else
            {
                $disabled = ''; 
            }
          }
           
          }
          else
          {
          $disabled = ' disabled="disabled" ';
          }
          
          $predictionMembers[] = HTMLHelper::_('select.option','0',Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PREDICTION_MEMBER_GROUP'),'value','text');
			if ( $res = sportsmanagementModelPrediction::getPredictionGroupList())
            {
                $predictionMembers = array_merge($predictionMembers,$res);
                }
                
			$lists['grouplist'] = HTMLHelper::_('select.genericList',$predictionMembers,'group_id','class="inputbox" '.$disabled.'onchange=""','value','text',$this->predictionMember->group_id);
			unset($res);
			unset($predictionMembers);
          
					if (!isset($champTeamsList[$predictionProject->project_id]))
          {
          $champTeamsList[$predictionProject->project_id] = 0;
          }
          
$lists['champ_tipp_disabled'][$predictionProject->project_id] = HTMLHelper::_('select.genericList',$projectteams,'champ_tipp['.$predictionProject->project_id.']','class="inputbox"'.$disabled.'','value','text',$champTeamsList[$predictionProject->project_id]);
$lists['champ_tipp_enabled'][$predictionProject->project_id] = HTMLHelper::_('select.genericList',$projectteams,'champ_tipp['.$predictionProject->project_id.']','class="inputbox"'.$disabled.'','value','text',$champTeamsList[$predictionProject->project_id]);

					unset($projectteams);
				}
        
				$this->form = $this->get('form');
                $this->form->setValue('picture', null,$this->predictionMember->picture);	
			}
			else
			{
				$this->favTeams = $favTeamsList;
				$this->champTeams = $champTeamsList;
			}

			$this->lists = $lists;
            $this->tippallowed = $tippAllowed;
      
			// Set page title
			$pageTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_TITLE');
            $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_TITLE' );

			$this->document->setTitle($pageTitle);

		}
		else
		{
			JLog::add(Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'), JLog::INFO, 'jsmerror');
		}



	}

	/**
	 * sportsmanagementViewPredictionUser::_setPointsChartdata()
	 * 
	 * @param mixed $config
	 * @return
	 */
	function _setPointsChartdata($config)
	{
		$data = sportsmanagementModelPredictionUsers::getPointsChartData();
    
		// Calculate Values for Chart Object
		$userpoints= array();
        $PointsCountMax = 0;		

		foreach( $data as $rw )
		{
			if (!$rw->points) $rw->points = 0;
			$userpoints[] = (int)$rw->points;
            $PointsCountMax = (int)$rw->points > $PointsCountMax ? (int)$rw->points : $PointsCountMax;
		}
$this->PointsCountMax = $PointsCountMax;
$this->userpoints = $userpoints;
		
	}

	/**
	 * sportsmanagementViewPredictionUser::_setRankingChartdata()
	 * 
	 * @param mixed $config
	 * @return
	 */
	function _setRankingChartdata($config)
	{
//		JLoader::import('components.com_sportsmanagement.assets.classes.open-flash-chart.open-flash-chart', JPATH_SITE);
//
//		//$data = $this->get('RankChartData');		
//		//some example data....fixme!!!
//		$data_1 = array();
//		$data_2 = array();
//
//		for( $i=0; $i<6.2; $i+=0.2 )
//		{
//			$data_1[] = (sin($i) * 1.9) + 10;
//		}
//
//		for( $i=0; $i<6.2; $i+=0.2 )
//		{
//			$data_2[] = (sin($i) * 1.3) + 10;
//		}
//		
//		$chart = new open_flash_chart();
//		//***********
//		
//		//line 1
//		$d = new $config['dotstyle_1']();
//		$d->size((int) $config['line1_dot_strength']);
//		$d->halo_size(1);
//		$d->colour($config['line1']);
//		$d->tooltip('Rank: #val#');
//
//		$line = new line();
//		$line->set_default_dot_style($d);
//		$line->set_values( $data_1 );
//		$line->set_width( (int) $config['line1_strength'] );
//		///$line->set_key($team->name, 12);
//		$line->set_colour( $config['line1'] );
//		$line->on_show(new line_on_show($config['l_animation_1'], $config['l_cascade_1'], $config['l_delay_1']));
//		$chart->add_element($line);
//		
//		//Line 2
//		$d = new $config['dotstyle_2']();
//		$d->size((int) $config['line2_dot_strength']);
//		$d->halo_size(1);
//		$d->colour($config['line2']);
//		$d->tooltip('Rank: #val#');
//
//		$line = new line();
//		$line->set_default_dot_style($d);
//		$line->set_values( $data_2);
//		$line->set_width( (int) $config['line2_strength'] );
//		//$line->set_key($team->name, 12);
//		$line->set_colour( $config['line2'] );
//		$line->on_show(new line_on_show($config['l_animation_2'], $config['l_cascade_2'], $config['l_delay_2']));
//		$chart->add_element($line);
//		
//		//X-axis
//		$x = new x_axis();
//		$x->set_colours($config['x_axis_colour'], $config['x_axis_colour_inner']);
//		//$x->set_labels_from_array($round_labels);
//		$chart->set_x_axis( $x );
//		$x_legend = new x_legend( Text::_('COM_SPORTSMANAGEMENT_PRED_USER_ROUNDS') );
//		$x_legend->set_style( '{font-size: 15px; color: #778877}' );
//		$chart->set_x_legend( $x_legend );
//
//		//Y-axis
//		$y = new y_axis();
//		$y->set_range( 0, @max($data_1)+2, 1);
//		$y->set_steps(round(@max($data_1)/8));
//		$y->set_colours($config['y_axis_colour'], $config['y_axis_colour_inner']);
//		$chart->set_y_axis( $y );
//		$y_legend = new y_legend( Text::_('COM_SPORTSMANAGEMENT_PRED_USER_POINTS') );
//		$y_legend->set_style( '{font-size: 15px; color: #778877}' );
//		$chart->set_y_legend( $y_legend );
//		
//		$this->rankingchartdata = $chart;
	}
}
?>