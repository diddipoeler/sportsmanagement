<?php
/**
 * Joomla 5/6 project rounds multi-select field.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

final class RoundsField extends SportsManagementListField
{
    protected $type = 'rounds';

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Prepare a ten-row multiple-round selector for Joomla administrator forms.
     *
     * Applies the legacy multiple, size and Bootstrap styling attributes
     * before invoking the inherited Joomla form-field initialisation.
     *
     * @param \SimpleXMLElement $element XML definition for this form field.
     * @param mixed $value Selected round identifiers.
     * @param string|null $group Optional form group.
     * @return bool True when the Joomla form field is initialised.
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $element['multiple'] = 'true';
        $element['size'] = '10';
        $element['class'] = 'form-select';

        return parent::setup($element, $value, $group);
    }

    /**
     * Build the round choices for a strictly validated project ID.
     *
     * Resolves the current project from saved form fields or administrator
     * input, validates ordering and required attributes, and binds the
     * resulting project identifier through the Joomla database API.
     * A malformed explicit project selection returns no round rows.
     *
     * @return array<int,object> Eligible project rounds or the empty option.
     */
    protected function getOptions(): array
    {
        $rawRequired = $this->element['required'] ?? '';
        $requiredText = is_string($rawRequired) || $rawRequired instanceof \Stringable
            ? (string) $rawRequired : '';
        $required = $rawRequired === true || $requiredText === 'true';

        $rawOrder = $this->element['order'] ?? 'ASC';
        $orderText = is_string($rawOrder) || $rawOrder instanceof \Stringable
            ? (string) $rawOrder : 'ASC';
        $direction = strtoupper($orderText) === 'DESC' ? 'DESC' : 'ASC';
        $projectId = $this->resolveProjectId();
        $options = [];

        if (!$required) {
            $options[] = (object) [
                'value' => '',
                'text' => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
            ];
        }

        if ($projectId > 0) {
            $db = $this->getSportsManagementDatabase();
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('id'),
                    $db->quoteName('name'),
                    $db->quoteName('roundcode'),
                ])
                ->from($db->quoteName('#__sportsmanagement_round'))
                ->where($db->quoteName('project_id') . ' = :projectId')
                ->bind(':projectId', $projectId, ParameterType::INTEGER)
                ->order($db->quoteName('roundcode') . ' ' . $direction);
            $db->setQuery($query);

            $rows = $db->loadObjectList();
            foreach (is_array($rows) ? $rows : [] as $round) {
                if (!is_object($round)
                    || (!is_int($round->id ?? null) && !is_string($round->id ?? null))
                    || !is_string($round->name ?? '')) {
                    continue;
                }

                $name = trim($round->name ?? '');

                if ($name === '') {
                    $name = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME') . ' ' . (int) $round->id;
                }

                $options[] = (object) [
                    'value' => (string) $round->id,
                    'text' => "\u{00A0}\u{00A0}\u{00A0}" . $name,
                ];
            }
        }

        return $options;
    }

    /**
     * Resolve the selected project from form state or Joomla request input.
     *
     * Tries historical field names and group contexts, preserving the
     * first valid positive ID. Explicit malformed values fail closed
     * instead of being truncated to a different project or replaced
     * by an unrelated fallback request value.
     *
     * @return int Positive selected project ID, or zero when unavailable.
     */
    private function resolveProjectId(): int
    {
        foreach (['project', 'project_id', 'p'] as $field) {
            foreach (['', 'request', 'params'] as $group) {
                $raw = $this->form->getValue($field, $group);
                $value = self::projectId($raw);

                if ($value > 0) {
                    return $value;
                }

                if ($raw !== null && $raw !== '' && $raw !== 0 && $raw !== '0') {
                    return 0;
                }
            }
        }

        $input = SportsManagementAdministratorApplicationResolver::resolve()->getInput();

        return max(0, $input->getInt('project_id', 0) ?: $input->getInt('p', 0));
    }

    /**
     * Parse a complete positive project ID from Joomla form state.
     *
     * Supports zero-padded numeric values but rejects strings with extra
     * characters, booleans, nested settings and integers outside PHP's
     * supported range. Returns zero instead of guessing another project.
     *
     * @param mixed $raw Raw form or saved project selection.
     * @return int Positive project identifier, or zero on invalid input.
     */
    private static function projectId(mixed $raw): int
    {
        if (is_int($raw)) {
            return $raw > 0 ? $raw : 0;
        }

        if (!is_string($raw) || $raw === '' || strlen($raw) > 64
            || preg_match('/^[0-9]+$/D', $raw) !== 1) {
            return 0;
        }

        $digits = ltrim($raw, '0');
        $value = $digits !== '' ? filter_var($digits, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]) : false;

        return $value === false ? 0 : $value;
    }
}
