<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_count_rekord
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

?>
<div class="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
	<?PHP
	$list = modJSMStatistikRekordHelper::getData($params, $module);

	?>


    <table class="table">

		<?PHP

		if ($list)
		{
			foreach ($list as $row)
			{
				?>
                <tr>
                    <td width="50%" align="left"><?PHP echo $row->text; ?></td>
                </tr>
				<?PHP
			}
		}

		?>

    </table>
</div>
