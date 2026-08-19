<?php
/** Native Joomla 5/6 project control panel. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$templatesToLoad = ['footer', 'listheader'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

$items = [
    [
        'show' => true,
        'url' => 'index.php?option=com_sportsmanagement&task=project.edit&id=' . (int) $this->project->id,
        'title' => Text::_('COM_SPORTSMANAGEMENT_P_PANEL_PSETTINGS'),
        'icon' => 'icon-cog',
    ],
    [
        'show' => true,
        'url' => 'index.php?option=com_sportsmanagement&view=templates&pid=' . (int) $this->project->id,
        'title' => Text::_('COM_SPORTSMANAGEMENT_P_PANEL_FES'),
        'icon' => 'icon-list',
    ],
    [
        'show' => in_array((string) $this->project->project_type, ['PROJECT_DIVISIONS', 'DIVISIONS_LEAGUE'], true),
        'url' => 'index.php?option=com_sportsmanagement&view=divisions&pid=' . (int) $this->project->id,
        'title' => Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS', (int) $this->count_projectdivisions),
        'icon' => 'icon-grid',
    ],
    [
        'show' => in_array((string) $this->project->project_type, ['TOURNAMENT_MODE', 'DIVISIONS_LEAGUE'], true),
        'url' => 'index.php?option=com_sportsmanagement&view=treetos&pid=' . (int) $this->project->id,
        'title' => Text::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE'),
        'icon' => 'icon-tree-2',
    ],
    [
        'show' => (int) $this->project->project_art_id !== 3,
        'url' => 'index.php?option=com_sportsmanagement&view=projectpositions&pid=' . (int) $this->project->id,
        'title' => Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', (int) $this->count_projectpositions),
        'icon' => 'icon-users',
    ],
    [
        'show' => true,
        'url' => 'index.php?option=com_sportsmanagement&view=projectreferees&persontype=3&pid=' . (int) $this->project->id,
        'title' => Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', (int) $this->count_projectreferees),
        'icon' => 'icon-user',
    ],
    [
        'show' => true,
        'url' => 'index.php?option=com_sportsmanagement&view=projectteams&pid=' . (int) $this->project->id,
        'title' => Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', (int) $this->count_projectteams),
        'icon' => 'icon-users',
    ],
    [
        'show' => true,
        'url' => 'index.php?option=com_sportsmanagement&view=rounds&pid=' . (int) $this->project->id,
        'title' => Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', (int) $this->count_matchdays),
        'icon' => 'icon-calendar',
    ],
    [
        'show' => true,
        'url' => 'index.php?option=com_sportsmanagement&view=jlxmlexports&pid=' . (int) $this->project->id,
        'title' => Text::_('COM_SPORTSMANAGEMENT_P_PANEL_XML_EXPORT'),
        'icon' => 'icon-download',
    ],
];
?>
<div id="j-main-container">
    <?php echo $this->loadTemplate('jsm_warnings'); ?>
    <?php echo $this->loadTemplate('jsm_notes'); ?>
    <?php echo $this->loadTemplate('jsm_tips'); ?>

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
                <?php foreach ($items as $item) : ?>
                    <?php if (!$item['show']) { continue; } ?>
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <a class="card h-100 text-decoration-none" href="<?php echo Route::_($item['url']); ?>">
                            <div class="card-body d-flex align-items-center gap-3">
                                <span class="<?php echo htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'); ?> fs-2" aria-hidden="true"></span>
                                <span><?php echo $item['title']; ?></span>
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
<?php echo $this->loadTemplate('footer'); ?>
