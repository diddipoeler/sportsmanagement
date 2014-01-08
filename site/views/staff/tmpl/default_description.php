<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<!-- Person description START -->
<?php
	$description = "";
	if ( !empty($this->inprojectinfo->notes) )
	{
		echo "<!-- Person Description -->";
		$description = $this->inprojectinfo->notes;
	} else {
		if ( !empty($this->person->notes) )
		{
			echo "<!-- Team Staff Description -->";
			$description = $this->person->notes;
		}
	}

	if ( $description != '' )
	{
		?>
		<h2><?php echo JText::_('COM_JOOMLEAGUE_PERSON_INFO'); ?></h2>
		<table width="96%" align="center" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td>
					&nbsp;
				</td>
			</tr>
			<tr>
				<td>
					<?php
					$description = JHTML::_('content.prepare', $description);
					echo stripslashes( $description );
					?>
				</td>
			</tr>
		</table>
		<br /><br />
		<?php
	}
?>
<!-- Person description END -->