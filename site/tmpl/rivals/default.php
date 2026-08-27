<?php
/**
 * Native Joomla 5/6 rivals layout.
 */
\defined('_JEXEC') or die;
?>
<div class="<?php echo $this->divclasscontainer; ?>" id="rivals">
    <?php
    if (!empty($this->config['show_sectionheader'])) {
        echo $this->loadTemplate('sectionheader');
    }

    echo $this->loadTemplate('projectheading');
    echo $this->loadTemplate('rivals');
    echo $this->loadTemplate('jsminfo');
    ?>
</div>
