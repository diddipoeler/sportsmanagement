<?php 
defined( '_JEXEC' ) or die( 'Restricted access' ); 
?>

<!-- section header e.g. ranking, results etc. -->
<a id="jl_top"></a>

<table class="contentpaneopen">
	<tr>
		<td class="contentheading">
		<?php
		if ( $this->roundid)
		{
			$title = JText::_( 'COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS' );
			if ( isset( $this->division ) )
			{
				$title = JText::sprintf( 'COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS2', '<i>' . $this->division->name . '</i>' );
			}
			sportsmanagementHelperHtml::showMatchdaysTitle(	$title, $this->roundid, $this->config );

			if ( $this->showediticon )
			{
				$link = sportsmanagementHelperRoute::getResultsRoute( $this->project->id, $this->roundid, $this->model->divisionid, $this->model->mode, $this->model->order, $this->config['result_style_edit'] );
				$imgTitle = JText::_( 'COM_SPORTSMANAGEMENT_RESULTS_ENTER_EDIT_RESULTS' );
				$desc = JHTML::image( 'media/com_joomleague/jl_images/edit.png', $imgTitle, array( ' title' => $imgTitle ) );
				echo ' ';
				echo JHTML::link( $link, $desc );
			}
		}
		else
		{
			//1 request for current round
			// seems to be this shall show a plan of matches of a team???
			sportsmanagementHelperHtml::showMatchdaysTitle( JText::_( 'COM_SPORTSMANAGEMENT_RESULTS_PLAN' ) , 0, $this->config );
		}
		?>
		</td>
			<?php if ($this->config['show_matchday_dropdown']==1) { ?>
	            <td class="contentheading" style="text-align:right; font-size: 100%;">
			<?php echo sportsmanagementHelperHtml::getRoundSelectNavigation(FALSE); ?>
				</td>
    	    <?php } ?>
		</tr>
</table>