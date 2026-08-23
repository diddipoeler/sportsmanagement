<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$personTypes = [
    HTMLHelper::_('select.option', 1, Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_ASSIGN_PLAYERS')),
    HTMLHelper::_('select.option', 2, Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_ASSIGN_STAFF')),
    HTMLHelper::_('select.option', 3, Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_ASSIGN_REFEREES')),
];
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=players&layout=assignconfirm'); ?>" method="post" id="adminForm" name="adminForm">
    <fieldset class="options-form">
        <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_ASSIGN_PLAYERS', $this->escape($this->projectname)); ?></legend>

        <?php if ($this->persons) : ?>
            <ul class="list-group mb-3">
                <?php foreach ($this->persons as $person) : ?>
                    <li class="list-group-item">
                        <input type="hidden" name="cid[]" value="<?php echo (int) $person->id; ?>">
                        <?php
                        $name = trim((string) ($person->firstname ?? '') . ' ' . (string) ($person->lastname ?? ''));
                        if (!empty($person->nickname)) {
                            $name .= ' (' . (string) $person->nickname . ')';
                        }
                        echo $this->escape($name);
                        ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <div class="alert alert-info"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label" for="persontype"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_TITLE'); ?></label>
            <?php echo HTMLHelper::_('select.genericlist', $personTypes, 'persontype', 'class="form-select"', 'value', 'text', 1); ?>
        </div>
        <div class="mb-3">
            <label class="form-label" for="team_id"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_TITLE'); ?></label>
            <?php echo HTMLHelper::_('select.genericlist', $this->projectTeamOptions, 'team_id', 'class="form-select"', 'value', 'text', 0); ?>
        </div>

        <button type="submit" class="btn btn-primary"<?php echo !$this->persons ? ' disabled' : ''; ?>><?php echo Text::_('JAPPLY'); ?></button>
        <input type="hidden" name="project_id" value="<?php echo (int) $this->project_id; ?>">
        <input type="hidden" name="season_id" value="<?php echo (int) $this->season_id; ?>">
        <input type="hidden" name="task" value="players.assign">
        <?php echo HTMLHelper::_('form.token'); ?>
    </fieldset>
</form>
