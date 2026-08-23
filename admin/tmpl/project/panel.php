<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$projectId = (int) $this->project->id;
$items = [
    [true, 'index.php?option=com_sportsmanagement&task=project.edit&id=' . $projectId, Text::_('COM_SPORTSMANAGEMENT_P_PANEL_PSETTINGS'), 'icon-cog'],
    [true, 'index.php?option=com_sportsmanagement&view=templates&pid=' . $projectId, Text::_('COM_SPORTSMANAGEMENT_P_PANEL_FES'), 'icon-list'],
    [in_array((string) $this->project->project_type, ['PROJECT_DIVISIONS', 'DIVISIONS_LEAGUE'], true), 'index.php?option=com_sportsmanagement&view=divisions&pid=' . $projectId, Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS', $this->count_projectdivisions), 'icon-grid'],
    [in_array((string) $this->project->project_type, ['TOURNAMENT_MODE', 'DIVISIONS_LEAGUE'], true), 'index.php?option=com_sportsmanagement&view=treetos&pid=' . $projectId, Text::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE'), 'icon-tree-2'],
    [(int) $this->project->project_art_id !== 3, 'index.php?option=com_sportsmanagement&view=projectpositions&pid=' . $projectId, Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', $this->count_projectpositions), 'icon-users'],
    [true, 'index.php?option=com_sportsmanagement&view=projectreferees&persontype=3&pid=' . $projectId, Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', $this->count_projectreferees), 'icon-user'],
    [true, 'index.php?option=com_sportsmanagement&view=projectteams&pid=' . $projectId, Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', $this->count_projectteams), 'icon-users'],
    [true, 'index.php?option=com_sportsmanagement&view=rounds&pid=' . $projectId, Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', $this->count_matchdays), 'icon-calendar'],
    [true, 'index.php?option=com_sportsmanagement&view=jlxmlexports&pid=' . $projectId, Text::_('COM_SPORTSMANAGEMENT_P_PANEL_XML_EXPORT'), 'icon-download'],
];
?>
<div id="j-main-container">
    <?php foreach ($this->notes as $note) : ?>
        <div class="alert alert-info"><?php echo $note; ?></div>
    <?php endforeach; ?>

    <div class="card mb-3">
        <div class="card-header">
            <h2 class="h4 mb-0">
                <?php echo Text::sprintf(
                    'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_CONTROL_PANEL_LEGEND',
                    '<i>' . htmlspecialchars((string) $this->project->name, ENT_QUOTES, 'UTF-8') . '</i>'
                ); ?>
            </h2>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($items as [$show, $url, $title, $icon]) : ?>
                    <?php if (!$show) : continue; endif; ?>
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <a class="card h-100 text-decoration-none" href="<?php echo Route::_($url); ?>">
                            <div class="card-body d-flex align-items-center gap-3">
                                <span class="<?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?> fs-2" aria-hidden="true"></span>
                                <span><?php echo $title; ?></span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="alert alert-info">
        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_CONTROL_PANEL_HINT'); ?>
    </div>
</div>
