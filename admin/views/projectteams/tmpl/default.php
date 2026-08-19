<?php
/** Main administrator project teams layout. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$templatesToLoad = ['footer', 'listheader'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
    <div>
        <script type="text/javascript">
            const leaguepicture = [];
            <?php foreach ($this->projectsbyleagueseason as $value) : ?>
            leaguepicture[<?php echo (int) $value->value; ?>] = <?php echo json_encode((string) $value->picture); ?>;
            <?php endforeach; ?>

            const teampicture = [];
            <?php foreach (($this->lists['country_teams_picture'] ?? []) as $key => $value) :
                $picture = $value ?: sportsmanagementHelper::getDefaultPlaceholder('clublogobig'); ?>
            teampicture[<?php echo (int) $key; ?>] = <?php echo json_encode((string) $picture); ?>;
            <?php endforeach; ?>
        </script>
        <?php
        $this->document->addStyleDeclaration(
            'img.item { padding-right: 10px; vertical-align: middle; } img.car { height: 25px; }'
        );

        if (isset($this->lists['country_teams'])) {
            echo HTMLHelper::_(
                'select.genericlist',
                $this->lists['country_teams'],
                'team_id',
                'style="width:225px" class="form-select" size="6"',
                'value',
                'text',
                0
            );
            ?>
            <button class="btn btn-primary" type="submit" name="task" value="projectteams.addteam">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_ADD'); ?>
            </button>
            <?php
        }

        if (ComponentHelper::getParams('com_sportsmanagement')->get('show_option_projectteam_change', '')) {
            echo HTMLHelper::_(
                'select.genericlist',
                $this->projectsbyleagueseason,
                'all_project_id',
                'style="width:225px" class="form-select" size="1"',
                'value',
                'text',
                $this->project_id
            );
        }
        ?>
    </div>

    <?php if ($this->project_art_id !== 3) : ?>
        <?php if ($this->projectteam) : ?>
            <?php echo $this->loadTemplate('teams'); ?>
        <?php else : ?>
            <div class="alert alert-info"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div>
        <?php endif; ?>
    <?php else : ?>
        <?php if ($this->projectteam) : ?>
            <?php echo $this->loadTemplate('persons'); ?>
        <?php else : ?>
            <div class="alert alert-info"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>" />
    <input type="hidden" name="season_id" value="<?php echo (int) $this->project->season_id; ?>" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo htmlspecialchars($this->sortDirection, ENT_QUOTES, 'UTF-8'); ?>" />
    <input type="hidden" name="filter_order" value="<?php echo htmlspecialchars($this->sortColumn, ENT_QUOTES, 'UTF-8'); ?>" />
    <input type="hidden" name="search_mode" value="<?php echo htmlspecialchars((string) $this->lists['search_mode'], ENT_QUOTES, 'UTF-8'); ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php echo $this->loadTemplate('footer'); ?>
