<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

require_once(JPATH_SITE.DS.JSM_PATH.DS.'assets'.DS.'classes'.DS.'open-flash-chart'.DS.'open-flash-chart.php' );

/**
 * sportsmanagementViewCurve
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewCurve extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewCurve::init()
	 * 
	 * @return void
	 */
	function init()
	{

		$js = $this->baseurl . '/components/'.$this->option.'/assets/js/json2.js';
		$this->document->addScript($js);
		$js = $this->baseurl . '/components/'.$this->option.'/assets/js/swfobject.js';
		$this->document->addScript($js);

		$rankingconfig = sportsmanagementModelProject::getTemplateConfig( "ranking",sportsmanagementModelCurve::$cfg_which_database );
		$flashconfig = sportsmanagementModelProject::getTemplateConfig( "flash",sportsmanagementModelCurve::$cfg_which_database );

		$this->season_id = sportsmanagementModelCurve::$season_id;
		$this->cfg_which_database = sportsmanagementModelCurve::$cfg_which_database;
		
		if ( isset( $this->project ) )
		{
			$teamid1 = sportsmanagementModelCurve::$teamid1;
			$teamid2 = sportsmanagementModelCurve::$teamid2;
			$options = array(	JHtml::_( 'select.option', '0', JText::_('COM_SPORTSMANAGEMENT_CURVE_CHOOSE_TEAM') ) );
			$divisions = sportsmanagementModelProject::getDivisions(0,sportsmanagementModelCurve::$cfg_which_database);
			if (count($divisions)>0 && $division == 0)
			{
				foreach ($divisions as $d)
				{
					$options = array();
					$teams = sportsmanagementModelProject::getTeams($d->id,'name',sportsmanagementModelCurve::$cfg_which_database);
					$i=0;
					foreach ((array) $teams as $t) {
						$options[] = JHtml::_( 'select.option', $t->id, $t->name );
						if($i==0) {
							$teamid1 = $t->id;
						}
						if($i==1) {
							$teamid2 = $t->id;
						}
						$i++;
					}
					$team1select[$d->id] = JHtml::_('select.genericlist', $options, 'tid1_'.$d->id, 'onchange="reload_curve_chart_'.$d->id.'()" class="inputbox" style="font-size:9px;"','value', 'text', $teamid1);
					$team2select[$d->id] = JHtml::_('select.genericlist', $options, 'tid2_'.$d->id, 'onchange="reload_curve_chart_'.$d->id.'()" class="inputbox" style="font-size:9px;"','value', 'text', $teamid2);
				}
			}
			else
			{
				$divisions = array();
				$team1select = array();
				$team2select = array();
				$div = $this->model->getDivision(sportsmanagementModelCurve::$division);
				if(empty($div)) {
					$div = new stdClass();
					$div->id = 0;
					$div->name = '';
				}
				$divisions[0] = $div;
				$teams = sportsmanagementModelProject::getTeams(sportsmanagementModelCurve::$division,'name',sportsmanagementModelCurve::$cfg_which_database);
                
				$i=0;
				foreach ((array) $teams as $t) {
					$options[] = JHtml::_( 'select.option', $t->id, $t->name );
					if( $i == 0 && $teamid1 == 0 ) 
                    {
						//$teamid1 = $t->id;
                        $teamid1 = $t->team_id;
					}
					if( $i == 1 && $teamid2 == 0 ) {
						//$teamid2 = $t->id;
                        $teamid2 = $t->team_id;
					}
					$i++;
				}
				$team1select[$div->id] = JHtml::_('select.genericlist', $options, 'tid1_'.$div->id, 'onchange="reload_curve_chart_'.$div->id.'()" class="inputbox" style="font-size:9px;"','value', 'text', $teamid1);
				$team2select[$div->id] = JHtml::_('select.genericlist', $options, 'tid2_'.$div->id, 'onchange="reload_curve_chart_'.$div->id.'()" class="inputbox" style="font-size:9px;"','value', 'text', $teamid2);		
			}

			if ( !isset( $this->overallconfig['seperator'] ) )
			{
				$this->overallconfig['seperator'] = ":";
			}

			$this->colors = sportsmanagementModelProject::getColors($rankingconfig['colors'],sportsmanagementModelCurve::$cfg_which_database);
			$this->divisions = $divisions;
			$this->division = $this->model->getDivision(sportsmanagementModelCurve::$division);
			$this->favteams = sportsmanagementModelProject::getFavTeams(sportsmanagementModelCurve::$cfg_which_database);
			$this->team1 = $this->model->getTeam1(sportsmanagementModelCurve::$division);
			$this->team2 = $this->model->getTeam2(sportsmanagementModelCurve::$division);
			$this->allteams = sportsmanagementModelProject::getTeams(sportsmanagementModelCurve::$division,'name',sportsmanagementModelCurve::$cfg_which_database);
			$this->team1select = $team1select;
			$this->team2select = $team2select;
			$this->_setChartdata(array_merge($flashconfig, $rankingconfig));
			// Set page title
			$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_CURVE_PAGE_TITLE' );
			if (( isset( $this->team1 ) ) AND (isset( $this->team1 )))
			{
				//$pageTitle .= ": ".$this->team1->name." - ".$this->team2->name;
			}
			$this->document->setTitle( $pageTitle );
		}

	}

	/**
	 * assign the chartdata object for open flash chart library
	 * @param $config
	 * @return unknown_type
	 */
	function _setChartdata($config)
	{
	// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
	   $option = $jinput->getCmd('option');
        
		$model = $this->getModel();
		$rounds	= sportsmanagementModelProject::getRounds('ASC',$model::$cfg_which_database);
		$round_labels = array();
		foreach ($rounds as $r) 
        {
			$round_labels[] = $r->name;
		}

		$divisions	= $this->divisions;

		//create a line
		$length = (count($rounds)-0.5);
		$linewidth = $config['color_legend_line_width'];
		$lines = array();
		foreach ($divisions as $division)
		{
			$data = $model->getDataByDivision($division->id);
			$allteams = sportsmanagementModelProject::getTeams($division->id,'name',$model::$cfg_which_database);
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' allteams<br><pre>'.print_r($allteams,true).'</pre>'),'');
            
			if(empty($allteams) || count($allteams)==0) continue;
			
			$chart = new open_flash_chart();
			//$title = $division->name;
			//$chart->set_title( $title );
			$chart->set_bg_colour($config['bg_colour']);

			//colors defined for ranking table lines
			//todo: add support for more than 2 lines
			foreach( $this->colors as $color )
			{
				foreach ( $rounds AS $r )
				{
					for ( $n = $color['from']; $n <= $color['to']; $n++ )
					{
						$lines[$color['color']][$n][] = $n;
					}
				}
			}
			//set lines on the graph
			foreach( $lines AS $key => $value )
			{
				foreach( $value AS $line =>$key2 )
				{
					$chart->add_element( hline($key,$length,$line,$linewidth) );
				}
			}
			$team1id = 0;
			//load team1, first team in the dropdown
			foreach ($allteams as $t) {
				if(empty($data[$t->projectteamid])) continue;
				$team = $data[$t->projectteamid];
				
				if(($t->division_id == $division->id 
				 	&& $t->team_id != $team1id 
				 	&& $model::$teamid1 == 0)
				 	|| ($model::$teamid1 != 0 && $model::$teamid1 == $t->team_id) 
				) {
					$team1id = $team->team_id;

					$d = new $config['dotstyle_1']();
					$d->size((int) $config['line1_dot_strength']);
					$d->halo_size(1);
					$d->colour($config['line1']);
					$d->tooltip('Rank: #val#');
	
					$line = new line();
					$line->set_default_dot_style($d);
					$line->set_values( $team->rankings );
					$line->set_width( (int) $config['line1_strength'] );
					$line->set_key($team->name, 12);
					$line->set_colour( $config['line1'] );
					$line->on_show(new line_on_show($config['l_animation_1'], $config['l_cascade_1'], $config['l_delay_1']));
					$chart->add_element($line);
					break;
				}
			}
			if($model::$teamid1!=0) {
				$team1id = $model::$teamid1;
			}
			//load team2, second team in the dropdown
			foreach ($allteams as $t) {
				if(empty($data[$t->projectteamid])) continue;
				$team = $data[$t->projectteamid];
				if(($t->division_id == $division->id 
				 	&& $t->team_id != $team1id 
				 	&& $model::$teamid2 == 0)
				 	|| ($model::$teamid2 != 0 && $model::$teamid2 == $t->team_id) 
				) {
					$d = new $config['dotstyle_2']();
					$d->size((int) $config['line2_dot_strength']);
					$d->halo_size(1);
					$d->colour($config['line2']);
					$d->tooltip('Rank: #val#');
	
					$line = new line();
					$line->set_default_dot_style($d);
					$line->set_values( $team->rankings );
					$line->set_width( (int) $config['line2_strength'] );
					$line->set_key($team->name, 12);
					$line->set_colour( $config['line2'] );
					$line->on_show(new line_on_show($config['l_animation_2'], $config['l_cascade_2'], $config['l_delay_2']));
					$chart->add_element($line);
					break;
				}
			}
				
			$x = new x_axis();
			if ($config['x_axis_label']==1)
			{
				$xlabels = new x_axis_labels();
				$xlabels->set_labels($round_labels);
				$xlabels->set_vertical();
			}
			$x->set_labels($xlabels);
			$x->set_colours($config['x_axis_colour'], $config['x_axis_colour_inner']);
			$chart->set_x_axis( $x );
			$x_legend = new x_legend( JText::_('COM_SPORTSMANAGEMENT_CURVE_ROUNDS') );
			$x_legend->set_style( '{font-size: 15px; color: #778877}' );
			$chart->set_x_legend( $x_legend );

			$y = new y_axis();
			$y->set_range( count($data), 1, -1);
			$y->set_colours($config['x_axis_colour'], $config['x_axis_colour_inner']);
			$chart->set_y_axis( $y );
			$y_legend = new y_legend( JText::_('COM_SPORTSMANAGEMENT_CURVE_RANK') );
			$y_legend->set_style( '{font-size: 15px; color: #778877}' );
			$chart->set_y_legend( $y_legend );

			if ( $division->id )
			{
			$this->chartdata_.$division->id = $chart;
			}
			else
			{
			$this->chartdata_0 = $chart;
			}
			unset($chart);
		}
	}
}

/**
 * hline()
 * 
 * @param mixed $color
 * @param mixed $length
 * @param mixed $ypoint
 * @param mixed $linewidth
 * @return
 */
function hline($color, $length, $ypoint, $linewidth)
{
	$hline = new shape( $color );
	$hline->append_value( new shape_point( -0.5, $ypoint) );
	$hline->append_value( new shape_point( -0.5, $ypoint + $linewidth ) );
	$hline->append_value( new shape_point( $length, $ypoint + $linewidth) );
	$hline->append_value( new shape_point( $length, $ypoint ) );
	return $hline;
}

?>
