<?php
/**
 * Joomla 5/6 runtime layout for the AJAX top navigation module.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$moduleId = (int) ($module->id ?? 0);
$tabsColumn = max(1, min(12, (int) $params->get('col_tabs', 6)));
$flagColumn = max(1, min(12, (int) $params->get('col_img', 2)));
$menuColumn = max(1, min(12, (int) $params->get('col_menu', 4)));
$selectedFederation = (string) ($country_federation ?? '');

$bucketKey = static function (array $buckets, string $federation): string {
    if (array_key_exists($federation, $buckets)) {
        return $federation;
    }

    $upper = strtoupper($federation);

    return array_key_exists($upper, $buckets) ? $upper : $federation;
};

$renderSelect = static function (
    array $options,
    string $name,
    mixed $selected
): string {
    return HTMLHelper::_(
        'select.genericlist',
        $options,
        $name,
        'class="form-select form-select-sm" size="1"',
        'value',
        'text',
        $selected
    );
};

$renderLinkItem = static function (string $view, string $label) use ($helper, $escape): string {
    if ($view === 'separator') {
        return '<li class="nav-item separator"><span class="nav-link disabled">' . $escape($label) . '</span></li>';
    }

    if ($view === '') {
        return '';
    }

    $link = $helper->getLink($view);
    if (!$link) {
        return '';
    }

    return '<li class="nav-item"><a class="nav-link" href="' . $escape(Route::_($link)) . '">'
        . $escape($label) . '</a></li>';
};
?>
<div class="container-fluid px-0">
    <div class="row g-3 align-items-start">
        <div class="col-12 col-lg-<?php echo $tabsColumn; ?>">
            <ul class="nav nav-tabs" role="tablist">
                <?php foreach ($tab_points as $index => $federation) : ?>
                    <?php
                    $federation = (string) $federation;
                    $isActive = $federation === $selectedFederation || ($selectedFederation === '' && $index === 0);
                    $tabId = 'jlajaxtopmenu-' . $federation . $moduleId;
                    ?>
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link<?php echo $isActive ? ' active' : ''; ?>"
                            id="<?php echo $escape($tabId . '-tab'); ?>"
                            data-bs-toggle="tab"
                            data-bs-target="#<?php echo $escape($tabId); ?>"
                            type="button"
                            role="tab"
                            aria-controls="<?php echo $escape($tabId); ?>"
                            aria-selected="<?php echo $isActive ? 'true' : 'false'; ?>"
                        >
                            <?php echo $escape(Text::_(strtoupper($federation))); ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content pt-3">
                <?php foreach ($tab_points as $index => $federation) : ?>
                    <?php
                    $federation = (string) $federation;
                    $isActive = $federation === $selectedFederation || ($selectedFederation === '' && $index === 0);
                    $tabId = 'jlajaxtopmenu-' . $federation . $moduleId;
                    $assocKey = $bucketKey($countryassocselect, $federation);
                    $subAssocKey = $bucketKey($countrysubassocselect, $federation);
                    $subSubAssocKey = $bucketKey($countrysubsubassocselect, $federation);
                    $subSubSubAssocKey = $bucketKey($countrysubsubsubassocselect, $federation);
                    $leagueKey = $bucketKey($leagueselect, $federation);
                    $projectKey = $bucketKey($projectselect, $federation);
                    ?>
                    <div
                        class="tab-pane fade<?php echo $isActive ? ' show active' : ''; ?>"
                        id="<?php echo $escape($tabId); ?>"
                        role="tabpanel"
                        aria-labelledby="<?php echo $escape($tabId . '-tab'); ?>"
                        tabindex="0"
                    >
                        <div class="vstack gap-2">
                            <?php echo $renderSelect(
                                $federationselect[$federation] ?? [],
                                'jlamtopfederation' . $federation . $moduleId,
                                $country_id
                            ); ?>
                            <?php echo $renderSelect(
                                $countryassocselect[$assocKey]['assocs'] ?? [],
                                'jlamtopassoc' . $federation . $moduleId,
                                $assoc_id
                            ); ?>
                            <?php echo $renderSelect(
                                $countrysubassocselect[$subAssocKey]['assocs'] ?? [],
                                'jlamtopsubassoc' . $federation . $moduleId,
                                $subassoc_id
                            ); ?>
                            <?php echo $renderSelect(
                                $countrysubsubassocselect[$subSubAssocKey]['subassocs'] ?? [],
                                'jlamtopsubsubassoc' . $federation . $moduleId,
                                $subsubassoc_id
                            ); ?>
                            <?php echo $renderSelect(
                                $countrysubsubsubassocselect[$subSubSubAssocKey]['subsubassocs'] ?? [],
                                'jlamtopsubsubsubassoc' . $federation . $moduleId,
                                $subsubsubassoc_id
                            ); ?>
                            <?php echo $renderSelect(
                                $leagueselect[$leagueKey]['leagues'] ?? [],
                                'jlamtopleagues' . $federation . $moduleId,
                                $league_id
                            ); ?>
                            <?php echo $renderSelect(
                                $projectselect[$projectKey]['projects'] ?? [],
                                'jlamtopprojects' . $federation . $moduleId,
                                $project_id
                            ); ?>
                            <?php echo $renderSelect(
                                $projectselect[$projectKey]['teams'] ?? [],
                                'jlamtopteams' . $federation . $moduleId,
                                $team_id
                            ); ?>
                        </div>

                        <?php if ($team_id && $params->get('show_nav_links')) : ?>
                            <ul class="nav flex-column mt-3">
                                <?php for ($i = 17; $i < 23; ++$i) : ?>
                                    <?php
                                    $view = (string) $params->get('navpointct' . $i, '');
                                    $label = (string) $params->get('navpointct_label' . $i, '');
                                    echo $renderLinkItem($view, $label);
                                    ?>
                                <?php endfor; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-12 col-lg-<?php echo $flagColumn; ?> text-lg-end">
            <?php if ($country_id && class_exists('JSMCountries')) : ?>
                <?php $flag = (string) JSMCountries::getCountryFlag($country_id, '', false, true); ?>
                <?php if ($flag !== '') : ?>
                    <img
                        class="img-fluid"
                        src="<?php echo $escape($flag); ?>"
                        alt="<?php echo $escape($country_id); ?>"
                        width="144"
                        loading="lazy"
                    >
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="col-12 col-lg-<?php echo $menuColumn; ?>">
            <ul class="pagination mb-2" id="pagination" aria-live="polite"></ul>

            <?php if ($project_id && $params->get('show_nav_links')) : ?>
                <ul class="nav flex-column" id="ajax-nav-list">
                    <?php for ($i = 1; $i < 18; ++$i) : ?>
                        <?php
                        $view = (string) $params->get('navpoint' . $i, '');
                        $label = (string) $params->get('navpoint_label' . $i, '');
                        echo $renderLinkItem($view, $label);
                        ?>
                    <?php endfor; ?>

                    <?php if ($params->get('show_tournament_nav_links') || ($project && ($project->project_type ?? '') === 'TOURNAMENT_MODE')) : ?>
                        <?php echo $renderLinkItem('tournamentbracket', (string) $params->get('show_tournament_text', '')); ?>
                    <?php endif; ?>

                    <?php if ($params->get('show_alltimetable_nav_links')) : ?>
                        <?php echo $renderLinkItem('rankingalltime', (string) $params->get('show_alltimetable_text', '')); ?>
                    <?php endif; ?>
                </ul>
            <?php else : ?>
                <ul class="nav flex-column" id="ajax-nav-list"></ul>
            <?php endif; ?>
        </div>
    </div>
</div>
