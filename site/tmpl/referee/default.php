<?php
/** Native Joomla 5/6 referee layout. */
\defined('_JEXEC') or die;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="<?php echo $escape($this->divclasscontainer); ?>" id="referee">
    <?php if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) : ?>
        <?php echo $this->loadTemplate('debug'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('projectheading'); ?>

    <?php if (!empty($this->config['show_sectionheader'])) : ?>
        <div class="<?php echo $escape($this->divclassrow); ?>" id="sectionheader">
            <div class="color-box">
                <div class="shadow">
                    <div class="info-tab note-icon" title="sectionheader"><i></i></div>
                    <div class="note-box">
                        <p><strong><?php echo $escape($this->headertitle); ?></strong></p>
                    </div>
                </div>
            </div>
            <br>
        </div>
    <?php endif; ?>

    <?php if (!empty($this->config['show_info'])) : ?>
        <?php echo $this->loadTemplate('info'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_extended'])) : ?>
        <?php echo $this->loadTemplate('extended'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_description'])) : ?>
        <?php echo $this->loadTemplate('description'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_gameshistory'])) : ?>
        <?php echo $this->loadTemplate('gameshistory'); ?>
    <?php endif; ?>

    <?php if (!empty($this->config['show_career'])) : ?>
        <?php echo $this->loadTemplate('career'); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
