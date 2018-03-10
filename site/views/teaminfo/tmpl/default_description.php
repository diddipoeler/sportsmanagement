<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      deafult_description.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teaminfo
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

	<?php
	// Show team-description if defined.
	if ( !isset ( $this->team->notes ) )
	{
		$description = "";
	}
	else
	{
		$description = $this->team->notes;
	}

	if( trim( $description != "" ) )
	{
		?>
<div class="description">
		<br />
		<table class="table">
			<tr class="sectiontableheader">
				<td>
					<?php
					echo '&nbsp;' . JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_TEAMINFORMATION' );
					?>
				</td>
			</tr>
		</table>

		<table class="table">
			<tr>
				<td>
					<?php
					$description = JHtml::_('content.prepare', $description);
					echo stripslashes( $description );
					?>
				</td>
			</tr>
		</table>
                </div>
		<?php
	}
	?>
	<br />