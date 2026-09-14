<?php
/**
 * Native Joomla 5/6 player playfield layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa https://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$teamPlayer = $this->teamPlayer ?? null;
$positionName = (string) ($teamPlayer->position_name ?? 'COM_SPORTSMANAGEMENT_PERSON_NO_POSITION');
$backgroundImage = (string) ($teamPlayer->position_image ?? '');
$mainImage = 'images/com_sportsmanagement/database/person_playground/hauptposition.png';
$secondaryImage = 'images/com_sportsmanagement/database/person_playground/nebenposition.png';
$mainPosition = trim((string) ($this->person_position ?? ''));
$secondaryPositions = $this->person_parent_positions ?? [];

if (!is_array($secondaryPositions)) {
    $secondaryPositions = trim((string) $secondaryPositions) !== '' ? [$secondaryPositions] : [];
}
?>
<div class="<?php echo $this->escape((string) ($this->divclassrow ?? '')); ?> table-responsive" id="player">
    <table class="table table-responsive">
        <tr>
            <td width="50%">
                <h2><?php echo '&nbsp;' . Text::_('COM_SPORTSMANAGEMENT_PERSON_PLAYFIELD'); ?></h2>

                <?php if ($mainPosition !== '') : ?>
                    <div<?php echo $backgroundImage !== '' ? ' style="position:relative;height:170px;background-image:url(\'' . $this->escape($backgroundImage) . '\');background-repeat:no-repeat;"' : ' style="position:relative;height:170px;"'; ?>>
                        <img src="<?php echo $this->escape($mainImage); ?>"
                             class="<?php echo $this->escape($mainPosition); ?>"
                             alt="<?php echo $this->escape(Text::_($positionName)); ?>"
                             title="<?php echo $this->escape(Text::_($positionName)); ?>" />

                        <?php foreach ($secondaryPositions as $secondaryPosition) : ?>
                            <?php $secondaryPosition = trim((string) $secondaryPosition); ?>
                            <?php if ($secondaryPosition !== '') : ?>
                                <img src="<?php echo $this->escape($secondaryImage); ?>"
                                     class="<?php echo $this->escape($secondaryPosition); ?>"
                                     alt="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_PERSON_PLAYFIELD')); ?>"
                                     title="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_PERSON_PLAYFIELD')); ?>" />
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>
