<?php
\defined('_JEXEC') or die;

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<div class="<?php echo $escape($this->divclasscontainer); ?>" id="eventsranking">
    <?php if (!empty($this->config['show_sectionheader'])) : ?>
        <div class="<?php echo $escape($this->divclassrow); ?>" id="sectionheader">
            <table class="table">
                <tr>
                    <td class="contentheading"><?php echo $escape($this->headertitle); ?></td>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <?php echo $this->loadTemplate('projectheading'); ?>

    <?php if (!empty($this->config['show_eventsstats'])) : ?>
        <?php echo $this->loadTemplate('eventsrank'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
