<?php
/**
 * Native Joomla 5/6 administrator debug output for the list header.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$debugEntries = isset(sportsmanagementHelper::$_success_text) && is_array(sportsmanagementHelper::$_success_text)
    ? sportsmanagementHelper::$_success_text
    : [];

if ($debugEntries === []) {
    return;
}

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div id="sportsmanagement-admin-debug" class="vstack gap-2">
    <h3><?php echo $escape(Text::_('COM_SPORTSMANAGEMENT_DEBUG_INFO')); ?></h3>

    <?php foreach ($debugEntries as $group => $rows) : ?>
        <details class="border rounded p-2">
            <summary class="fw-semibold"><?php echo $escape(Text::_((string) $group)); ?></summary>

            <div class="vstack gap-2 mt-2">
                <?php foreach ((array) $rows as $row) : ?>
                    <?php
                    $method = is_object($row) ? ($row->methode ?? '') : '';
                    $line = is_object($row) ? ($row->line ?? '') : '';
                    $text = is_object($row) ? ($row->text ?? '') : '';
                    ?>
                    <fieldset class="border rounded p-2">
                        <?php if ((string) $method !== '') : ?>
                            <legend class="float-none w-auto px-2 fs-6"><?php echo $escape(Text::_((string) $method)); ?></legend>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <tbody>
                                <tr>
                                    <th scope="row" class="text-nowrap"><?php echo $escape($line); ?></th>
                                    <td><pre class="mb-0 text-wrap"><?php echo $escape($text); ?></pre></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </fieldset>
                <?php endforeach; ?>
            </div>
        </details>
    <?php endforeach; ?>
</div>
