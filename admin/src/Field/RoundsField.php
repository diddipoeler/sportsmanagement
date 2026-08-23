<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** Joomla 5/6 project rounds multi-select. */
final class RoundsField extends FormField
{
    use SportsManagementDatabaseTrait;

    protected $type = 'rounds';

    protected function getInput(): string
    {
        $required = (string) ($this->element['required'] ?? '') === 'true';
        $direction = strtoupper((string) ($this->element['order'] ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $projectId = $this->resolveProjectId();
        $options = [];

        if (!$required) {
            $options[] = HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'));
        }

        if ($projectId > 0) {
            $db = $this->getSportsManagementDatabase();
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('id'),
                    $db->quoteName('name'),
                    $db->quoteName('roundcode'),
                ])
                ->from($db->quoteName('#__sportsmanagement_round'))
                ->where($db->quoteName('project_id') . ' = ' . $projectId)
                ->order($db->quoteName('roundcode') . ' ' . $direction);
            $db->setQuery($query);

            foreach ($db->loadObjectList() ?: [] as $round) {
                $name = trim((string) $round->name);

                if ($name === '') {
                    $name = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME') . ' ' . (int) $round->id;
                }

                $options[] = HTMLHelper::_('select.option', (int) $round->id, "\u{00A0}\u{00A0}\u{00A0}" . $name);
            }
        }

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            $this->name . '[]',
            'class="form-select" multiple="multiple" size="10"',
            'value',
            'text',
            $this->value,
            $this->id
        );
    }

    private function resolveProjectId(): int
    {
        foreach (['project', 'project_id', 'p'] as $field) {
            foreach (['', 'request', 'params'] as $group) {
                $value = (int) $this->form->getValue($field, $group);

                if ($value > 0) {
                    return $value;
                }
            }
        }

        $input = Factory::getApplication()->getInput();

        return $input->getInt('project_id', 0) ?: $input->getInt('p', 0);
    }
}
