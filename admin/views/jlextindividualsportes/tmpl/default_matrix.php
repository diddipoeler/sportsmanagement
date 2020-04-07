<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_matrix.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage jlextindividualsportes
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>

<!--[if IE]>
   <style>
	  .rotate_text
	  {
		 writing-mode: tb-rl;
		 filter: flipH() flipV();
	  }
	  .rotated_cell
	  {
		 height:400px;
		 background-color: grey;
		 color: white;
		 padding-bottom: 3px;
		 padding-left: 5px;
		 padding-right: 5px;
		 white-space:nowrap;
		 vertical-align:bottom
	  }
   </style>
<![endif]-->

<!--[if !IE]><!-->
<style>
.rotate_text
	  {
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

	  .rotated_cell
	  {
		 height:400px;
		 background-color: grey;
		 color: white;
		 padding-bottom: 3px;
		 padding-left: 5px;
		 padding-right: 5px;
		 white-space:nowrap;
		 vertical-align:bottom
	  }
   </style>
<!--<![endif]-->







<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&id=' . $this->match_id . '&team1=' . $this->projectteam1_id . '&team2=' . $this->projectteam2_id . '&rid=' . $this->rid);?>" method="post" name="matrixForm" id="matrixForm">

<fieldset class="adminform"><legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_SINGLE_MATCHES_TITLE', '<i>' . $this->roundws->name . '</i>', '<i>' . $this->projectws->name . '</i>'); ?></legend>
<fieldset class="adminform">
	<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MATRIX_HINT'); ?>
</fieldset>
<?php

// $model =& $this->getModel();
$teams = $this->ProjectTeams;

$homeplayer = $this->getHomePlayer;
$awayplayer = $this->getAwayPlayer;

$matrix = '';

// If (count($homeplayer) <= 20  && $homeplayer )
// {
	$matrix = "<table width=\"100%\" class=\"adminlist\">";

	$k = 0;

for ($rows = 0; $rows <= count($homeplayer); $rows++)
{
	if ($rows == 0)
	{
		$trow = $homeplayer[0];
	}
	else
	{
		$trow = $homeplayer[$rows - 1];
	}

	$matrix .= "<tr class=\"row$k\">";

	for ($cols = 0; $cols <= count($awayplayer); $cols++)
	{
		$text = '';
		$checked = '';
		$color = 'white';

		if ($cols == 0)
		{
			$tcol = $awayplayer[0];
		}
		else
		{
			$tcol = $awayplayer[$cols - 1];
		}

		$match = $trow->value . '_' . $tcol->value;
		$onClick = sprintf("onClick=\"javascript:SaveMatch('%s','%s');\"", $trow->value, $tcol->value);

		if ($rows == 0 && $cols == 0)
		{
			$text = "<th align=\"center\"></th>";
		}
		elseif ($rows == 0)
		{
			$text = sprintf("<th width=\"200\" class=\"rotated_cell\" align=\"center\" title=\"%s\"><div class='rotate_text'>%s</div></th>", $tcol->text, $tcol->text); // Picture columns
		}
		elseif ($cols == 0)
		{
			$text = sprintf("<td align=\"left\" nowrap>%s</td>", $trow->text); // Named rows
		}
		// Else if($rows == $cols) $text = "<td align=\"center\"><input type=\"radio\" DISABLED></td>"; //impossible matches

		else
		{
			if (count($this->matches) > 0)
			{
				for ($i = 0,$n = count($this->matches); $i < $n; $i++)
				{
					$row =& $this->matches[$i];

					if ($row->teamplayer1_id == $trow->value
						&& $row->teamplayer2_id == $tcol->value
					)
					{
							$checked = 'checked';
							$color = 'teal';
							$onClick = '';
							break;
					}
					else
					{
						$checked = '';
						$color = 'white';
						$onClick = sprintf("onClick=\"javascript:SaveMatch('%s','%s');\"", $trow->value, $tcol->value);
					}
				}
			}

			$text = sprintf("<td align=\"center\" title=\"%s - %s\" bgcolor=\"%s\"><input type=\"radio\" name=\"match_%s\" %s %s></td>\n", $trow->text, $tcol->text, $color, $trow->value . $tcol->value, $onClick, $checked);
		}

		$matrix .= $text;
	}

	$k = 1 - $k;
}

	$matrix .= "</table>";

// }

// Show the matrix
echo $matrix;
?></fieldset>
<?php $dValue = $this->roundws->round_date_first . ' ' . $this->projectws->start_time; ?>
<input type='hidden' name='match_date' value='<?php echo $dValue; ?>' />
<input type='hidden' name='projectteam1_id' value='<?php echo $this->projectteam1_id; ?>' />
<input type='hidden' name='projectteam2_id' value='<?php echo $this->projectteam2_id; ?>' />

<input type='hidden' name='match_id' value='<?php echo $this->match_id; ?>' />
<input type='hidden' name='teamplayer1_id' value='' />
<input type='hidden' name='teamplayer2_id' value='' />

<input type='hidden' name='published' value='1' />
<input type='hidden' name='task' value='jlextindividualsport.addmatch' />
<?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>
