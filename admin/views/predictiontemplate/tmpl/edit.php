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
 * @subpackage predictiontemplate
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

JHtmlBehavior::formvalidation();




$i    = 1;
?>
<style type="text/css">
    <!--
    fieldset.panelform label, fieldset.panelform div.paramrow label, fieldset.panelform span.faux-label {
        max-width: 255px;
        min-width: 255px;
        padding: 0 5px 0 0;
    }
    -->
</style>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view='.$this->view.'&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
  
    <?php
  
    ?>
    <fieldset class="adminform">
        <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_LEGEND', '<i>' . Text::_('COM_SPORTSMANAGEMENT_FES_' . strtoupper($this->form->getName()) . '_NAME') . '</i>', '<i>' . $this->predictionGame->name . '</i>'); ?></legend>
        <fieldset class="adminform">
    <?php
    echo Text::_('COM_SPORTSMANAGEMENT_FES_' . strtoupper($this->form->getName()) . '_DESCR');
    ?>
        </fieldset>

    <?php
    echo HTMLHelper::_('tabs.start', 'tabs', array('useCookie'=>1));
        $fieldSets = $this->form->getFieldsets();
    foreach ($fieldSets as $name => $fieldSet) :
        $label = $fieldSet->name;
        echo HTMLHelper::_('tabs.panel', Text::_($label), 'panel'.$i++);
    ?>
            <fieldset class="panelform">
                <?php
                if (isset($fieldSet->description) && !empty($fieldSet->description)) :
                    echo '<fieldset class="adminform">'.Text::_($fieldSet->description).'</fieldset>';
                endif;
                ?>
                <ul class="config-option-list">
                <?php foreach ($this->form->getFieldset($name) as $field): ?>
                    <li>
        <?php if (!$field->hidden) : ?>
        <?php echo $field->label; ?>
        <?php endif; ?>
        <?php echo $field->input; ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            </fieldset>

    <div class="clr"></div>
    <?php endforeach; ?>
    <?php echo HTMLHelper::_('tabs.end'); ?>
  
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
