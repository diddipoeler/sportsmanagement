<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       edit_3.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage predictiontemplate
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

$params = $this->form->getFieldsets('params');
$fieldsets = $this->form->getFieldsets();


JHtmlBehavior::formvalidation();





$i    = 1;
?>

<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=predictiontemplate&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
    
    <?php
    
    ?>
    <fieldset class="adminform">
        <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_LEGEND', '<i>' . Text::_('COM_SPORTSMANAGEMENT_FES_' . strtoupper($this->form->getName()) . '_NAME') . '</i>', '<i>' . $this->predictionGame->name . '</i>'); ?></legend>
        <fieldset class="adminform">
    <?php
    echo Text::_('COM_SPORTSMANAGEMENT_FES_' . strtoupper($this->form->getName()) . '_DESCR');
    ?>
        </fieldset>

<div class="form-horizontal">
<?php 

echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'COM_SPORTSMANAGEMENT_FES_PARAMS_GROUP_OPTIONS')); 


?>

<?PHP    
foreach ($fieldsets as $fieldset) 
{
    echo HTMLHelper::_('bootstrap.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true));    


    ?>
    <div class="row-fluid">
            <div class="span9">
                <div class="row-fluid form-horizontal-desktop">
                    <div class="span6">
    <?PHP
    foreach( $this->form->getFieldset($fieldset->name) as $field ) 
    {
        ?>
                    <div class="control-group">
                        <div class="control-label">
        <?php echo $field->label; ?>
                        </div>
                        <div class="controls">
        <?php echo $field->input; ?>
                        </div>
                    </div>
                <?php

    }
    ?>
    </div>
                </div>
            </div>
            </div>
    <?PHP

    echo HTMLHelper::_('bootstrap.endTab');    
}    

?>    
    
<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
</div> 			
    
</fieldset>    
<div>		
<input type='hidden' name='user_id' value='<?php echo $this->user->id; ?>'/>
<input type="hidden" name="id" value="<?php echo $this->item->id; ?>"/>
<input type="hidden" name="predid" value="<?php echo $this->prediction_id; ?>"/>
<input type="hidden" name="task" value="predictiontemplate.edit"/>
<?php echo HTMLHelper::_('form.token'); ?>
</div>
</form>

<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   
