<?php defined( '_JEXEC' ) or die( 'Restricted access' );

$columns		= explode( ',', $this->config['ordered_columns'] );
$column_names	= explode( ',', $this->config['ordered_columns_names'] );
?>
<thead>
	<tr class="sectiontableheader">
		<th class="rankheader" colspan="3">
			<?php JoomleagueHelperHtml::printColumnHeadingSort( JText::_( 'COM_JOOMLEAGUE_RANKING_POSITION' ), "rank", $this->config, "ASC" ); ?>
		</th>
		
		<?php
		if ( $this->config['show_logo_small_table'] != "no_logo" )
		{
			echo '<th style="text-align: center">&nbsp;</th>';
		}
		?>
		
		<th class="teamheader">	
			<?php JoomleagueHelperHtml::printColumnHeadingSort( JText::_( 'COM_JOOMLEAGUE_RANKING_TEAM' ), "name", $this->config, "ASC" ); ?>
		</th>
		
<?php
	foreach ( $columns as $k => $column )
	{
		if (empty($column_names[$k])){$column_names[$k]='???';}
		
		$c=strtoupper(trim($column));
		$c="COM_JOOMLEAGUE_".$c;		

		$toolTipTitle=$column_names[$k];
		$toolTipText=JText::_($c);		
		
		switch ( trim( strtoupper( $column ) ) )	
		{
			case 'PLAYED':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';			
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "played", $this->config );
				echo '</th>';
				break;

			case 'WINS':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';						
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "won", $this->config );
				echo '</th>';
				break;

			case 'TIES':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "draw", $this->config );
				echo '</th>';
				break;

			case 'LOSSES':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "loss", $this->config );
				echo '</th>';
				break;

			case 'WOT':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "wot", $this->config );
				echo '</th>';
				break;

			case 'WSO':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "wso", $this->config );
				echo '</th>';
				break;

			case 'LOT':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "lot", $this->config );
				echo '</th>';
				break;

			case 'LSO':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "lso", $this->config );
				echo '</th>';
				break;
				
			case 'WINPCT':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "winpct", $this->config );
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
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "legsdiff", $this->config );
				echo '</th>';
				break;

			case 'LEGS_RATIO':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "legsratio", $this->config );
				echo '</th>';
				break;				
				
			case 'SCOREFOR':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "goalsfor", $this->config );
				echo '</th>';
				break;				
				
			case 'SCOREAGAINST':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "goalsagainst", $this->config );
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
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "goalsp", $this->config );
				echo '</th>';
				break;

			case 'DIFF':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "diff", $this->config );
				echo '</th>';
				break;

			case 'POINTS':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "points", $this->config );
				echo '</th>';
				break;
                
            case 'PENALTYPOINTS':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "penaltypoints", $this->config );
				echo '</th>';
				break;    

			case 'NEGPOINTS':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "negpoints", $this->config );
				echo '</th>';
				break;

			case 'OLDNEGPOINTS':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "negpoints", $this->config );
				echo '</th>';
				break;
				
			case 'POINTS_RATIO':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "pointsratio", $this->config );
				echo '</th>';
				break;				

			case 'BONUS':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "bonus", $this->config );
				echo '</th>';
				break;

			case 'START':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "start", $this->config );
				echo '</th>';
				break;

			case 'QUOT':
				echo '<th class="headers">';
				echo '<span class="hasTip" title="'.$toolTipTitle.'::'.$toolTipText.'">';	
				JoomleagueHelperHtml::printColumnHeadingSort( $column_names[$k], "quot", $this->config );
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