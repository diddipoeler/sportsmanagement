<?php
/** Native results section header for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$title = $this->roundid > 0
    ? Text::_('COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS')
    : Text::_('COM_SPORTSMANAGEMENT_RESULTS_PLAN');

if ($this->roundid > 0 && $this->division) {
    $title = Text::sprintf(
        'COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS2',
        '<i>' . $this->escape((string) ($this->division->name ?? '')) . '</i>'
    );
}

if ($this->roundcode !== '') {
    $title .= ' - ' . Text::sprintf('COM_SPORTSMANAGEMENT_RESULTS_GAMEDAY_NB', $this->roundcode);
}

$projectReference = (string) ($this->project->slug ?? $this->project->id ?? '');
$divisionId = (int) ($this->division->id ?? $this->input->getInt('division', 0));
$mode = $this->input->getInt('mode', 0);
$order = $this->input->getInt('order', 0);
?>
<div class="<?php echo $this->escape($this->divclassrow); ?>" id="sectionheader">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="contentheading">
            <?php echo $title; ?>
            <?php if ($this->showediticon) : ?>
                <?php
                $editLink = SiteRouteHelper::view('results', [
                    'cfg_which_database' => $this->cfg_which_database,
                    's' => $this->season_id,
                    'p' => $projectReference,
                    'r' => $this->roundid,
                    'division' => $divisionId,
                    'mode' => $mode,
                    'order' => $order,
                    'layout' => (string) ($this->config['result_style_edit'] ?? 'form'),
                ]);
                $editTitle = Text::_('COM_SPORTSMANAGEMENT_RESULTS_ENTER_EDIT_RESULTS');
                echo ' ' . HTMLHelper::link(
                    $editLink,
                    HTMLHelper::image(
                        'media/com_sportsmanagement/jl_images/edit.png',
                        $editTitle,
                        ['title' => $editTitle, 'width' => 20, 'height' => 20]
                    ),
                    ['title' => $editTitle]
                );
                ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($this->config['show_matchday_dropdown']) && $this->roundsoption) : ?>
            <form method="get" class="d-flex align-items-center gap-2">
                <input type="hidden" name="option" value="com_sportsmanagement">
                <input type="hidden" name="view" value="results">
                <input type="hidden" name="cfg_which_database" value="<?php echo $this->cfg_which_database; ?>">
                <input type="hidden" name="s" value="<?php echo $this->season_id; ?>">
                <input type="hidden" name="p" value="<?php echo $this->escape($projectReference); ?>">
                <?php if ($divisionId > 0) : ?>
                    <input type="hidden" name="division" value="<?php echo $divisionId; ?>">
                <?php endif; ?>
                <label for="results-round-select" class="visually-hidden">
                    <?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHDAY_NAME'); ?>
                </label>
                <select
                    id="results-round-select"
                    name="r"
                    class="form-select form-select-sm"
                    onchange="this.form.submit()"
                >
                    <?php foreach ($this->roundsoption as $round) : ?>
                        <?php $value = (int) ($round->value ?? 0); ?>
                        <option value="<?php echo $value; ?>"<?php echo $value === $this->roundid ? ' selected' : ''; ?>>
                            <?php echo $this->escape((string) ($round->text ?? $value)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php endif; ?>
    </div>
</div>
