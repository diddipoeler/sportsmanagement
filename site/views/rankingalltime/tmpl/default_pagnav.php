<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rankingalltime
 * @file       default_pagenav.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
?>
<!-- matchdays pageNav -->
<br />
<table width='96%' align='center' cellpadding='0' cellspacing='0' border='0'>
	<tr>
		<td>
	<?php
	if (!empty($this->rounds))
	{
		$pageNavigation  = "<div class='pagenav'>";
		$pageNavigation .= sportsmanagementPagination::pagenav($this->project);
		$pageNavigation .= "</div>";
		echo $pageNavigation;
	}
	?>
		</td>
	</tr>
</table>
<!-- matchdays pageNav END -->
