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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

final class RoundsField extends SportsManagementListField
{
    protected $type = 'rounds';

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $element['multiple'] = 'true';
        $element['size'] = '10';
        $element['class'] = 'form-select';

        return parent::setup($element, $value, $group);
    }

    protected function getOptions(): array
    {
        $required = (string) ($this->element['required'] ?? '') === 'true';
        $direction = strtoupper((string) ($this->element['order'] ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
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
                ->where($db->quoteName('project_id') . ' = ' . $projectId)
                ->order($db->quoteName('roundcode') . ' ' . $direction);
            $db->setQuery($query);

            foreach ($db->loadObjectList() ?: [] as $round) {
                $name = trim((string) $round->name);

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
