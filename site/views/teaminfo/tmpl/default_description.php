<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

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
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr class="sectiontableheader">
				<td>
					<?php
					echo '&nbsp;' . JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_TEAMINFORMATION' );
					?>
				</td>
			</tr>
		</table>

		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td>
					<?php
					$description = JHTML::_('content.prepare', $description);
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