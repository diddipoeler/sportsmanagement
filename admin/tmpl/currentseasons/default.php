<?php
/**
 * Native Joomla 5/6 administrator current seasons layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<div class="container-fluid">
    <?php if (!$this->items) : ?>
        <div class="alert alert-info" role="alert">
            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>
        <div class="accordion" id="sportsmanagement-current-seasons">
            <?php foreach ($this->items as $index => $item) : ?>
                <?php
                $projectId = (int) $item->id;
                $headingId = 'jsm-current-season-heading-' . $projectId;
                $collapseId = 'jsm-current-season-project-' . $projectId;
                $projectType = (string) ($item->project_type ?? '');
                $projectArtId = (int) ($item->project_art_id ?? 0);
                $teamsAsReferees = (int) ($item->teams_as_referees ?? 0);

                $actions = [
                    [
                        'label' => Text::_('COM_SPORTSMANAGEMENT_P_PANEL_PSETTINGS'),
                        'icon' => 'icon-options',
                        'url' => Route::_('index.php?option=com_sportsmanagement&task=project.edit&id=' . $projectId),
                    ],
                    [
                        'label' => Text::_('COM_SPORTSMANAGEMENT_P_PANEL_FES'),
                        'icon' => 'icon-file',
                        'url' => Route::_('index.php?option=com_sportsmanagement&view=templates&pid=' . $projectId),
                    ],
                ];

                if (in_array($projectType, ['PROJECT_DIVISIONS', 'DIVISIONS_LEAGUE'], true)) {
                    $actions[] = [
                        'label' => Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS', (int) $item->count_projectdivisions),
                        'icon' => 'icon-tree-2',
                        'url' => Route::_('index.php?option=com_sportsmanagement&view=divisions&pid=' . $projectId),
                    ];
                }

                if (in_array($projectType, ['TOURNAMENT_MODE', 'DIVISIONS_LEAGUE'], true)) {
                    $actions[] = [
                        'label' => Text::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE'),
                        'icon' => 'icon-share-alt',
                        'url' => Route::_('index.php?option=com_sportsmanagement&view=treetos&pid=' . $projectId),
                    ];
                }

                if ($projectArtId !== 3) {
                    $actions[] = [
                        'label' => Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', (int) $item->count_projectpositions),
                        'icon' => 'icon-users',
                        'url' => Route::_('index.php?option=com_sportsmanagement&view=projectpositions&pid=' . $projectId),
                    ];
                }

                if ($teamsAsReferees === 0) {
                    $actions[] = [
                        'label' => Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', (int) $item->count_projectreferees),
                        'icon' => 'icon-user',
                        'url' => Route::_('index.php?option=com_sportsmanagement&view=projectreferees&persontype=3&pid=' . $projectId),
                    ];
                }

                $actions[] = [
                    'label' => Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', (int) $item->count_projectteams),
                    'icon' => 'icon-users',
                    'url' => Route::_('index.php?option=com_sportsmanagement&view=projectteams&pid=' . $projectId),
                ];
                $actions[] = [
                    'label' => Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', (int) $item->count_matchdays),
                    'icon' => 'icon-calendar',
                    'url' => Route::_('index.php?option=com_sportsmanagement&view=rounds&pid=' . $projectId),
                ];
                $actions[] = [
                    'label' => Text::_('COM_SPORTSMANAGEMENT_P_PANEL_XML_EXPORT'),
                    'icon' => 'icon-download',
                    'url' => Route::_('index.php?option=com_sportsmanagement&view=jlxmlexports&pid=' . $projectId),
                ];
                ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="<?php echo $headingId; ?>">
                        <button
                            class="accordion-button<?php echo $index === 0 ? '' : ' collapsed'; ?>"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#<?php echo $collapseId; ?>"
                            aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                            aria-controls="<?php echo $collapseId; ?>"
                        >
                            <span class="me-2 fw-semibold">
                                <?php echo htmlspecialchars((string) $item->name, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="text-body-secondary small">
                                <?php echo htmlspecialchars((string) $item->season, ENT_QUOTES, 'UTF-8'); ?>
                                · <?php echo htmlspecialchars((string) $item->league, ENT_QUOTES, 'UTF-8'); ?>
                                · <?php echo htmlspecialchars((string) $item->sportstype, ENT_QUOTES, 'UTF-8'); ?>
                                <?php if (!empty($item->country)) : ?>
                                    · <?php echo htmlspecialchars((string) $item->country, ENT_QUOTES, 'UTF-8'); ?>
                                <?php endif; ?>
                            </span>
                        </button>
                    </h2>
                    <div
                        id="<?php echo $collapseId; ?>"
                        class="accordion-collapse collapse<?php echo $index === 0 ? ' show' : ''; ?>"
                        aria-labelledby="<?php echo $headingId; ?>"
                        data-bs-parent="#sportsmanagement-current-seasons"
                    >
                        <div class="accordion-body">
                            <div class="row g-3">
                                <?php foreach ($actions as $action) : ?>
                                    <div class="col-12 col-sm-6 col-xl-3">
                                        <a class="card h-100 text-decoration-none" href="<?php echo $action['url']; ?>">
                                            <div class="card-body d-flex align-items-center gap-3">
                                                <span class="<?php echo $action['icon']; ?> fs-3" aria-hidden="true"></span>
                                                <span class="fw-semibold">
                                                    <?php echo htmlspecialchars((string) $action['label'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($this->pagination) : ?>
        <div class="mt-3">
            <?php echo $this->pagination->getListFooter(); ?>
        </div>
    <?php endif; ?>
</div>
