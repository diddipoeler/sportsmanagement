<?php
/**
 * Native Joomla 5/6 rivals layout.
 */
\defined('_JEXEC') or die;
?>
<div class="<?php echo htmlspecialchars((string) $this->divclasscontainer, ENT_QUOTES, 'UTF-8'); ?>" id="rivals">
    <?php if (!empty($this->config['show_sectionheader'])) : ?>
        <div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?>" id="sectionheader">
            <div class="color-box">
                <div class="shadow">
                    <div class="info-tab note-icon" title="sectionheader"><i></i></div>
                    <div class="note-box">
                        <p><strong><?php echo htmlspecialchars((string) $this->headertitle, ENT_QUOTES, 'UTF-8'); ?></strong></p>
                    </div>
                </div>
            </div>
            <br>
        </div>
    <?php endif; ?>

    <?php
    echo $this->loadTemplate('projectheading');
    echo $this->loadTemplate('rivals');
    echo $this->loadTemplate('jsminfo');
    ?>
</div>
