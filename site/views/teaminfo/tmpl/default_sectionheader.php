<?php 
defined( '_JEXEC' ) or die( 'Restricted access' ); 

JHTML::_('behavior.mootools');
$modalheight = JComponentHelper::getParams('com_sportsmanagement')->get('modal_popup_height', 600);
$modalwidth = JComponentHelper::getParams('com_sportsmanagement')->get('modal_popup_width', 900);

?>
<!-- START: Contentheading -->
<div class="contentpaneopen">
	<div class="contentheading">
		<?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_PAGE_TITLE') . " - " . $this->team->tname;
		if ( $this->showediticon )
		{
			/*
            $link = JoomleagueHelperRoute::getProjectTeamInfoRoute( $this->project->id, $this->projectteamid, 'projectteam.edit' );
			$desc = JHTML::image(
					"media/com_sportsmanagement/jl_images/edit.png",
					JText::_( 'COM_SPORTSMANAGEMENT_PROJECTTEAM_EDIT' ),
					array( "title" => JText::_( "COM_SPORTSMANAGEMENT_PROJECTTEAMEDIT" ) )
			);
			echo " ";
			echo JHTML::_('link', $link, $desc );
            */
        ?>   
	             <a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									href="index.php?option=com_sportsmanagement&tmpl=component&view=editprojectteam&ptid=<?php echo $this->projectteamid; ?>&tid=<?php echo $this->teamid; ?>&p=<?php echo $this->project->id; ?>"
									 class="modal">
									<?php
									echo JHTML::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMINFO_EDIT_DETAILS'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMINFO_EDIT_DETAILS').'"');
									?>
								</a>
                <?PHP    
		} else {
			//echo "no permission";
		}
		?>
	</div>
</div>
<!-- END: Contentheading -->
