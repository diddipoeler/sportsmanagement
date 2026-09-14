<?php
/**
 * Native Joomla 5/6 administrator fieldsets edit layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$input = $this->jinput ?? null;
$view = (string) ($this->view ?? ($input ? $input->getCmd('view', 'cpanel') : 'cpanel'));
$tmpl = (string) ($this->tmpl ?? ($input ? $input->getCmd('tmpl', '') : ''));
$itemId = (int) ($this->item->id ?? 0);
$modalHeight = (int) ($this->modalheight ?? (\defined('COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT') ? COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT : 600));
$modalWidth = (int) ($this->modalwidth ?? (\defined('COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH') ? COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH : 900));
$helpBase = \defined('COM_SPORTSMANAGEMENT_HELP_SERVER') ? (string) COM_SPORTSMANAGEMENT_HELP_SERVER : '';
$countryDatabase = null;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$formAction = Route::_(
    'index.php?option=com_sportsmanagement&view=' . rawurlencode($view)
    . '&layout=edit&id=' . $itemId
    . '&tmpl=' . rawurlencode($tmpl)
);
?>
<form action="<?php echo $formAction; ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
    <div class="width-60 fltlft">
        <fieldset class="adminform">
            <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'); ?></legend>
            <ul class="adminformlist">
                <?php foreach ($this->form->getFieldset('details') as $field) : ?>
                    <li>
                        <?php echo $field->label; ?>
                        <?php echo $field->input; ?>

                        <?php if ($field->name === 'jform[country]') : ?>
                            <?php
                            $countryDatabase ??= (new SportsManagementDatabaseResolver())->resolve();
                            echo CountryOptionsHelper::getFlag($countryDatabase, (string) $field->value);
                            ?>
                        <?php endif; ?>

                        <?php if ($field->name === 'jform[website]') : ?>
                            <?php $website = trim((string) $field->value); ?>
                            <?php if ($website !== '' && filter_var($website, FILTER_VALIDATE_URL)) : ?>
                                <img
                                    src="<?php echo $escape('https://www.thumbshots.de/cgi-bin/show.cgi?url=' . rawurlencode($website)); ?>"
                                    alt="<?php echo $escape($website); ?>"
                                    loading="lazy"
                                >
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php
                        $fieldKey = str_replace(['jform[', ']'], '', (string) $field->name);

                        if ($fieldKey !== 'id' && $helpBase !== '') :
                            $helpUrl = $helpBase
                                . 'SM-Backend-Felder:'
                                . $view
                                . '-'
                                . $this->form->getName()
                                . '-'
                                . $fieldKey;
                            $modalId = 'jsm-field-help-' . substr(sha1($helpUrl), 0, 12);
                            $helpTitle = Text::_('COM_SPORTSMANAGEMENT_HELP_LINK');
                            ?>
                            <button
                                type="button"
                                class="btn btn-link p-0 align-baseline"
                                data-bs-toggle="modal"
                                data-bs-target="#<?php echo $escape($modalId); ?>"
                                title="<?php echo $escape($helpTitle); ?>"
                                aria-label="<?php echo $escape($helpTitle); ?>"
                            >
                                <?php
                                echo HTMLHelper::_(
                                    'image',
                                    'media/com_sportsmanagement/jl_images/help.png',
                                    $helpTitle,
                                    ['title' => $helpTitle]
                                );
                                ?>
                            </button>
                            <?php
                            echo HTMLHelper::_(
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
                            ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </fieldset>
    </div>

    <?php echo $this->loadTemplate('editdata'); ?>
