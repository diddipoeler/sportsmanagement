<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage allprojectrounds
 */
 
defined( '_JEXEC' ) or die( 'Restricted access' ); 

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>


<div class="">
<!-- projectheading -->

<?php echo $this->loadTemplate('projectheading'); ?>

<?php 

if ( $this->config['show_sectionheader'] )
{
echo $this->loadTemplate('sectionheader'); 
}

?>
<?php echo $this->loadTemplate('results_all'); ?>
<?PHP

// if ($this->config['show_colorlegend']==1){echo $this->loadTemplate('colorlegend');}
	
// if ($this->config['show_explanation']==1){echo $this->loadTemplate('explanation');}

?>
	
<!-- backbutton -->
<div>
<?php echo $this->loadTemplate('backbutton'); ?>
<!-- footer -->
<?php echo $this->loadTemplate('footer'); ?>
</div>

</div>