<?php 
defined( '_JEXEC' ) or die( 'Restricted access' ); 

JHTML::_('behavior.mootools');
$option = JRequest::getCmd('option');
$modalheight = JComponentHelper::getParams($option)->get('modal_popup_height', 600);
$modalwidth = JComponentHelper::getParams($option)->get('modal_popup_width', 900);

?>
<table class="contentpaneopen">
	<tr>
		<td class="contentheading">
			<?php
	echo JText::sprintf('COM_SPORTSMANAGEMENT_PLAYER_INFORMATION', sportsmanagementHelper::formatName(null, $this->person->firstname, $this->person->nickname, $this->person->lastname, $this->config["name_format"]));
	
	if ( $this->showediticon )
	{
		/*
    $link = sportsmanagementHelperRoute::getPlayerRoute( $this->project->id, $this->teamPlayer->team_id, $this->person->id, 'person.edit' );
		$desc = JHTML::image(
			"media/com_joomleague/jl_images/edit.png",
			JText::_( 'COM_SPORTSMANAGEMENT_PERSON_EDIT' ),
			array( "title" => JText::_( "COM_SPORTSMANAGEMENT_PERSON_EDIT" ) )
		);
	    echo " ";
	    echo JHTML::_('link', $link, $desc );
	    */
	
	?>   
	             <a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									href="index.php?option=com_joomleague&tmpl=component&view=editperson&cid=<?php echo $this->person->id; ?>"
									 class="modal">
									<?php
									echo JHTML::_(	'image','administrator/components/com_joomleague/assets/images/edit.png',
													JText::_('COM_SPORTSMANAGEMENT_PERSON_EDIT'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_PERSON_EDIT').'"');
									?>
								</a>
                <?PHP
                
  }

	if ( isset($this->teamPlayer->injury) && $this->teamPlayer->injury )
	{
		$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURED' );
		echo "&nbsp;&nbsp;" . JHTML::image(	'images/com_joomleague/database/events/'.$this->project->fs_sport_type_name.'/injured.gif',
							$imageTitle,
							array( 'title' => $imageTitle ) );
	}

	if ( isset($this->teamPlayer->suspension) && $this->teamPlayer->suspension )
	{
		$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_SUSPENDED' );
		echo "&nbsp;&nbsp;" . JHTML::image(	'images/com_joomleague/database/events/'.$this->project->fs_sport_type_name.'/suspension.gif',
							$imageTitle,
							array( 'title' => $imageTitle ) );
	}


	if ( isset($this->teamPlayer->away) && $this->teamPlayer->away )
	{
		$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_AWAY' );
		echo "&nbsp;&nbsp;" . JHTML::image(	'images/com_joomleague/database/events/'.$this->project->fs_sport_type_name.'/away.gif',
							$imageTitle,
							array( 'title' => $imageTitle ) );
	}
			?>
		</td>
	</tr>
</table>