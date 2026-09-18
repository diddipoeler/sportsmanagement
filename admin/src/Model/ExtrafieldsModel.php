<?php
/**
 * Joomla 5/6 administrator extra-fields list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

/** Native Joomla 5/6 list model for SportsManagement extra fields. */
final class ExtrafieldsModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'obj.name', 'name',
            'obj.template_backend', 'template_backend',
            'obj.template_frontend', 'template_frontend',
            'obj.views_backend', 'views_backend',
            'obj.fieldtyp', 'fieldtyp',
            'obj.field_type', 'field_type',
            'obj.published', 'published', 'state',
            'obj.ordering', 'ordering',
            'obj.id', 'id',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'obj.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);
        $app = $this->administratorApplication();
        $this->setState('filter.search', $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
        $this->setState('filter.state', $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('obj.id'),
                $db->quoteName('obj.name'),
                $db->quoteName('obj.template_backend'),
                $db->quoteName('obj.template_frontend'),
                $db->quoteName('obj.views_backend'),
                $db->quoteName('obj.fieldtyp'),
                $db->quoteName('obj.field_type'),
                $db->quoteName('obj.views_backend_field'),
                $db->quoteName('obj.select_columns'),
                $db->quoteName('obj.select_values'),
                $db->quoteName('obj.published'),
                $db->quoteName('obj.ordering'),
                $db->quoteName('obj.checked_out'),
                $db->quoteName('obj.checked_out_time'),
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_user_extra_fields', 'obj'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('obj.checked_out'));

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $token = '%' . $db->escape($search, true) . '%';
            $query->where('LOWER(' . $db->quoteName('obj.name') . ') LIKE LOWER(:extraFieldSearch)')
                ->bind(':extraFieldSearch', $token, ParameterType::STRING);
        }

        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $published = (int) $state;
            $query->where($db->quoteName('obj.published') . ' = :extraFieldPublished')
                ->bind(':extraFieldPublished', $published, ParameterType::INTEGER);
        }

        $ordering = (string) $this->getState('list.ordering', 'obj.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $orderMap = [
            'obj.name' => $db->quoteName('obj.name'), 'name' => $db->quoteName('obj.name'),
            'obj.template_backend' => $db->quoteName('obj.template_backend'), 'template_backend' => $db->quoteName('obj.template_backend'),
            'obj.template_frontend' => $db->quoteName('obj.template_frontend'), 'template_frontend' => $db->quoteName('obj.template_frontend'),
            'obj.views_backend' => $db->quoteName('obj.views_backend'), 'views_backend' => $db->quoteName('obj.views_backend'),
            'obj.fieldtyp' => $db->quoteName('obj.fieldtyp'), 'fieldtyp' => $db->quoteName('obj.fieldtyp'),
            'obj.field_type' => $db->quoteName('obj.field_type'), 'field_type' => $db->quoteName('obj.field_type'),
            'obj.published' => $db->quoteName('obj.published'), 'published' => $db->quoteName('obj.published'), 'state' => $db->quoteName('obj.published'),
            'obj.ordering' => $db->quoteName('obj.ordering'), 'ordering' => $db->quoteName('obj.ordering'),
            'obj.id' => $db->quoteName('obj.id'), 'id' => $db->quoteName('obj.id'),
        ];
        $query->order(($orderMap[$ordering] ?? $orderMap['obj.name']) . ' ' . $direction);

        return $query;
    }

    public function getExtraFieldsProject(int $projectId = 0): string
    {
        $projectTemplate = 'project';
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('ef.name'))
            ->from($db->quoteName('#__sportsmanagement_user_extra_fields_values', 'ev'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_user_extra_fields', 'ef')
                . ' ON ' . $db->quoteName('ef.id') . ' = ' . $db->quoteName('ev.field_id')
            )
            ->where($db->quoteName('ev.jl_id') . ' = :extraFieldProjectId')
            ->where($db->quoteName('ef.template_backend') . ' = :extraFieldProjectTemplate')
            ->where($db->quoteName('ev.fieldvalue') . ' <> ' . $db->quote(''))
            ->bind(':extraFieldProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':extraFieldProjectTemplate', $projectTemplate, ParameterType::STRING);
        $db->setQuery($query);

        return implode('<br>', $db->loadColumn() ?: []);
    }

    public function getExtraFields(string $templateBackend = '', string $templateFrontend = ''): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([$db->quoteName('id'), $db->quoteName('name')])
            ->from($db->quoteName('#__sportsmanagement_user_extra_fields'))
            ->order($db->quoteName('name') . ' ASC');

        if ($templateBackend !== '') {
            $query->where($db->quoteName('template_backend') . ' = :templateBackend')
                ->bind(':templateBackend', $templateBackend, ParameterType::STRING);
        }
        if ($templateFrontend !== '') {
            $query->where($db->quoteName('template_frontend') . ' = :templateFrontend')
                ->bind(':templateFrontend', $templateFrontend, ParameterType::STRING);
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }
}
