<?php defined( '_JEXEC' ) or die( 'Restricted access' );

if ( !isset ( $this->project ) )
{
	JError::raiseWarning( 'ERROR_CODE', JText::_( 'Error: ProjectID was not submitted in URL or selected project was not found in database!' ) );
}
else
{
	/*
	// only for testing purposes. Should be marked out in public revision
	$this->config['show_match_number']=1;
	$this->config['show_events']=1;
	$this->config['show_division']=1;
	$this->config['switch_home_guest']=1;
	$this->config['show_time']=1;
	$this->project->fav_team_color='blue';
	$this->project->fav_team_text_color='#FFFFFF';
	$this->config['show_playground']=1;
	$this->config['show_playground_alert']=1;
	$this->config['show_referee']=1;
	$this->config['show_dnp_teams']=1;
	$this->config['show_referee']=1;

	$this->config['result_style'] = 1;
	*/
	?>
	<div id="jlg_ranking_table" align="center">
		<br />
		<?php
		if ( count( $this->matches ) > 0 )
		{
			switch ( $this->config['result_style'] )
			{
				case 4:
							{
								echo $this->loadTemplate('results_style_dfcday');
							}
							break;
        case 3:
							{
								echo $this->loadTemplate('results_style3');
							}
							break;

				case 0:
				case 1:
				case 2:				
				default:
							{
								echo $this->loadTemplate('results_style0');
							}
							break;
			}
		}
		?>
	</div>
	<!-- Main END -->
	<?php
	if ($this->config['show_dnp_teams']) { echo $this->loadTemplate('freeteams'); }
}
?>