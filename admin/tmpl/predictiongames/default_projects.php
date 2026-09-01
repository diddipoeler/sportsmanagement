<?php
/** Native Joomla 5/6 prediction-game project relations. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$stateIcon = static function (bool $enabled, string $enabledTitle = 'JENABLED', string $disabledTitle = 'JDISABLED'): string {
    $title = Text::_($enabled ? $enabledTitle : $disabledTitle);
    $class = $enabled ? 'icon-check text-success' : 'icon-times text-danger';
    $escaped = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

    return '<span class="' . $class . '" title="' . $escaped . '" aria-label="' . $escaped . '"></span>';
};
?>

<div class="card mt-4">
    <div class="card-header">
        <h2 class="h5 mb-0"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_PROJ_COUNT'); ?></h2>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0" id="predictiongame-projects">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_PROJ_NAME'); ?></th>
                        <th class="text-center"><?php echo Text::_('JSTATUS'); ?></th>
                        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_MODE'); ?></th>
                        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_OVERVIEW'); ?></th>
                        <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_JOKER'); ?></th>
                        <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_CHAMP'); ?></th>
                        <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_FINAL4'); ?></th>
                        <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_PREDROUNDS'); ?></th>
                        <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_USE_CARDS'); ?></th>
                        <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_USE_PENALTIES'); ?></th>
                        <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_USE_GOALS'); ?></th>
                        <th class="text-center"><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$this->predictionProjects) : ?>
                    <tr>
                        <td colspan="13" class="text-center py-4">
                            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($this->predictionProjects as $i => $relation) : ?>
                    <?php
                    $relationId = (int) ($relation['id'] ?? 0);
                    $projectId = (int) ($relation['project_id'] ?? 0);
                    $predictionId = (int) ($relation['prediction_id'] ?? $this->prediction_id);
                    $projectName = (string) ($relation['project_name'] ?? '');
                    $modalId = 'predictionproject-modal-' . $relationId;
                    $editUrl = Route::_(
                        'index.php?option=com_sportsmanagement&task=predictionproject.edit&tmpl=component&id=' . $relationId
                        . '&project_id=' . $projectId
                    );
                    $roundsLink = Route::_(
                        'index.php?option=com_sportsmanagement&view=predictionrounds&prediction_id=' . $predictionId
                    );
                    $activeRounds = (int) ($this->activeRoundCounts[$relationId] ?? 0);
                    $projectRounds = (int) ($this->projectRoundCounts[$relationId] ?? 0);
                    $jokerEnabled = !empty($relation['joker']);
                    $jokerLimit = (int) ($relation['joker_limit'] ?? 0);
                    $jokerTitle = $jokerEnabled
                        ? Text::sprintf(
                            'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_MAX_JOKER',
                            $jokerLimit > 0 ? $jokerLimit : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_UNLIMITED_JOKER')
                        )
                        : Text::_('JDISABLED');
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $i + 1; ?></td>
                        <td>
                            <a
                                href="#<?php echo $this->escape($modalId); ?>"
                                data-bs-toggle="modal"
                                role="button"
                                class="fw-semibold"
                            >
                                <?php echo $this->escape($projectName); ?>
                            </a>
                            <?php echo HTMLHelper::_(
                                'bootstrap.renderModal',
                                $modalId,
                                [
                                    'url' => $editUrl,
                                    'title' => Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_EDIT_SETTINGS') . ': ' . $projectName,
                                    'height' => '100%',
                                    'width' => '100%',
                                    'modalWidth' => 80,
                                    'bodyHeight' => 70,
                                    'closeButton' => true,
                                ]
                            ); ?>
                        </td>
                        <td class="text-center"><?php echo $stateIcon(!empty($relation['published'])); ?></td>
                        <td>
                            <?php echo Text::_((int) ($relation['mode'] ?? 0) === 1
                                ? 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TIPP'
                                : 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TOTO'); ?>
                        </td>
                        <td>
                            <?php echo Text::_((int) ($relation['overview'] ?? 0) === 1
                                ? 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_FULL_SEASON'
                                : 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_HALF_SEASON'); ?>
                        </td>
                        <td class="text-center">
                            <?php if ($jokerEnabled) : ?>
                                <span
                                    class="icon-check text-success"
                                    title="<?php echo $this->escape($jokerTitle); ?>"
                                    aria-label="<?php echo $this->escape($jokerTitle); ?>"
                                ></span>
                            <?php else : ?>
                                <?php echo $stateIcon(false); ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?php echo $stateIcon(!empty($relation['champ'])); ?></td>
                        <td class="text-center"><?php echo $stateIcon(!empty($relation['final4'])); ?></td>
                        <td class="text-center">
                            <a href="<?php echo $roundsLink; ?>" class="text-decoration-none">
                                <?php echo $stateIcon($activeRounds > 0); ?>
                                <span class="ms-1"><?php echo $activeRounds; ?>/<?php echo $projectRounds; ?></span>
                            </a>
                        </td>
                        <td class="text-center"><?php echo $stateIcon(!empty($relation['use_cards'])); ?></td>
                        <td class="text-center"><?php echo $stateIcon(!empty($relation['use_penalties'])); ?></td>
                        <td class="text-center"><?php echo $stateIcon(!empty($relation['use_goals'])); ?></td>
                        <td class="text-center"><?php echo $projectId; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
