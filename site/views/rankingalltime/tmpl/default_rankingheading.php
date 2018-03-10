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

defined( '_JEXEC' ) or die( 'Restricted access' );

$columns		= explode( ',', $this->config['ordered_columns'] );
$column_names	= explode( ',', $this->config['ordered_columns_names'] );
?>
<thead>
	<tr class="sectiontableheader">
		<th class="rankheader" colspan="3">
			<?php sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( JText::_( 'COM_SPORTSMANAGEMENT_RANKING_POSITION' ), "rank", $this->config, "ASC" ); ?>
		</th>
		
		<?php
		if ( $this->config['show_logo_small_table'] != "no_logo" )
		{
			echo '<th style="text-align: center">&nbsp;</th>';
		}
		?>
		
		<th class="teamheader">	
			<?php sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( JText::_( 'COM_SPORTSMANAGEMENT_RANKING_TEAM' ), "name", $this->config, "ASC" ); ?>
		</th>
		
<?php
	foreach ( $columns as $k => $column )
	{
		if (empty($column_names[$k])){$column_names[$k]='???';}
		
		$c=strtoupper(trim($column));
		$c="COM_SPORTSMANAGEMENT_".$c;		

		$toolTipTitle=$column_names[$k];
		$toolTipText=JText::_($c);		
		
		switch ( trim( strtoupper( $column ) ) )	
		{
			case 'PLAYED':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';			
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "played", $this->config );
				echo '</th>';
				break;

			case 'WINS':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';						
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "won", $this->config );
				echo '</th>';
				break;

			case 'TIES':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "draw", $this->config );
				echo '</th>';
				break;

			case 'LOSSES':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "loss", $this->config );
				echo '</th>';
				break;

			case 'WOT':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "wot", $this->config );
				echo '</th>';
				break;

			case 'WSO':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "wso", $this->config );
				echo '</th>';
				break;

			case 'LOT':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "lot", $this->config );
				echo '</th>';
				break;

			case 'LSO':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "lso", $this->config );
				echo '</th>';
				break;
				
			case 'WINPCT':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "winpct", $this->config );
				echo '</th>';
				break;

			case 'GB':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				echo $column_names[$k];
				echo '</th>';
				break;

			case 'LEGS':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				echo $column_names[$k];
				echo '</th>';
				break;

			case 'LEGS_DIFF':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "legsdiff", $this->config );
				echo '</th>';
				break;

			case 'LEGS_RATIO':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "legsratio", $this->config );
				echo '</th>';
				break;				
				
			case 'SCOREFOR':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "goalsfor", $this->config );
				echo '</th>';
				break;				
				
			case 'SCOREAGAINST':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "goalsagainst", $this->config );
				echo '</th>';
				break;

			case 'SCOREPCT':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				echo $column_names[$k];
				echo '</th>';
				break;
				
			case 'RESULTS':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "goalsp", $this->config );
				echo '</th>';
				break;

			case 'DIFF':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "diff", $this->config );
				echo '</th>';
				break;

			case 'POINTS':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "points", $this->config );
				echo '</th>';
				break;

			case 'NEGPOINTS':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "negpoints", $this->config );
				echo '</th>';
				break;

			case 'OLDNEGPOINTS':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "negpoints", $this->config );
				echo '</th>';
				break;
				
			case 'POINTS_RATIO':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "pointsratio", $this->config );
				echo '</th>';
				break;				

			case 'BONUS':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "bonus", $this->config );
				echo '</th>';
				break;

			case 'START':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "start", $this->config );
				echo '</th>';
				break;

			case 'QUOT':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				sportsmanagementHelperHtml::printColumnHeadingSortAllTimeRanking( $column_names[$k], "quot", $this->config );
				echo '</th>';
				break;

			case 'TADMIN':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				echo $column_names[$k];
				echo '</th>';
				break;

			case 'GFA':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				echo $column_names[$k];
				echo '</th>';
				break;

			case 'GAA':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				echo $column_names[$k];
				echo '</th>';
				break;
				
			case 'PPG':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';
				echo $column_names[$k];
				echo '</th>';
				break;				
				
			case 'PPP':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				echo $column_names[$k];
				echo '</th>';
				break;	
				
			case 'LASTGAMES':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				echo $column_names[$k];
				echo '</th>';
				break;		
				
			default:
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				echo JText::_($column);
				echo '</th>';
				break;
		}
	}
?>
	</tr>
</thead>