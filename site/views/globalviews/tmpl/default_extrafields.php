<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      deafault_extrafields.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage globalviews
 */

defined('_JEXEC') or die('Restricted access');

?>

<div class="row-fluid">
<h4>
<?php echo JText::_('COM_SPORTSMANAGEMENT_EXTRA_FIELDS'); ?>
</h4>
</div>

<?php
if ( isset($this->extrafields) )
{
foreach ($this->extrafields as $field)
{
$value = $field->fvalue;
$field_type = $field->field_type;
if (!empty($value)) // && !$field->backendonly)
{
?>
<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">
<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
<strong><?php echo JText::_( $field->name); ?></strong>
</div>
<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
<?php 
switch (JFactory::getApplication()->input->getVar('view'))
{
    case 'clubinfo':
    $title = $this->club->name;
    break;
    
}    
switch ($field_type)
{
    case 'link':
    echo JHtml::_( 'link', $field->fvalue,$title,  array( "target" => "_blank" ) );
    break;
    default:
    echo JText::_( $field->fvalue); 
    break;
}


?>
</div>
</div>
<?php
}
}
}
?>


