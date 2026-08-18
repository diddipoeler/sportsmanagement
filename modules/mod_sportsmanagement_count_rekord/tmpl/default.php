<?php
\defined('_JEXEC') or die;
?>
<div class="<?php echo htmlspecialchars($module->module . '-' . $module->id, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if ($list) : ?>
        <table class="table">
            <tbody>
            <?php foreach ($list as $row) : ?>
                <tr><td><?php echo $row->text; ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
