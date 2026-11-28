<?php
/**
 * Native Joomla 5/6 administrator leagues list layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$user = $this->getCurrentUser();
$listOrder = (string) $this->state->get('list.ordering', 'obj.name');
$listDirn = (string) $this->state->get('list.direction', 'ASC');

$renderOptions = static function (array $options, $selected): string {
    $html = '';

    foreach ($options as $option) {
        $value = (string) $option->value;
        $html .= '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"'
            . ((string) $selected === $value ? ' selected' : '') . '>'
            . htmlspecialchars((string) $option->text, ENT_QUOTES, 'UTF-8') . '</option>';
    }

    return $html;
};

/**
 * Render the saved country even when its catalogue entry is missing.
 * Match historical case and whitespace variants without changing the saved
 * value on a quick save. Translate catalogue labels and escape all markup.
 *
 * @param array $options Country catalogue options.
 * @param mixed $selected Stored league country code.
 * @return string Country option markup.
 */
$renderCountryOptions = static function (array $options, $selected): string {
    $stored = is_string($selected) ? $selected : '';
    $normalised = strtoupper(trim($stored));
    $seen = [];
    $html = '';
    $matched = false;

    foreach ($options as $option) {
        if (!is_object($option) || !is_string($option->value ?? null)) {
            continue;
        }

        $code = strtoupper(trim($option->value));
        if (preg_match('/^[A-Z]{3}$/D', $code) !== 1 || isset($seen[$code])) {
            continue;
        }

        $seen[$code] = true;
        $isSelected = $normalised === $code;
        $matched = $matched || $isSelected;
        $value = $isSelected ? $stored : $option->value;
        $label = is_string($option->text ?? null) && trim($option->text) !== ''
            ? Text::_($option->text) : $code;
        $html .= '<option value="' . htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"'
            . ($isSelected ? ' selected' : '') . '>'
            . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</option>';
    }

    if (!$matched && $stored !== '') {
        $escaped = htmlspecialchars($stored, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = '<option value="' . $escaped . '" selected>' . $escaped . '</option>' . $html;
    }

    return $html;
};
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=leagues'); ?>" method="post" name="adminForm" id="adminForm">
  <?php if ($this->filterForm) echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
  <div class="table-responsive">
    <table class="table table-striped" id="leaguesList">
      <thead>
        <tr>
          <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
          <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_NAME', 'obj.name', $listDirn, $listOrder); ?></th>
          <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_COUNTRY'); ?></th>
          <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_ASSOCIATION'); ?></th>
          <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'); ?></th>
          <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_ACT_SEASON_MOD'); ?></th>
          <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_CHAMPIONS_COMPLETE'); ?></th>
          <th class="w-10 text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'obj.published', $listDirn, $listOrder); ?></th>
          <th class="w-5 text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'obj.id', $listDirn, $listOrder); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->items as $i => $item) : ?>
          <?php $canEdit = $user->authorise('core.edit', 'com_sportsmanagement'); $canChange = $user->authorise('core.edit.state', 'com_sportsmanagement'); ?>
          <tr>
            <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
            <td>
              <?php if ($canEdit) : ?>
                <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=league.edit&id=' . (int) $item->id); ?>"><?php echo $this->escape($item->name); ?></a>
              <?php else : ?>
                <?php echo $this->escape($item->name); ?>
              <?php endif; ?>
              <div class="small text-muted"><?php echo $this->escape($item->short_name); ?> · <?php echo $this->escape(Text::_($item->sportstype)); ?></div>
            </td>
            <td>
              <?php $countryCode = strtoupper(trim((string) ($item->country ?? ''))); ?>
              <?php if (isset($this->countryPresentation[$countryCode])) : ?>
                <div class="small mb-1"><?php echo $this->countryPresentation[$countryCode]; ?></div>
              <?php endif; ?>
              <?php
              // Older Choices.js releases trim option values; keep unusual historical
              // whitespace-bearing codes in the unenhanced select until explicitly changed.
              $storedCountry = (string) ($item->country ?? '');
              $searchableCountry = $storedCountry === trim($storedCountry);
              ?>
              <?php if ($searchableCountry) : ?>
                <joomla-field-fancy-select search-placeholder="<?php echo htmlspecialchars(Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
              <?php endif; ?>
              <select class="form-select form-select-sm" id="league-country-<?php echo (int) $item->id; ?>" name="country<?php echo (int) $item->id; ?>" aria-label="<?php echo htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_COUNTRY'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" data-max-results="30"><option value=""></option><?php echo $renderCountryOptions($this->inlineOptions['countries'] ?? [], $storedCountry); ?></select>
              <?php if ($searchableCountry) : ?>
                </joomla-field-fancy-select>
              <?php endif; ?>
            </td>
            <td><joomla-field-fancy-select search-placeholder="<?php echo htmlspecialchars(Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"><select class="form-select form-select-sm" name="association<?php echo (int) $item->id; ?>"><option value="0"></option><?php echo $renderOptions($this->inlineOptions['associations'], $item->associations); ?></select></joomla-field-fancy-select></td>
            <td><joomla-field-fancy-select search-placeholder="<?php echo htmlspecialchars(Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"><select class="form-select form-select-sm" name="agegroup<?php echo (int) $item->id; ?>"><option value="0"></option><?php echo $renderOptions($this->inlineOptions['agegroups'], $item->agegroup_id); ?></select></joomla-field-fancy-select></td>
            <td class="text-center"><select class="form-select form-select-sm" name="published_act_season<?php echo (int) $item->id; ?>"><option value="0"<?php echo (int) $item->published_act_season === 0 ? ' selected' : ''; ?>><?php echo Text::_('JNO'); ?></option><option value="1"<?php echo (int) $item->published_act_season === 1 ? ' selected' : ''; ?>><?php echo Text::_('JYES'); ?></option></select></td>
            <td class="text-center"><select class="form-select form-select-sm" name="champions_complete<?php echo (int) $item->id; ?>"><option value="0"<?php echo (int) $item->champions_complete === 0 ? ' selected' : ''; ?>><?php echo Text::_('JNO'); ?></option><option value="1"<?php echo (int) $item->champions_complete === 1 ? ' selected' : ''; ?>><?php echo Text::_('JYES'); ?></option></select></td>
            <td class="text-center"><?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'leagues.', $canChange, 'cb'); ?></td>
            <td class="text-center"><?php echo (int) $item->id; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php echo $this->pagination->getListFooter(); ?>
  <input type="hidden" name="task" value="">
  <input type="hidden" name="boxchecked" value="0">
  <input type="hidden" name="filter_order" value="<?php echo $this->escape($listOrder); ?>">
  <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($listDirn); ?>">
  <?php echo HTMLHelper::_('form.token'); ?>
</form>
