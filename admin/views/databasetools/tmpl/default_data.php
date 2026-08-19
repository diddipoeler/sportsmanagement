<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

$tools = [
    [
        'task' => 'databasetool.truncate',
        'label' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATE',
        'description' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATE_DESCR',
        'title' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATE2',
        'danger' => true,
    ],
    [
        'task' => 'databasetool.truncatejl',
        'label' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATEJL',
        'description' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATEJL_DESCR',
        'title' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATE2JL',
        'danger' => true,
    ],
    [
        'task' => 'databasetool.optimize',
        'label' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_OPTIMIZE',
        'description' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_OPTIMIZE_DESCR',
        'title' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_OPTIMIZE2',
        'danger' => false,
    ],
    [
        'task' => 'databasetool.repair',
        'label' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_REPAIR',
        'description' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_REPAIR_DESCR',
        'title' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_REPAIR2',
        'danger' => false,
    ],
    [
        'task' => 'databasetool.picturepath',
        'label' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_PICTURE_PATH_MIGRATION',
        'description' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_PICTURE_PATH_MIGRATION_DESCR',
        'title' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_PICTURE_PATH_MIGRATION2',
        'danger' => false,
    ],
    [
        'task' => 'databasetool.updatetemplatemasters',
        'label' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_UPDATE_TEMPLATE_MASTERS',
        'description' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_UPDATE_TEMPLATE_MASTERS_DESCR',
        'title' => 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_UPDATE_TEMPLATE_MASTERS2',
        'danger' => false,
    ],
];
?>
<div id="editcell">
    <table class="table">
        <thead>
            <tr>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TOOL'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_DESCR'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tools as $tool) : ?>
            <tr>
                <td class="text-nowrap align-top">
                    <button
                        type="submit"
                        class="btn <?php echo $tool['danger'] ? 'btn-danger' : 'btn-outline-primary'; ?>"
                        name="task"
                        value="<?php echo htmlspecialchars($tool['task'], ENT_QUOTES, 'UTF-8'); ?>"
                        title="<?php echo htmlspecialchars(Text::_($tool['title']), ENT_QUOTES, 'UTF-8'); ?>"
                    >
                        <?php echo Text::_($tool['label']); ?>
                    </button>
                </td>
                <td>
                    <div class="alert <?php echo $tool['danger'] ? 'alert-warning' : 'alert-info'; ?> mb-0">
                        <?php echo Text::_($tool['description']); ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
