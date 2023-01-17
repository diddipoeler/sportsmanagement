<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage ranking
 * @file       deafult_ranking_away.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
?>

<!-- Main START -->
<a name="jl_top" id="jl_top"></a>

<!-- content -->
<?php
foreach ($this->awayRank as $division => $cu_rk)
{
	if ($division)
	{
		?>
        <div class="<?php echo $this->divclassrow; ?> table-responsive">
            <table class="<?PHP echo $this->config['table_class']; ?>">
                <tr>
                    <td class="contentheading">
						<?php
						// Get the division name from the first team of the division
						foreach ($cu_rk as $ptid => $team)
						{
							echo $this->divisions[$division]->name;
							break;
						}
						?>
                    </td>
                </tr>
            </table>
        </div>
        <div class="<?php echo $this->divclassrow; ?> table-responsive">
            <table class="<?PHP echo $this->config['table_class']; ?>">
				<?php
				foreach ($cu_rk as $ptid => $team)
				{
					echo $this->loadTemplate('rankingheading');
					break;
				}

				$this->division = $division;
				$this->current  = &$cu_rk;
				$this->teamrow  = 'ar';
				echo $this->loadTemplate('rankingrows');
				?>
            </table>
        </div>
		<?php
	}
	else
	{
		?>
        <div class="table-responsive">
            <table class="<?PHP echo $this->config['table_class']; ?>">
				<?php
				echo $this->loadTemplate('rankingheading');
				$this->division = $division;
				$this->current  = &$cu_rk;
				$this->teamrow  = 'ar';
				echo $this->loadTemplate('rankingrows');
				?>
            </table>
        </div>
        <br/>
		<?php
	}
}
?>
<!-- ranking END -->



