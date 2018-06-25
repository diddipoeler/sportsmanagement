<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_description.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @subpackage playground
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
?>
<?php
if ( $this->playground->notes )
{
?>
<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PLAYGROUND_NOTES'); ?></h2>
<div class="row-fluid">
<?php 
$description = $this->playground->notes;
$description = JHtml::_('content.prepare', $description);
echo $description; 
?>
</div>
<?php
}
?>
