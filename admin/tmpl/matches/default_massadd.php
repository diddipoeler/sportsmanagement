<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$roundDate = (string) ($this->roundws->round_date_first ?? '');
if ($roundDate === '' || $roundDate === '0000-00-00') {
    $roundDate = date('Y-m-d');
}
$startTime = (string) ($this->projectws->start_time ?? '20:00');
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=matches&layout=massadd&massadd=1&pid=' . (int) $this->project_id); ?>"
    method="post"
    name="copyform"
    id="copyform"
>
    <h2 class="h4 mb-3">
        <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_TITLE', '<strong>' . $this->escape((string) $this->projectws->name) . '</strong>'); ?>
    </h2>

    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="h5"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_MULTI'); ?></h3>

                    <div class="mb-3">
                        <label class="form-label" for="tempaddmatchescount"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_NR'); ?></label>
                        <input class="form-control" type="number" min="1" name="tempaddmatchescount" id="tempaddmatchescount" value="1">
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_START_HERE'); ?></label>
                        <div><?php echo $this->lists['addToRound'] ?? ''; ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_AUTO_PUBL'); ?></label>
                        <div><?php echo $this->lists['autoPublish'] ?? ''; ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="firstMatchNumber"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_FIRST_MATCHNR'); ?></label>
                        <input class="form-control" type="number" min="1" name="firstMatchNumber" id="firstMatchNumber">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label" for="match_date"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_STARTTIME'); ?></label>
                            <?php echo HTMLHelper::calendar(
                                \sportsmanagementHelper::convertDate($roundDate),
                                'match_date',
                                'match_date',
                                '%d-%m-%Y',
                                ['class' => 'form-control']
                            ); ?>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="startTime"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TIME'); ?></label>
                            <input class="form-control" type="time" name="startTime" id="startTime" value="<?php echo $this->escape($startTime); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="playground_id"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MD_VENUE'); ?></label>
                        <?php echo HTMLHelper::_('select.genericlist', $this->playgrounds, 'playground_id', 'class="form-select"', 'value', 'text'); ?>
                    </div>

                    <button class="btn btn-primary" type="submit" id="create-matches">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_NEW_MATCHES'); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="h5"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_COPY'); ?></h3>

                    <div class="mb-3">
                        <label class="form-label"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_COPY2'); ?></label>
                        <?php echo $this->lists['project_rounds2'] ?? ''; ?>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label" for="date"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_DEFAULT_DATE'); ?></label>
                            <?php echo HTMLHelper::calendar(
                                \sportsmanagementHelper::convertDate($roundDate),
                                'date',
                                'date',
                                '%d-%m-%Y',
                                ['class' => 'form-control']
                            ); ?>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="copyStartTime"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TIME'); ?></label>
                            <input class="form-control" type="time" name="copy_start_time" id="copyStartTime" value="<?php echo $this->escape($startTime); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="start_match_number"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_FIRST_MATCHNR'); ?></label>
                        <input class="form-control" type="number" min="1" name="start_match_number" id="start_match_number">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="start_round_name"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_TITLE'); ?></label>
                        <input class="form-control" type="text" name="start_round_name" id="start_round_name">
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="create_new" id="create_new" value="1" checked>
                        <label class="form-check-label" for="create_new"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_CREATE_NEW'); ?></label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="mirror"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_COPY_MIRROR'); ?></label>
                        <select class="form-select" name="mirror" id="mirror">
                            <option value="0"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_COPY_MATCHES'); ?></option>
                            <option value="1"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_MIRROR_HA'); ?></option>
                        </select>
                    </div>

                    <button class="btn btn-primary" type="submit" id="copy-matches">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_COPY_MATCHES'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="task" value="match.copyfrom">
    <input type="hidden" name="addtype" value="0" id="addtype">
    <input type="hidden" name="add_match_count" value="0" id="addmatchescount">
    <input type="hidden" name="project_id" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="round_id" value="<?php echo (int) $this->rid; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const type = document.getElementById('addtype');
    const count = document.getElementById('addmatchescount');
    const tempCount = document.getElementById('tempaddmatchescount');
    const startTime = document.getElementById('startTime');
    const copyStartTime = document.getElementById('copyStartTime');

    document.getElementById('create-matches').addEventListener('click', () => {
        type.value = '1';
        count.value = tempCount.value;
    });

    document.getElementById('copy-matches').addEventListener('click', () => {
        type.value = '2';
        startTime.value = copyStartTime.value;
    });
});
</script>
