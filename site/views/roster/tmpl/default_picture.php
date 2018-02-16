<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_picture.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage roster
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<?php
	// Show team-picture if defined.
	if ( ( $this->config['show_team_logo'] == 1 ) )
	{
		?>
		<table class="table">
			<tr>
				<td align="center">
					<?php

					$picture = $this->projectteam->picture;
					if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("team") ))
					{
						$picture = $this->team->picture;
					}
					if ( !file_exists( $picture ) )
					{
						$picture = sportsmanagementHelper::getDefaultPlaceholder("team");
					}					
					$imgTitle = JText::sprintf( 'COM_SPORTSMANAGEMENT_ROSTER_PICTURE_TEAM', $this->team->name );
           
      
      ?>


<a href="#" title="<?php echo $this->team->name;?>" data-toggle="modal" data-target=".teamroster">
<img src="<?php echo $picture;?>" alt="<?php echo $this->team->name;?>" width="<?php echo $this->config['team_picture_width'];?>" />
</a>


<div id="" style="display: none;" class="modal fade teamroster" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
<!--  <div class="modal-dialog"> -->
    <div class="modal-content">
    
    <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
          <h4 class="modal-title" id="myLargeModalLabel"><?php echo $this->team->name;?></h4>
        </div>
        
        <div class="modal-body">
            <img src="<?php echo $picture;?>" class="img-responsive img-rounded center-block">
        </div>
        <div class="modal-footer">
<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE');?> </button>
</div>
    </div>
<!--  </div> -->
  </div>
</div>    
     

    <?php
      	
                    ?>
				</td>
			</tr>
		</table>
	<?php
	}
	?>