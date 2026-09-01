<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

final class ProjectroundsField extends SportsManagementListField
{
    protected $type = 'projectrounds';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'));

        $projectId = (int) $this->form->getValue('project');

        if ($projectId > 0) {
            $query->where($db->quoteName('project_id') . ' = ' . $projectId);
        }

        $query->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => (string) $item->text,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
