<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * sportsmanagementViewStats
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewStats extends JViewLegacy
{
	/**
	 * sportsmanagementViewStats::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display($tpl = null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        // Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();

		$model = $this->getModel();
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());

		$tableconfig = sportsmanagementModelProject::getTemplateConfig( "ranking" );
		$eventsconfig = sportsmanagementModelProject::getTemplateConfig( "eventsranking" );
		$flashconfig = sportsmanagementModelProject::getTemplateConfig( "flash" );

		$this->assign( 'project', sportsmanagementModelProject::getProject() );
		if ( isset( $this->project ) )
		{
			$this->assign( 'division', sportsmanagementModelProject::getDivision($model->divisionid));
			$this->assign( 'overallconfig', sportsmanagementModelProject::getOverallConfig() );
			if ( !isset( $this->overallconfig['seperator'] ) )
			{
				$this->overallconfig['seperator'] = ":";
			}
			$this->assignRef( 'config', $config );
			$this->assignRef( 'model', $model);
				
			$this->assignRef( 'tableconfig', $tableconfig );
			$this->assignRef( 'eventsconfig', $eventsconfig );
			$this->assign( 'actualround', sportsmanagementModelProject::getCurrentRoundNumber() );

//			$this->assign( 'highest_home', 		$model->getHighestHome());
//			$this->assign( 'highest_away', 		$model->getHighestAway());
			
            $this->assign( 'highest_home', 		$model->getHighest('HOME'));
			$this->assign( 'highest_away', 		$model->getHighest('AWAY'));
            
            $this->assign( 'totals', 			$model->getSeasonTotals());
			$this->assign( 'totalrounds', 		$model->getTotalRounds());
			$this->assign( 'attendanceranking', 	$model->getAttendanceRanking());
			$this->assign( 'bestavg', 			$model->getBestAvg());
			$this->assign( 'bestavgteam', 		$model->getBestAvgTeam());
			$this->assign( 'worstavg', 			$model->getWorstAvg());
			$this->assign( 'worstavgteam', 		$model->getWorstAvgTeam());

			$limit = 3;

			$this->assignRef( 'limit', $limit );
			$this->_setChartdata(array_merge($flashconfig, $config));
		}
		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_STATS_PAGE_TITLE' );
		if ( isset( $this->project ) )
		{
			$pageTitle .= ': ' . $this->project->name;
			if ( isset( $this->division ) )
			{
				$pageTitle .= ': ' . $this->division->name;
			}
		}
		$document->setTitle( $pageTitle );
        
        $view = JRequest::getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);

		parent::display( $tpl );
	}

	/**
	 * sportsmanagementViewStats::_setChartdata()
	 * 
	 * @param mixed $config
	 * @return
	 */
	function _setChartdata($config)
	{
		require_once( JPATH_SITE.DS.JSM_PATH.DS."assets".DS."classes".DS."open-flash-chart".DS."open-flash-chart.php" );

		$data = $this->get('ChartData');
		// Calculate Values for Chart Object
		$forSum = array();
		$againstSum = array();
		$matchDayGoalsCount = array();
		$round_labels = array();

		foreach( $data as $rw )
		{
			if (!$rw->homegoalspd) $rw->homegoalspd = 0;
			if (!$rw->guestgoalspd) $rw->guestgoalspd = 0;
			$homeSum[] = (int)$rw->homegoalspd;
			$awaySum[] = (int)$rw->guestgoalspd;
			// check, if both results are missing and avoid drawing the flatline of "0" goals for not played games yet
			if ((!$rw->homegoalspd) && (!$rw->guestgoalspd))
			{
				$matchDayGoalsCount[] = null;
			}
			else
			{
				$matchDayGoalsCount[] = (int)$rw->homegoalspd + $rw->guestgoalspd;
			}
			$round_labels[] = $rw->roundcode;
		}

		$chart = new open_flash_chart();
		//$chart->set_title( $title );
		$chart->set_bg_colour($config['bg_colour']);

		if(!empty($homeSum)&&(!empty($awaySum)))
		{
			if ( $config['home_away_stats'] )
			{
				$bar1 = new $config['bartype_1']();
				$bar1->set_values( $homeSum );
				$bar1->set_tooltip( JText::_('COM_SPORTSMANAGEMENT_STATS_HOME'). ": #val#" );
				$bar1->set_colour( $config['bar1'] );
				$bar1->set_on_show(new bar_on_show($config['animation_1'], $config['cascade_1'], $config['delay_1']));
				$bar1->set_key(JText::_('COM_SPORTSMANAGEMENT_STATS_HOME'), 12);

				$bar2 = new $config['bartype_2']();
				$bar2->set_values( $awaySum );
				$bar2->set_tooltip(   JText::_('COM_SPORTSMANAGEMENT_STATS_AWAY'). ": #val#" );
				$bar2->set_colour( $config['bar2'] );
				$bar2->set_on_show(new bar_on_show($config['animation_2'], $config['cascade_2'], $config['delay_2']));
				$bar2->set_key(JText::_('COM_SPORTSMANAGEMENT_STATS_AWAY'), 12);

				$chart->add_element($bar1);
				$chart->add_element($bar2);
			}
		}
		// total
		$d = new $config['dotstyle_3']();
		$d->size((int)$config['line3_dot_strength']);
		$d->halo_size(1);
		$d->colour($config['line3']);
		$d->tooltip(JText::_('COM_SPORTSMANAGEMENT_STATS_TOTAL2').' #val#');

		$line = new line();
		$line->set_default_dot_style($d);
		$line->set_values( $matchDayGoalsCount );
		$line->set_width( (int) $config['line3_strength'] );
		$line->set_key(JText::_('COM_SPORTSMANAGEMENT_STATS_TOTAL'), 12);
		$line->set_colour( $config['line3'] );
		$line->on_show(new line_on_show($config['l_animation_3'], $config['l_cascade_3'], $config['l_delay_3']));
		$chart->add_element($line);


		$x = new x_axis();
		$x->set_colours($config['x_axis_colour'], $config['x_axis_colour_inner']);
		$x->set_labels_from_array($round_labels);
		$chart->set_x_axis( $x );
		$x_legend = new x_legend( JText::_('COM_SPORTSMANAGEMENT_STATS_ROUNDS') );
		$x_legend->set_style( '{font-size: 15px; color: #778877}' );
		$chart->set_x_legend( $x_legend );

		$y = new y_axis();
		$y->set_range( 0, @max($matchDayGoalsCount)+2, 1);
		$y->set_steps(round(@max($matchDayGoalsCount)/8));
		$y->set_colours($config['y_axis_colour'], $config['y_axis_colour_inner']);
		$chart->set_y_axis( $y );
		$y_legend = new y_legend( JText::_('COM_SPORTSMANAGEMENT_STATS_GOALS') );
		$y_legend->set_style( '{font-size: 15px; color: #778877}' );
		$chart->set_y_legend( $y_legend );

		$this->assignRef( 'chartdata',  $chart);
	}
}
?>