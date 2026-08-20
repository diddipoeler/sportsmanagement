<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<form action="<?php echo $this->escape($this->request_url); ?>" method="post" id="adminForm" name="adminForm">
    <fieldset class="options-form">
        <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_ASSIGN_PLAYERS', $this->escape($this->projectname)); ?></legend>

        <?php if ($this->persons) : ?>
            <ul class="list-group mb-3">
                <?php foreach ($this->persons as $person) : ?>
                    <li class="list-group-item">
                        <input type="hidden" name="cid[]" value="<?php echo (int) $person->id; ?>">
                        <?php echo $this->escape(sportsmanagementHelper::formatName(null, $person->firstname, $person->nickname, $person->lastname, 0)); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="mb-3">
            <?php echo $this->lists['type']; ?>
        </div>
        <div class="mb-3">
            <?php echo $this->lists['teams']; ?>
        </div>

        <button type="submit" class="btn btn-primary"><?php echo Text::_('JAPPLY'); ?></button>
        <input type="hidden" name="project_id" value="<?php echo (int) $this->project_id; ?>">
        <input type="hidden" name="season_id" value="<?php echo (int) $this->season_id; ?>">
        <input type="hidden" name="task" value="players.assign">
        <?php echo HTMLHelper::_('form.token'); ?>
    </fieldset>
</form>
