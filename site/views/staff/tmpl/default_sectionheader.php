<?php 
defined( '_JEXEC' ) or die( 'Restricted access' ); 
JHtml::_('behavior.mootools');
$option = JRequest::getCmd('option');
$modalheight = JComponentHelper::getParams($option)->get('modal_popup_height', 600);
$modalwidth = JComponentHelper::getParams($option)->get('modal_popup_width', 900);


?>
<table width="100%" class="contentpaneopen">
	<tr>
		<td class="contentheading">
			<?php
			echo $this->title;
			
			
			if ( $this->showediticon )
	{
		/*
    $link = JoomleagueHelperRoute::getPlayerRoute( $this->project->id, $this->teamPlayer->team_id, $this->person->id, 'person.edit' );
		$desc = JHtml::image(
			"media/com_joomleague/jl_images/edit.png",
			JText::_( 'COM_SPORTSMANAGEMENT_PERSON_EDIT' ),
			array( "title" => JText::_( "COM_SPORTSMANAGEMENT_PERSON_EDIT" ) )
		);
	    echo " ";
	    echo JHtml::_('link', $link, $desc );
	    */
	
	?>   
	             <a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									href="index.php?option=com_joomleague&tmpl=component&view=editperson&cid=<?php echo $this->person->id; ?>"
									 class="modal">
									<?php
									echo JHtml::_(	'image','administrator/components/com_joomleague/assets/images/edit.png',
													JText::_('COM_SPORTSMANAGEMENT_PERSON_EDIT'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_PERSON_EDIT').'"');
									?>
								</a>
                <?PHP
                
  }
  ?>
		</td>
	</tr>
</table>

