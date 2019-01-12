<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_colorlegend.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage rankingalltime
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<!-- colors legend START -->
<?php
	if (!isset($this->tableconfig['show_colors_legend'])){$this->tableconfig['show_colors_legend']=1;}
	if ($this->tableconfig['show_colors_legend'])
	{
		?>
		<br />
		<table width='96%' align='center' cellpadding='0' cellspacing='0' border='0'>
			<tr>
				<?php
				JoomleagueHelper::showColorsLegend($this->colors);
				?>
			</tr>
		</table>
		<?php
	}
?>
<!-- colors legend END -->