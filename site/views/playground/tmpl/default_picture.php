<?php 
defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<?php
if ( ( $this->playground->picture ) )
{
    ?>

 <h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PLAYGROUND_CLUB_PICTURE'); ?></h2>  
		<div class="venuecontent picture">
                <?php
                if (($this->playground->picture)) 
                {
                
                  $picture = JURI::root() . $this->playground->picture;
                
                } 
                else 
                {
                
$picture = JURI::root() . sportsmanagementHelper::getDefaultPlaceholder("team");
                
                }
                
?>
        
<a href="<?php echo $picture;?>" title="<?php echo $this->playground->name;?>" class="modal">
<img src="<?php echo $picture;?>" alt="<?php echo $this->playground->name;?>" width="<?php echo $this->config['playground_picture_width'];?>" />
</a>        

		<?php                
                ?>
		</div>
    <?php
}
?>
