<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage roster
 * @file       default_players_johncage.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

$totalEvents = array();
if (count($this->rows) > 0)
{
	?>
    <div id="jl_rosterholder">
		<?php
		$position = "";
		foreach ($this->rows as $position_id => $rows)
		{
			$position = Text::_($rows[0]->position);
			?>
            <div class="jl_rosterposition sectiontableheader">
				<?php
				echo $position;
				?>
            </div>
			<?php
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$k = $i % 2;
				if ($k == 0)
				{
					?>
                    <div class="jl_rosterpersonrow">
					<?php
				}
				$row       = &$rows[$i];
				$this->row = $row;
				$this->k   = $k;
				echo $this->loadTemplate('person_player');
				if ($k == 1 OR !isset($rows[$i + 1]))
				{
					?>
                    </div><!-- /.jl_rosterpersonrow -->
					<?php
				}
			}// for ($i=0, $n=count( $rows ); $i < $n; $i++) ends
		}//foreach ($this->rows as $position_id => $rows) ends
		?>
    </div><!-- /#jl_rosterholder -->
	<?php
}//if (count($this->rows>0)) ends
?>
