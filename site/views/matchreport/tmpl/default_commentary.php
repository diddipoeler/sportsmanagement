<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<!-- START of match commentary -->
<?php

if (!empty($this->matchcommentary))
{
	?>
	<table width="100%" class="contentpaneopen">
		<tr>
			<td class="contentheading">
				<?php
				echo '&nbsp;' . JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_COMMENTARY' );
				?>
			</td>
		</tr>
	</table>
    
<table class="matchreport" border="0">
			<?php
			foreach ( $this->matchcommentary as $commentary )
			{
				
                echo $farbe = ($farbe == '<tr class="weiss">') ? '<tr class="grau">' : '<tr class="weiss">';
                ?>
				
				
					<td class="list">
						<dl>
							<?php echo $commentary->event_time; ?>
						</dl>
					</td>
                    <td class="list">
						<dl>
							<?php 
                            echo JHtml::image( JURI::root().'media/com_sportsmanagement/jl_images/discuss_active.gif', 'Kommentar', array(' title' => 'Kommentar')); 
                            ?>
						</dl>
					</td>
					<td class="list">
						<dl>
							<?php echo $commentary->notes; ?>
						</dl>
					</td>
				</tr>
				<?php
			}
			?>
</table>        
<?PHP    
}    

?>