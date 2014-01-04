<?php defined( '_JEXEC' ) or die( 'Restricted access' );

$nbcols		= 5;
$dates		= $this->sortByDate();
$nametype = $this->config['names'];

if($this->config['show_match_number']){$nbcols++;}
if($this->config['show_events']){$nbcols++;}
if(($this->config['show_playground'] || $this->config['show_playground_alert'])){$nbcols++;}
if($this->config['show_referee']){$nbcols++;}

?>
<table class="fixtures-results" border='1'>
	<tr>
		<td>
			May be designed in the future???
		</td>
	</tr>
</table><br />