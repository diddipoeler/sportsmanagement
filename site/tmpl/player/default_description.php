<?php
/**
 * Native Joomla 5/6 player description layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa https://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$description = '';

if (!empty($this->teamPlayer->notes)) {
    $description = (string) $this->teamPlayer->notes;
}

if (!empty($this->person->notes)) {
    $description .= ($description !== '' ? '<br />' : '') . (string) $this->person->notes;
}
?>
<div class="<?php echo $this->escape((string) ($this->divclassrow ?? '')); ?> table-responsive" id="defaultplayerdescription">
    <?php if ($description !== '') : ?>
        <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_INFO'); ?></h2>
        <div class="personinfo">
            <?php echo stripslashes(HTMLHelper::_('content.prepare', $description)); ?>
        </div>
    <?php endif; ?>
</div>
