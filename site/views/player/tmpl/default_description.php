<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<!-- Team Player Description START -->
<?php
	$description = "";
	if ( isset($this->teamPlayer) && !empty($this->teamPlayer->notes) )
	{
		$description = $this->teamPlayer->notes;
	}
	else
	{
		if ( !empty($this->person->notes) )
		{
			$description = $this->person->notes;
		}
	}

	if ( !empty($description) )
	{
		?>
		<h2><?php echo JText::_( 'COM_JOOMLEAGUE_PERSON_INFO' );	?></h2>
		<div class="personinfo">
			<?php	
			$description = JHTML::_('content.prepare', $description);
			echo stripslashes( $description ); 
			?>
		</div>
		<?php
	}
	?>
<!-- Team Player Description END -->