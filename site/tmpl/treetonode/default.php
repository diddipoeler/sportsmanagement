<?php
/** Native Joomla 5/6 tree-to-node layout. */
\defined('_JEXEC') or die;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="<?php echo $escape($this->divclasscontainer); ?>" id="treetonode">
    <div class="<?php echo $escape($this->divclassrow); ?> table-responsive" id="treetonodeanzeige">
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
        <?php echo $this->loadTemplate('treetonode'); ?>
        <?php echo $this->loadTemplate('jsminfo'); ?>
    </div>
</div>
