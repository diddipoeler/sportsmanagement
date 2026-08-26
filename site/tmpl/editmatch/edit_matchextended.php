<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       edit_matchextended.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

if (!$this->extended) {
    echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
    return;
}
?>
<?php foreach ($this->extended->getFieldsets() as $fieldset) : ?>
    <fieldset class="adminform">
        <legend><?php echo Text::_($fieldset->name); ?></legend>
        <?php $fields = $this->extended->getFieldset($fieldset->name); ?>
        <?php if ($fields === []) : ?>
            <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS'); ?>
        <?php else : ?>
            <?php foreach ($fields as $field) : ?>
                <?php echo $field->label; ?>
                <?php echo $field->input; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </fieldset>
<?php endforeach; ?>
