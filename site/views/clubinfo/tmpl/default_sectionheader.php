<?php 
defined( '_JEXEC' ) or die( 'Restricted access' ); 
JHTML::_('behavior.mootools');
$modalheight = JComponentHelper::getParams('COM_SPORTSMANAGEMENT')->get('modal_popup_height', 600);
$modalwidth = JComponentHelper::getParams('COM_SPORTSMANAGEMENT')->get('modal_popup_width', 900);
?>

	<div class="contentpaneopen">
		<div class="contentheading">
			<?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_TITLE' ) . " " . $this->club->name;

	            if ( $this->showediticon )
	            {
	                /*
                    $link = JoomleagueHelperRoute::getClubInfoRoute( $this->project->id, $this->club->id, "club.edit" );
	                $desc = JHTML::image(
	                                      "media/COM_SPORTSMANAGEMENT/jl_images/edit.png",
	                                      JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_EDIT' ),
	                                      array( "title" => JText::_( "COM_SPORTSMANAGEMENT_CLUBINFO_EDIT" ) )
	                                   );
	                echo " ";
	                echo JHTML::_('link', $link, $desc );
                    */
                 ?>   
	             <a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									href="index.php?option=COM_SPORTSMANAGEMENT&tmpl=component&view=editclub&cid=<?php echo $this->club->id; ?>"
									 class="modal">
									<?php
									echo JHTML::_(	'image','administrator/components/COM_SPORTSMANAGEMENT/assets/images/edit.png',
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBINFO_EDIT_DETAILS'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBINFO_EDIT_DETAILS').'"');
									?>
								</a>
                <?PHP
                
                
                }
			?>
		</div>
	</div>