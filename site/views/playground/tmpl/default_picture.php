<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_picture.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @subpackage playground
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 

//echo '<pre>',print_r($this->playground,true),'</pre><br>';

?>

<?php
if ( ( $this->playground->picture ) )
{
    ?>

 <h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PLAYGROUND_CLUB_PICTURE'); ?></h2>  
		<div class="row-fluid">
                <?php
                if (($this->playground->picture)) 
                {
                
                  $picture = COM_SPORTSMANAGEMENT_PICTURE_SERVER . $this->playground->picture;
                
                } 
                else 
                {
                
$picture = COM_SPORTSMANAGEMENT_PICTURE_SERVER . sportsmanagementHelper::getDefaultPlaceholder("team");
                
                }

echo sportsmanagementHelperHtml::getBootstrapModalImage('playground'.$this->playground->id,$picture,$this->playground->name,$this->config['playground_picture_width'])                
?>

		</div>
    <?php
}
?>
