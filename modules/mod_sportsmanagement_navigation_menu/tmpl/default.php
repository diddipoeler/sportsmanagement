<?php
/**
 * Joomla 5/6 default layout for mod_sportsmanagement_navigation_menu.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

$moduleId = (int) $module->id;
?>
<div
    id="jl-nav-module-<?= $moduleId ?>"
    class="jl-nav-module"
    data-jsm-navigation-menu
>
    <form method="post" action="<?= htmlspecialchars((string) Uri::root(), ENT_QUOTES, 'UTF-8') ?>">
        <ul class="nav menu<?= htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8') ?>">
            <?php if ((string) $params->get('show_project_dropdown') === 'season') : ?>
                <?php if ($seasonselect) : ?>
                    <li class="season-select"><?= $seasonselect ?></li>
                <?php endif; ?>
                <?php if ($leagueselect) : ?>
                    <li class="league-select"><?= $leagueselect ?></li>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($params->get('show_project_dropdown') && $projectselect) : ?>
                <li class="project-select"><?= $projectselect ?></li>
            <?php endif; ?>

            <?php if ($params->get('show_division_dropdown') && $divisionselect) : ?>
                <li class="division-select"><?= $divisionselect ?></li>
            <?php endif; ?>

            <?php if ($params->get('show_teams_dropdown') && $teamselect) : ?>
                <li class="team-select">
                    <?php if ($heading = trim((string) $params->get('heading_teams_dropdown', ''))) : ?>
                        <span class="label"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <?= $teamselect ?>
                </li>
            <?php endif; ?>

            <?php if ($params->get('show_nav_links')) : ?>
                <?php for ($i = 1; $i <= 16; $i++) : ?>
                    <?php $view = (string) $params->get('navpoint' . $i, ''); ?>
                    <?php $label = (string) $params->get('navpoint_label' . $i, ''); ?>
                    <?php if ($view === 'separator') : ?>
                        <li class="nav-item separator"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php elseif ($view !== '' && ($link = $helper->getLink($view))) : ?>
                        <li class="nav-item">
                            <a href="<?= htmlspecialchars((string) $link, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>
            <?php endif; ?>
        </ul>

        <input type="hidden" name="option" value="com_sportsmanagement">
        <input type="hidden" name="view" value="<?= htmlspecialchars((string) $defaultview, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="teamview" value="<?= htmlspecialchars((string) $params->get('link_teams_dropdown', 'roster'), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="o" value="<?= (int) $params->get('project_ordering', 0) ?>">
        <input type="hidden" name="include_season" value="<?= (int) $params->get('project_include_season_name', 0) ?>">
    </form>
</div>
