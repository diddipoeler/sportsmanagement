<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_results_style3.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @subpackage results
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$nbcols		= 5;
$dates		= $this->sortByDate($this->matches);
$nametype = $this->config['names'];

if($this->config['show_match_number']){$nbcols++;}
if($this->config['show_events']){$nbcols++;}
if(($this->config['show_playground'] || $this->config['show_playground_alert'])){$nbcols++;}
if($this->config['show_referee']){$nbcols++;}

?>
<div class="row-fluid">  
<table class="<?PHP echo $this->config['table_class']; ?> table-responsive">
	<tr>
		<td>
			May be designed in the future???
		</td>
	</tr>
</table>
</div>
