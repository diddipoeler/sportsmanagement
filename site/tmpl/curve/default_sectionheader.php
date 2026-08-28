<?php
/** Native Joomla 5/6 Curve section header. */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$title = Text::_('COM_SPORTSMANAGEMENT_CURVE_TITLE');
if ($this->division && trim((string) ($this->division->name ?? '')) !== '') {
    $title .= ' ' . (string) $this->division->name;
}
?>
<div class="<?php echo $this->escape($this->divclassrow); ?> mb-3" id="curve-sectionheader">
    <h2 class="h4 mb-0"><?php echo $this->escape($title); ?></h2>
</div>
