<?php
/**
 * Shared Joomla 5/6 extra-fields presentation.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ExtraFieldsReadHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$this->notes = [Text::_('COM_SPORTSMANAGEMENT_EXTRA_FIELDS')];
echo $this->loadTemplate('jsm_notes');

$this->tips = [];
$viewName = $this->input->getCmd('view', (string) ($this->view ?? $this->getName()));
$extraFields = [];
$title = '';

if ($viewName === 'clubinfo' && !empty($this->club->id)) {
    $model = $this->getModel();
    if ($model instanceof SportsManagementProjectModel) {
        $extraFields = ExtraFieldsReadHelper::load(
            $model->getDatabase(),
            (int) $this->club->id,
            $viewName
        );
        $title = (string) ($this->club->name ?? '');
    }
} elseif ($viewName === 'teaminfo' && !empty($this->teamid)) {
    $extraFields = is_array($this->extrafields ?? null) ? $this->extrafields : [];
    $title = (string) ($this->team->tname ?? $this->team->name ?? '');
}

if ($extraFields) {
    $output = '<table class="table">';

    foreach ($extraFields as $field) {
        $value = trim((string) ($field->fvalue ?? ''));
        if ($value === '') {
            continue;
        }

        $output .= '<tr>';
        $output .= '<td>' . Text::_((string) ($field->name ?? '')) . '</td>';

        if ((string) ($field->field_type ?? '') === 'link') {
            $output .= '<td>' . HTMLHelper::link(
                $value,
                $title,
                ['target' => '_blank', 'rel' => 'noopener']
            ) . '</td>';
        } else {
            $output .= '<td>' . Text::_($value) . '</td>';
        }

        $output .= '</tr>';

        if (strtolower(Text::_((string) ($field->name ?? ''))) === 'wikipedia'
            && preg_match('#^https?://#i', $value)) {
            $output .= '<tr><td colspan="2">'
                . '<div class="row"><div class="col-lg-12 col-md-12 col-sm-12">'
                . '<iframe class="col-lg-12 col-md-12 col-sm-12" style="height:400px" src="'
                . htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
                . '" loading="lazy" referrerpolicy="no-referrer"></iframe>'
                . '</div></div></td></tr>';
        }
    }

    $output .= '</table>';
    $this->tips[] = $output;
}

echo $this->loadTemplate('jsm_tips');
