<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_ranking.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage rankingalltime
 */

defined( '_JEXEC' ) or die( 'Restricted access' );?>
<div class="<?php echo $this->divclassrow;?> table-responsive">
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
    </div>
<!-- ranking END -->



