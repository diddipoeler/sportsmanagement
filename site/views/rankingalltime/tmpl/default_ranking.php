<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_ranking.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage rankingalltime
 */

defined( '_JEXEC' ) or die( 'Restricted access' );?>

<!-- Main START -->
<a name="jl_top" id="jl_top"></a>

<!-- content -->
<?php
foreach ( $this->currentRanking as $division => $cu_rk )
{
	if ($division)
	{
	?>
	<table class="table">
		<tr>
			<td class="contentheading">
				<?php
					//get the division name from the first team of the division 
					foreach( $cu_rk as $ptid => $team )
					{
						echo $this->divisions[$division]->name;
						break;
					}
				?>
			</td>
		</tr>
	</table>

	<table class="table">
	<?php
		foreach( $cu_rk as $ptid => $team )
		{
			//echo $this->loadTemplate('rankingheading');
			break;
		}
		$this->division = $division;
		$this->current  = &$cu_rk;
		echo $this->loadTemplate('rankingrows');
	?>
	</table>
	<?php
	}
	else
	{
	?>
	<table class="table">
		<?php
			echo $this->loadTemplate('rankingheading');
			$this->division = $division;
			$this->current  = &$cu_rk;
			echo $this->loadTemplate('rankingrows');
		?>
	</table>
	<br />
	<?php
	}
}
	?>
<!-- ranking END -->



