<?php defined( '_JEXEC' ) or die( 'Restricted access' );

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('projectheading', 'backbutton', 'footer');
JoomleagueHelper::addTemplatePaths($templatesToLoad, $this);

if ( !isset( $this->config['show_referees'] ) )
{
	$this->config['show_referees'] = 1;
}
?>
<div class="joomleague">
	<?php
	echo $this->loadTemplate( 'projectheading' );

	if ( $this->config['show_sectionheader'] == 1 )
	{
		echo $this->loadTemplate('sectionheader');
	}

	if ( $this->config['show_referees'] == 1 )
	{
		echo $this->loadTemplate( 'referees' );
	}

	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>