<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<?php
if ( $this->playground->notes )
{
?>

<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PLAYGROUND_NOTES'); ?></h2>
		
	<div class="venuecontent">
    <?php 
    $description = $this->playground->notes;
    $description = JHTML::_('content.prepare', $description);
    echo $description; 
    ?>
    </div>
    <?php
}
?>