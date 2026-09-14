<?php
/**
 * Native Joomla 5/6 administrator fieldsets router layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$app = $this->app;
$input = $this->jinput;
$modalHeight = (int) ($this->modalheight ?? (\defined('COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT') ? COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT : 600));
$modalWidth = (int) ($this->modalwidth ?? (\defined('COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH') ? COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH : 900));
$helpBase = \defined('COM_SPORTSMANAGEMENT_HELP_SERVER') ? (string) COM_SPORTSMANAGEMENT_HELP_SERVER : '';

if ($app->get('debug')) {
    Log::add(
        __METHOD__ . ' ' . __LINE__ . ' fieldset -> ' . (string) $this->fieldset,
        Log::NOTICE,
        'jsmerror'
    );
}

$renderHelpButton = static function (string $helpUrl, string $modalId) use ($escape, $modalHeight, $modalWidth): string {
    if ($helpUrl === '') {
        return '';
    }

    $helpTitle = Text::_('COM_SPORTSMANAGEMENT_HELP_LINK');
    $button = '<button type="button" class="btn btn-link p-0 align-baseline"'
        . ' data-bs-toggle="modal" data-bs-target="#' . $escape($modalId) . '"'
        . ' title="' . $escape($helpTitle) . '" aria-label="' . $escape($helpTitle) . '">'
        . HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/help.png', $helpTitle, ['title' => $helpTitle])
        . '</button>';

    $modal = HTMLHelper::_(
        'bootstrap.renderModal',
        $modalId,
        [
            'title' => $helpTitle,
            'url' => $helpUrl,
            'height' => $modalHeight,
            'width' => $modalWidth,
            'bodyHeight' => 70,
            'modalWidth' => 80,
        ]
    );

    return $button . $modal;
};

$renderFormFields = static function (iterable $fields): void {
    foreach ($fields as $field) {
        ?>
        <div class="control-group">
            <div class="control-label"><?php echo $field->label; ?></div>
            <div class="controls"><?php echo $field->input; ?></div>
        </div>
        <?php
    }
};

switch ((string) $this->fieldset) {
    case 'playgroundnotic':
        echo $this->loadTemplate('playgroundnotiz');
        break;

    case 'clublogohistory':
        echo $this->loadTemplate('clublogos');
        break;

    case 'playgroundlogohistory':
        echo $this->loadTemplate('playgroundlogos');
        break;

    case 'leaguelogohistory':
        echo $this->loadTemplate('leaguelogos');
        break;

    case 'playground_jquery':
        $pictureName = basename((string) ($this->item->picture ?? ''));
        $backgroundPath = JPATH_ROOT . '/media/com_sportsmanagement/rosterground/' . $pictureName;
        $backgroundImage = Uri::root() . 'media/com_sportsmanagement/rosterground/' . rawurlencode($pictureName);
        $imageSize = is_file($backgroundPath) ? @getimagesize($backgroundPath) : false;
        $width = $imageSize !== false ? (int) $imageSize[0] : 0;
        $height = $imageSize !== false ? (int) $imageSize[1] : 0;
        $picture = Uri::root() . 'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png';
        $schemaHome = $this->bildpositionen[$this->item->name] ?? [];
        ?>
        <style>
            #draggable {
                width: 100px;
                height: 70px;
                background: silver;
            }
        </style>

        <div id="start">
            <input type="text" id="text" value="">
        </div>

        <div id="stop">spieler verschieben</div>

        <div
            id="roster"
            style="background-image:url('<?php echo $escape($backgroundImage); ?>');background-position:left;position:relative;height:<?php echo $height; ?>px;width:<?php echo $width; ?>px;"
        >
            <?php $testlauf = 1; ?>
            <?php foreach ($schemaHome as $value) : ?>
                <?php
                $left = (int) ($value['heim']['links'] ?? 0);
                $top = (int) ($value['heim']['oben'] ?? 0);
                ?>
                <div
                    id="draggable_<?php echo $testlauf; ?>"
                    style="position:absolute;width:103px;left:<?php echo $left; ?>px;top:<?php echo $top; ?>px;text-align:center;"
                >
                    <img
                        class="bild_s"
                        style="width:60px;"
                        id="img_<?php echo $testlauf; ?>"
                        src="<?php echo $escape($picture); ?>"
                        alt=""
                    ><br>
                </div>
                <?php $testlauf++; ?>
            <?php endforeach; ?>
        </div>
        <?php
        break;

    case 'training':
        $view = $input->getCmd('view', 'cpanel');
        ?>
        <fieldset class="adminform">
            <table class="table">
                <tr>
                    <td class="key" nowrap="nowrap">
                        <?php echo Text::_('JACTION_CREATE'); ?>&nbsp;
                        <input
                            type="checkbox"
                            name="add_trainingData"
                            id="add"
                            value="1"
                            onchange="Joomla.submitbutton('<?php echo $escape($view); ?>.apply');"
                        >
                    </td>
                    <td class="key text-center" width="5%"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_DAY'); ?></td>
                    <td class="key text-center" width="5%"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_STARTTIME'); ?></td>
                    <td class="key text-center" width="5%"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_ENDTIME'); ?></td>
                    <td class="key text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_PLACE'); ?></td>
                    <td class="key text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_NOTES'); ?></td>
                </tr>
                <?php if (!empty($this->trainingData)) : ?>
                    <input type="hidden" name="tdCount" value="<?php echo count($this->trainingData); ?>">
                    <?php foreach ($this->trainingData as $td) : ?>
                        <?php
                        $hours = (int) ($td->time_start / 3600);
                        $mins = (int) (($td->time_start - (3600 * $hours)) / 60);
                        $startTime = sprintf('%02d:%02d', $hours, $mins);
                        $hours = (int) ($td->time_end / 3600);
                        $mins = (int) (($td->time_end - (3600 * $hours)) / 60);
                        $endTime = sprintf('%02d:%02d', $hours, $mins);
                        $trainingId = (int) $td->id;
                        ?>
                        <tr>
                            <td class="key" nowrap="nowrap">
                                <?php echo Text::_('JACTION_DELETE'); ?>&nbsp;
                                <input
                                    type="checkbox"
                                    name="delete[]"
                                    value="<?php echo $trainingId; ?>"
                                    onchange="Joomla.submitbutton('<?php echo $escape($view); ?>.apply');"
                                >
                            </td>
                            <td nowrap="nowrap" width="5%"><?php echo $this->lists['dayOfWeek'][$trainingId] ?? ''; ?></td>
                            <td nowrap="nowrap" width="5%">
                                <input class="text" type="text" name="time_start[<?php echo $trainingId; ?>]" size="8" maxlength="5" value="<?php echo $escape($startTime); ?>">
                            </td>
                            <td nowrap="nowrap" width="5%">
                                <input class="text" type="text" name="time_end[<?php echo $trainingId; ?>]" size="8" maxlength="5" value="<?php echo $escape($endTime); ?>">
                            </td>
                            <td>
                                <input class="text" type="text" name="place[<?php echo $trainingId; ?>]" size="40" maxlength="255" value="<?php echo $escape($td->place ?? ''); ?>">
                            </td>
                            <td>
                                <textarea class="text_area" name="notes[<?php echo $trainingId; ?>]" rows="3" cols="40"><?php echo $escape($td->notes ?? ''); ?></textarea>
                                <input type="hidden" name="tdids[]" value="<?php echo $trainingId; ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </fieldset>
        <?php
        break;

    case 'help':
        ?>
        <fieldset class="adminform">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAME_HINT_1'); ?>
        </fieldset>
        <?php
        break;

    case 'teamsofclub':
        if (isset($this->teamsofclub)) {
            ?>
            <fieldset class="adminform">
                <table class="table">
                    <?php foreach ($this->teamsofclub as $team) : ?>
                        <tr>
                            <td>
                                <input type="hidden" name="team_id[]" value="<?php echo (int) $team->id; ?>">
                                <input type="text" name="team_value_id[]" size="50" maxlength="100" value="<?php echo $escape($team->name ?? ''); ?>">
                                <input type="text" name="team_short_name[]" size="100" maxlength="100" style="width:400px;" value="<?php echo $escape($team->short_name ?? ''); ?>">
                                <input type="text" name="club_value_id[]" size="50" maxlength="50" value="<?php echo (int) ($team->club_id ?? 0); ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </fieldset>
            <?php
        }
        break;

    case 'extra_fields':
        ?>
        <fieldset class="adminform">
            <table class="table">
                <?php if (!empty($this->lists['ext_fields']) && is_countable($this->lists['ext_fields'])) : ?>
                    <?php foreach ($this->lists['ext_fields'] as $extField) : ?>
                        <tr>
                            <td width="100"><?php echo $escape($extField->name ?? ''); ?></td>
                            <td>
                                <textarea name="extraf[]" cols="100" rows="4"><?php echo $escape($extField->fvalue ?? ''); ?></textarea>
                                <input type="hidden" name="extra_id[]" value="<?php echo (int) ($extField->id ?? 0); ?>">
                                <input type="hidden" name="extra_value_id[]" value="<?php echo (int) ($extField->value_id ?? 0); ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </fieldset>
        <?php
        break;

    case 'extended':
        if (!empty($this->extended)) {
            foreach ($this->extended->getFieldsets() as $fieldset) {
                $fields = $this->extended->getFieldset($fieldset->name);
                ?>
                <fieldset class="adminform">
                    <?php
                    if (!count($fields)) {
                        echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
                    }
                    $renderFormFields($fields);
                    ?>
                </fieldset>
                <?php
            }
        } else {
            echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
        }
        break;

    case 'extendeduser':
        if (!empty($this->extendeduser)) {
            foreach ($this->extendeduser->getFieldsets() as $fieldset) {
                $fields = $this->extendeduser->getFieldset($fieldset->name);
                ?>
                <fieldset class="adminform">
                    <?php
                    if (!count($fields)) {
                        echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
                    }
                    $renderFormFields($fields);
                    ?>
                </fieldset>
                <?php
            }
        } else {
            echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
        }
        break;

    case 'teamplayer':
    case 'paramsranking':
        if (!empty($this->extended)) {
            foreach ($this->extended->getFieldsets() as $fieldset) {
                $fields = $this->extended->getFieldset($fieldset->name);
                ?>
                <fieldset class="adminform">
                    <?php
                    if (!count($fields)) {
                        echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
                    }
                    $renderFormFields($fields);
                    ?>
                </fieldset>
                <?php
            }
        } else {
            echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
        }
        break;

    case 'params':
        if (!empty($this->formparams)) {
            foreach ($this->formparams->getFieldsets() as $fieldset) {
                $fields = $this->formparams->getFieldset($fieldset->name);
                ?>
                <fieldset class="adminform">
                    <?php if (!count($fields)) : ?>
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS'); ?>
                    <?php endif; ?>
                    <p class="tab-description fw-bold"><?php echo Text::_((string) $this->description); ?></p>
                    <?php foreach ($fields as $field) : ?>
                        <?php echo $field->label; ?>
                        <?php echo $field->input; ?>
                    <?php endforeach; ?>
                </fieldset>
                <?php
            }
        } else {
            echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
        }
        break;

    case 'request':
        echo $this->form->renderFieldset('request');
        break;

    case 'save_injury':
        echo $this->form->renderFieldset('save_injury');
        break;

    case 'save_suspension':
        echo $this->form->renderFieldset('save_suspension');
        break;

    case 'save_away':
        echo $this->form->renderFieldset('save_away');
        break;

    case 'competition':
        echo $this->form->renderFieldset('competition');
        break;

    default:
        ?>
        <table class="table">
            <?php foreach ($this->form->getFieldset($this->fieldset) as $field) : ?>
                <?php
                $fieldKey = str_replace(
                    ['jform[', ']', 'request['],
                    ['', '', ''],
                    (string) $field->name
                );
                $helpUrl = '';

                if ($fieldKey !== 'id' && $helpBase !== '') {
                    $helpUrl = $helpBase
                        . 'SM-Backend-Felder:'
                        . $input->getCmd('view')
                        . '-'
                        . $this->form->getName()
                        . '-'
                        . $fieldKey;
                }
                ?>
                <tr>
                    <td class="key"><?php echo $field->label; ?></td>
                    <td><?php echo $field->input; ?></td>
                    <td>
                        <?php
                        if ($helpUrl !== '') {
                            echo $renderHelpButton(
                                $helpUrl,
                                'jsm-fieldsets4-help-' . substr(sha1($helpUrl), 0, 12)
                            );
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
        break;
}
