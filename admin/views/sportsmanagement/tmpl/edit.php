<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       edit.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage sportsmanagement
 */
 

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);


$params = $this->form->getFieldsets('params');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view='.$this->view.'&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="helloworld-form" class="form-validate">
 
    <div class="width-60 fltlft">
        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_HELLOWORLD_HELLOWORLD_DETAILS'); ?></legend>
            <ul class="adminformlist">
<?php foreach($this->form->getFieldset('details') as $field): ?>
                <li><?php echo $field->label;echo $field->input;?></li>
<?php endforeach; ?>
            </ul>
    </div>
 
    <div class="width-40 fltrt">
    <?php echo HTMLHelper::_('sliders.start', 'sportsmanagement-slider'); ?>
<?php foreach ($params as $name => $fieldset): ?>
    <?php echo HTMLHelper::_('sliders.panel', Text::_($fieldset->label), $name.'-params');?>
    <?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
        <p class="tip"><?php echo $this->escape(Text::_($fieldset->description));?></p>
    <?php endif;?>
        <fieldset class="panelform" >
            <ul class="adminformlist">
    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                <li><?php echo $field->label; ?><?php echo $field->input; ?></li>
    <?php endforeach; ?>
            </ul>
        </fieldset>
<?php endforeach; ?>
 
    <?php echo HTMLHelper::_('sliders.end'); ?>
    </div>
 
    <div>
        <input type="hidden" name="task" value="sportsmanagement.edit" />
    <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   
