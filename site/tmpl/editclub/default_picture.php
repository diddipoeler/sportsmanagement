<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage editclub
 * @file       default_picture.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2026 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
?>
<fieldset class="mb-4">
    <legend class="h5"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_LOGO'); ?></legend>

    <?php foreach ($this->form->getFieldset('picture') as $field) : ?>
        <?php if (strtolower((string) $field->type) === 'hidden') : ?>
            <?php echo $field->input; ?>
            <?php continue; ?>
        <?php endif; ?>
        <div class="mb-3">
            <div class="form-label"><?php echo $field->label; ?></div>
            <?php echo $field->input; ?>
        </div>
    <?php endforeach; ?>
</fieldset>
