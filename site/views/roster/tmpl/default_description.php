<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_description.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage roster
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>

	<?php
	// Show team-description if defined.
	if ( !isset ( $this->projectteam->notes ) )
	{
		$description  = "";
	}
	else
	{
		$description  = $this->projectteam->notes;
	}

	if( trim( $description  != "" ) )
	{
		?>
		<br />
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr class="sectiontableheader">
				<th>
					<?php
					echo '&nbsp;' . Text::_( 'COM_SPORTSMANAGEMENT_ROSTER_TEAMINFORMATION' );
					?>
				</th>
			</tr>
		</table>

		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td>
					<?php
					$description = HTMLHelper::_('content.prepare', $description);
					echo stripslashes( $description );
					?>
				</td>
			</tr>
		</table>
		<?php
	}
	?>
	<br />