<?php
/** Joomla 5/6 JoomLeague age-group mapping view. */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=joomleagueimports&layout=infofield', false); ?>"
      method="post" id="adminForm" name="adminForm">
    <div class="card">
        <div class="card-header">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_SETAGEGROUP_START_BUTTON'); ?>
        </div>
        <div class="card-body p-0">
            <?php if ($this->get_info_fields) : ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME'); ?></th>
                                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->get_info_fields as $value) : ?>
                            <?php $info = (string) ($value->info ?? ''); ?>
                            <tr>
                                <th scope="row"><?php echo $this->escape($info); ?></th>
                                <td>
                                    <?php
                                    echo HTMLHelper::_(
                                        'select.genericlist',
                                        $this->agegroupOptions,
                                        'agegroup[' . $info . ']',
                                        'class="form-select"',
                                        'value',
                                        'text',
                                        (int) ($value->agegroup_id ?? 0)
                                    );
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div class="alert alert-info m-3 mb-0">
                    <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="jl_table_import_step" value="<?php echo $this->escape($this->jl_table_import_step); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
