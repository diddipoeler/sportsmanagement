<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teamstats
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * sportsmanagementViewTeamStats
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewTeamStats extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewTeamStats::init()
	 * 
	 * @return void
	 */
	function init()
	{

		if ( isset( $this->project ) )
		{

			if ( !isset( $this->overallconfig['seperator'] ) )
			{
				$this->overallconfig['seperator'] = ":";
			}

			$this->tableconfig = sportsmanagementModelProject::getTemplateConfig("ranking",sportsmanagementModelTeamStats::$cfg_which_database );
			$this->eventsconfig = sportsmanagementModelProject::getTemplateConfig("eventsranking",sportsmanagementModelTeamStats::$cfg_which_database );
			$this->actualround = sportsmanagementModelProject::getCurrentRound(NULL,sportsmanagementModelTeamStats::$cfg_which_database);
			$this->team = $this->model->getTeam();
            $this->highest_home = $this->model->getHighest('HOME','WIN');
			$this->highest_away = $this->model->getHighest('AWAY','WIN');
            $this->highestdef_home = $this->model->getHighest('HOME','DEF');
			$this->highestdef_away = $this->model->getHighest('AWAY','DEF');
            $this->highestdraw_home = $this->model->getHighest('HOME','DRAW');
			$this->highestdraw_away = $this->model->getHighest('AWAY','DRAW');
			$this->totalshome = $this->model->getSeasonTotals('HOME');
            $this->totalsaway = $this->model->getSeasonTotals('AWAY');
            $this->matchdaytotals = $this->model->getMatchDayTotals();
			$this->totalrounds = $this->model->getTotalRounds();
			$this->totalattendance = $this->model->getTotalAttendance();
			$this->bestattendance = $this->model->getBestAttendance();
			$this->worstattendance = $this->model->getWorstAttendance();
			$this->averageattendance = $this->model->getAverageAttendance();
			$this->chart_url = $this->model->getChartURL();
			$this->nogoals_against = $this->model->getNoGoalsAgainst();
			$this->logo = $this->model->getLogo();
			$this->results = $this->model->getResults();

			$this->_setChartdata(array_merge(sportsmanagementModelProject::getTemplateConfig("flash",sportsmanagementModelTeamStats::$cfg_which_database ), $this->config));
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' results<br><pre>'.print_r($this->results,true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' totalshome<br><pre>'.print_r($this->totalshome,true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' totalsaway<br><pre>'.print_r($this->totalsaway,true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' highest_home<br><pre>'.print_r($this->highest_home,true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' highest_away<br><pre>'.print_r($this->highest_away,true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' highestdef_home<br><pre>'.print_r($this->highestdef_home,true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' highestdef_away<br><pre>'.print_r($this->highestdef_away,true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' highestdraw_home<br><pre>'.print_r($this->highestdraw_home,true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' highestdraw_away<br><pre>'.print_r($this->highestdraw_away,true).'</pre>'),'');
            
		}
	
		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_TEAMSTATS_PAGE_TITLE' );
		if ( isset( $this->team ) )
		{
			$pageTitle .= ': ' . $this->team->name;
		}
		$this->document->setTitle( $pageTitle );

//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$this->option.'/assets/css/'.$this->view.'.css'.'" type="text/css" />' ."\n";
//        $this->document->addCustomTag($stylelink);
        
        $this->headertitle = JText::_( 'COM_SPORTSMANAGEMENT_TEAMSTATS_TITLE' ) . " - " . $this->team->name;
        
		//parent::display( $tpl );
	}

	/**
	 * assign the chartdata object for open flash chart library
	 * @param $config
	 * @return unknown_type
	 */
	function _setChartdata($config)
	{
		require_once( JPATH_COMPONENT_SITE.DS."assets".DS."classes".DS."open-flash-chart".DS."open-flash-chart.php" );

		$data = $this->get('ChartData');

		// Calculate Values for Chart Object
		$forSum = array();
		$againstSum = array();
		$matchDayGoalsCount = array();
		$matchDayGoalsCount[] = 0;
		$round_labels = array();

		$matchDayGoalsCountMax = 0;
		foreach( $data as $rw )
		{
			if (!$rw->goalsfor) $rw->goalsfor = 0;
			if (!$rw->goalsagainst) $rw->goalsagainst = 0;
			$forSum[]     = intval($rw->goalsfor);
			$againstSum[] = intval($rw->goalsagainst);

			// check, if both results are missing and avoid drawing the flatline of "0" goals for not played games yet
			if ((!$rw->goalsfor) && (!$rw->goalsagainst))
			{
				$matchDayGoalsCount[] = 0;
			}
			else
			{
				$matchDayGoalsCount[] = intval($rw->goalsfor + $rw->goalsagainst);
			}
			$round_labels[] = $rw->roundcode;
		}
		
		$chart = new open_flash_chart();
		//$chart->set_title( $title );
		$chart->set_bg_colour($config['bg_colour']);

		$barfor = new $config['bartype_1']();
		$barfor->set_values( $forSum );
		$barfor->set_tooltip( JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_FOR'). ": #val#" );
		$barfor->set_colour( $config['bar1'] );
		$barfor->set_on_show(new bar_on_show($config['animation_1'], $config['cascade_1'], $config['delay_1']));
		$barfor->set_key(JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_FOR'), 12);

		$baragainst = new $config['bartype_2']();
		$baragainst->set_values( $againstSum );
		$baragainst->set_tooltip(   JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_AGAINST'). ": #val#" );
		$baragainst->set_colour( $config['bar2'] );
		$baragainst->set_on_show(new bar_on_show($config['animation_2'], $config['cascade_2'], $config['delay_2']));
		$baragainst->set_key(JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_AGAINST'), 12);

		$chart->add_element($barfor);
		$chart->add_element($baragainst);

		// total
		$d = new $config['dotstyle_3']();
		$d->size((int)$config['line3_dot_strength']);
		$d->halo_size(1);
		$d->colour($config['line3']);
		$d->tooltip(JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_TOTAL2').' #val#');

		$line = new line();
		$line->set_default_dot_style($d);
		$line->set_values(array_slice( $matchDayGoalsCount,1) );
		$line->set_width( (int) $config['line3_strength'] );
		$line->set_key(JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_TOTAL'), 12);
		$line->set_colour( $config['line3'] );
		$line->on_show(new line_on_show($config['l_animation_3'], $config['l_cascade_3'], $config['l_delay_3']));
		$chart->add_element($line);		
		
		$x = new x_axis();
		$x->set_colours($config['x_axis_colour'], $config['x_axis_colour_inner']);
		$x->set_labels_from_array($round_labels);
		$chart->set_x_axis( $x );
		$x_legend = new x_legend( JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_ROUNDS') );
		$x_legend->set_style( '{font-size: 15px; color: #778877}' );
		$chart->set_x_legend( $x_legend );

		$y = new y_axis();
		$y->set_range( 0, max($matchDayGoalsCount)+2, $config['y_axis_steps']);
		$y->set_colours($config['y_axis_colour'], $config['y_axis_colour_inner']);
		$chart->set_y_axis( $y );
		$y_legend = new y_legend( JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS') );
		$y_legend->set_style( '{font-size: 15px; color: #778877}' );
		$chart->set_y_legend( $y_legend );

		$this->chartdata = $chart;
	}
}
?>
