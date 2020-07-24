<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_clubicons
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

?>

<style>
.img-height {
	width: auto;
	height: <?php echo $params->get('picture_height', '30'); ?>;
}	
</style>	

<table id="clubicons<?php echo $module->id; ?>" class="<?php echo $params->get('table_class', 'table'); ?>">
    <tr>
		<?php
		$cnt   = 0;
		$after = 0;
		$rest  = count($data);
		foreach ($data->ranking AS $k => $value)
		{
		$val    = $data->teams[$k];
		$append = ($params->get('teamlink', 0) == 5 AND $params->get('newwindow', 0) == 1) ?
			' target="_blank"' : '';
		?>
        <td class="">
			<?PHP
			if (!empty($val['link']))
			{
				echo '<a href="' . $val['link'] . '"' . $append . '>';
			}
			echo $val['logo'];
			if (!empty($val['link']))
			{
				echo '</a>';
			}
			?>
        </td>
		<?PHP
		$cnt++;
		$modulo = intval($cnt % $params->get('iconsperrow', 20));
		if ($modulo == 0)
		{
		?>
    </tr>
    <tr>
		<?PHP
		}


		}
		?>
    </tr>
</table>
