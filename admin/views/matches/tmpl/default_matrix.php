<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matches
 * @file       defaul_matrix.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

?>

<!--[if IE]>
<style>
    .rotate_text {
        writing-mode: tb-rl;
        filter: flipH() flipV();
    }

    .rotated_cell {
        height: 400px;
        background-color: grey;
        color: white;
        padding-bottom: 3px;
        padding-left: 5px;
        padding-right: 5px;
        white-space: nowrap;
        vertical-align: bottom
    }
</style>
<![endif]-->

<!--[if !IE]><!-->
<style>
    .rotate_text {
        text-align: center;
        vertical-align: middle;
        width: 20px;
        margin: 0px;
        padding: 0px;
        padding-left: 3px;
        padding-right: 3px;
        padding-top: 10px;
        white-space: nowrap;
        -webkit-transform: rotate(-90deg);
        -moz-transform: rotate(-90deg);
        -o-transform: rotate(-90deg);
    }

    .rotated_cell {
        height: 400px;
        background-color: grey;
        color: white;
        padding-bottom: 3px;
        padding-left: 5px;
        padding-right: 5px;
        white-space: nowrap;
        vertical-align: bottom
    }
</style>
<!--<![endif]-->

<div class="clr"></div>
<form method="post" name="matrixForm" id="matrixForm">

    <!--<fieldset class="adminform">-->
    <legend>
		<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MATRIX_TITLE'); ?>
    </legend>
    <!--<fieldset class="adminform">-->
	<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MATRIX_HINT'); ?>
    <!--</fieldset>-->
	<?php

	$mdlProject = BaseDatabaseModel::getInstance("Projectteams", "sportsmanagementModel");
	$teams      = $mdlProject->getProjectTeams($this->project_id);
	$matrix     = '';

	// Diddipoeler
	// warum <= 20 blödsinn
	// kann man anders regeln
	// if (count($teams) <= 200) {
	$matrix = "<table width=\"100%\" class=\"adminlist\">";

	$k = 0;

	for ($rows = 0; $rows <= count($teams); $rows++)
	{
		if ($rows == 0)
		{
			$trow = $teams[0];
		}
		else
		{
			$trow = $teams[$rows - 1];
		}

		$matrix .= "<tr class=\"row$k\">";

		for ($cols = 0; $cols <= count($teams); $cols++)
		{
			$text    = '';
			$checked = '';
			$color   = 'white';

			if ($cols == 0)
			{
				$tcol = $teams[0];
			}
			else
			{
				$tcol = $teams[$cols - 1];
			}

			$match   = $trow->value . '_' . $tcol->value;
			$onClick = sprintf("onClick=\"javascript:SaveMatch('%s','%s');\"", $trow->value, $tcol->value);

			if ($rows == 0 && $cols == 0)
			{
				$text = "<th align=\"center\"></th>";
			}
            elseif ($rows == 0)
			{
				$text = sprintf("<th width=\"20\" class=\"rotated_cell\" align=\"center\" title=\"%s\"><div class='rotate_text'>%s</div></th>", $tcol->text, $tcol->text); // Picture columns
			}
            elseif ($cols == 0)
			{
				$text = sprintf("<td align=\"left\" nowrap>%s</td>", $trow->text); // Named rows
			}
            elseif ($rows == $cols)
			{
				$text = "<td align=\"center\"><input type=\"radio\" DISABLED></td>"; // Impossible matches
			}
			else
			{
				if (count($this->matches) > 0)
				{
					for ($i = 0, $n = count($this->matches); $i < $n; $i++)
					{
						$row =& $this->matches[$i];

						if ($row->projectteam1_id == $trow->value
							&& $row->projectteam2_id == $tcol->value
						)
						{
							$checked = 'checked';
							$color   = 'teal';
							$onClick = '';
							break;
						}
						else
						{
							$checked = '';
							$color   = 'white';
							$onClick = sprintf("onClick=\"javascript:SaveMatch('%s','%s');\"", $trow->value, $tcol->value);
						}
					}
				}

				$text = sprintf("<td width=\"20\" align=\"center\" title=\"%s - %s\" bgcolor=\"%s\"><input type=\"radio\" name=\"match_%s\" %s %s></td>\n", $trow->text, $tcol->text, $color, $trow->value . $tcol->value, $onClick, $checked);
			}

			$matrix .= $text;
		}

		$k = 1 - $k;
	}

	$matrix .= "</table>";

	// }

	// Show the matrix
	echo $matrix;
	?>
    <!--</fieldset>-->
	<?php $dValue = $this->roundws->round_date_first . ' ' . $this->projectws->start_time; ?>
    <input type='hidden' name='match_date' value='<?php echo $dValue; ?>'/>
    <input type='hidden' name='projectteam1_id' value=''/>
    <input type='hidden' name='projectteam2_id' value=''/>
    <input type='hidden' name='published' value='1'/>
    <input type='hidden' name='task' value='match.addmatch'/>
	<?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>
