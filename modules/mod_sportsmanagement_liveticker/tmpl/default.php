<?php
/**
 * Joomla 5/6 layout for the SportsManagement liveticker module.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$moduleClass = trim((string) $params->get('moduleclass_sfx', ''));
$moduleId = (int) ($moduleId ?? $module->id ?? 0);
$elementId = 'mod-sportsmanagement-liveticker-' . $moduleId;
$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<div
    id="<?php echo $escape($elementId); ?>"
    class="js-sportsmanagement-liveticker<?php echo $moduleClass !== '' ? ' ' . $escape($moduleClass) : ''; ?>"
    data-refresh-url="<?php echo $escape($refreshUrl ?? ''); ?>"
    data-module-id="<?php echo $moduleId; ?>"
    data-update-timeout="<?php echo (int) $updateTimeout * 1000; ?>"
>
    <noscript>
        <div class="alert alert-warning">
            <?php echo Text::_('!Warning! JavaScript must be enabled for automatic updates.'); ?>
        </div>
    </noscript>

    <div class="turtushout-status" role="status" aria-live="polite"></div>
    <div class="turtushout-shout"><?php echo $listHtml; ?></div>
</div>
