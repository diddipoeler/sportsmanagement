<?php
/**
 * Native Joomla 5/6 next-match preview.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$preview = trim((string) ($this->match->preview ?? ''));
?>
<?php if ($preview !== '') : ?>
    <div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?> table-responsive" id="nextmatch-preview">
        <div class="accordion" id="accordion-nextmatch-preview">
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-nextmatch-preview">
                    <button class="accordion-button collapsed" type="button"
                            data-bs-toggle="collapse" data-bs-target="#nextmatch-preview-content"
                            aria-expanded="false" aria-controls="nextmatch-preview-content">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_PREVIEW'); ?>
                    </button>
                </h2>
                <div id="nextmatch-preview-content" class="accordion-collapse collapse"
                     aria-labelledby="heading-nextmatch-preview" data-bs-parent="#accordion-nextmatch-preview">
                    <div class="accordion-body">
                        <?php
                        $preview = HTMLHelper::_('content.prepare', $preview);
                        // Do not leak JComments control tags when the comments
                        // extension is not present on a Joomla 5/6 installation.
                        if (!class_exists('JComments')) {
                            $preview = preg_replace('#{jcomments\s+(off|lock)}#is', '', $preview) ?? $preview;
                        }
                        echo $preview;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
