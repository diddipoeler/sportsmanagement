<?php
/**
 * Native Joomla 5/6 parent position field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\Database\ParameterType;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Language\Text;

/**
 * Parent-position selector for the administrator position form.
 */
final class ParentpositionField extends SportsManagementListField
{
    protected $type = 'parentposition';

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Load root positions eligible for selection as a parent.
     * Excludes the current record, translates position names and retains the
     * explicit root option for positions without a parent.
     * @return array Joomla selection options.
     */
    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $currentId = SportsManagementAdministratorApplicationResolver::resolve()->getInput()->getInt('id', 0);
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position'))
            ->where($db->quoteName('parent_id') . ' = 0')
            ->order($db->quoteName('ordering') . ' ASC');

        if ($currentId > 0) {
            $query->where($db->quoteName('id') . ' <> :currentId')
                ->bind(':currentId', $currentId, ParameterType::INTEGER);
        }

        $db->setQuery($query);
        $options = [
            (object) [
                'value' => '0',
                'text' => Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_IS_P_POSITION'),
            ],
        ];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => Text::_((string) $item->text),
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
