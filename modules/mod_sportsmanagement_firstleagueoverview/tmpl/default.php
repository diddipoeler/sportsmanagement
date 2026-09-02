<?php
/**
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$tabSetId = 'jsm-firstleagueoverview-' . (int) $module->id;
$activeTab = $federations ? 'federation-' . (int) array_key_first($federations) : '';
?>
<div class="<?php echo htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8'); ?>"
     id="<?php echo htmlspecialchars($module->module . '-' . $module->id, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="mb-2">
        <?php echo Text::_('MOD_SPORTSMANAGEMENT_FIRSTLEAGUEOVERVIEW_DESCRIPTION'); ?>
    </div>

    <?php if ($federations) : ?>
        <?php echo HTMLHelper::_('bootstrap.startTabSet', $tabSetId, ['active' => $activeTab]); ?>
        <?php foreach ($federations as $federationId => $federation) : ?>
            <?php
            $tabId = 'federation-' . (int) $federationId;
            $label = Text::_((string) $federation->name);

            if (!empty($federation->picture_url)) {
                $label .= ' <img src="'
                    . htmlspecialchars((string) $federation->picture_url, ENT_QUOTES, 'UTF-8')
                    . '" alt="' . htmlspecialchars(Text::_((string) $federation->name), ENT_QUOTES, 'UTF-8')
                    . '" width="50" loading="lazy">';
            }
            ?>
            <?php echo HTMLHelper::_('bootstrap.addTab', $tabSetId, $tabId, $label); ?>

            <?php $hasProjects = false; ?>
            <?php foreach ($firstleagueoverview as $project) : ?>
                <?php if ((int) ($project->federation ?? 0) !== (int) $federationId) { continue; } ?>
                <?php $hasProjects = true; ?>
                <div class="mb-1">
                    <?php echo $project->flag_html; ?>
                    <a href="<?php echo htmlspecialchars((string) $project->ranking_link, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars((string) $project->name, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </div>
            <?php endforeach; ?>

            <?php if (!$hasProjects) : ?>
                <div class="text-muted"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div>
            <?php endif; ?>

            <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php endforeach; ?>
        <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
    <?php endif; ?>
</div>
