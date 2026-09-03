<?php
/**
 * Native Joomla 5/6 administrator model for divisions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

final class DivisionModel extends SportsManagementAdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        $form = parent::getForm($data, $loadData);

        if (!$form) {
            return false;
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $mediaTool = trim((string) $params->get('cfg_which_media_tool', 'media')) ?: 'media';
        $directory = ($mediaTool === 'media' ? 'local-0:/' : '') . 'com_sportsmanagement/database/divisions';

        $form->setFieldAttribute('picture', 'default', (string) $params->get('ph_icon', ''));
        $form->setFieldAttribute('picture', 'directory', $directory);
        $form->setFieldAttribute('picture', 'type', $mediaTool);

        return $form;
    }

    public function divisiontoproject()
    {
        $app = $this->administratorApplication();
        $post = $app->getInput()->post->getArray();
        $divisionIds = array_values(array_unique(array_filter(array_map('intval', (array) ($post['cid'] ?? [])))));
        $projectId = (int) ($post['pid'] ?? 0);

        if (!$divisionIds || $projectId <= 0) {
            return '';
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('p.*')
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.id') . ' = ' . $projectId);
        $db->setQuery($query);
        $sourceProject = $db->loadObject();

        if (!$sourceProject) {
            return '';
        }

        foreach ($divisionIds as $divisionId) {
            try {
                $query = $db->getQuery(true)
                    ->select($db->quoteName('dv.name'))
                    ->from($db->quoteName('#__sportsmanagement_division', 'dv'))
                    ->where($db->quoteName('dv.project_id') . ' = ' . $projectId)
                    ->where($db->quoteName('dv.id') . ' = ' . $divisionId);
                $db->setQuery($query);
                $divisionName = (string) $db->loadResult();

                if ($divisionName === '') {
                    continue;
                }

                $newProject = clone $sourceProject;
                $newProject->id = 0;
                $newProject->name = trim((string) $sourceProject->name . ' ' . $divisionName);
                $newProject->project_type = 'SIMPLE_LEAGUE';
                $newProject->alias = OutputFilter::stringURLSafe($newProject->name);
                $newProject->published = 1;
                $newProject->checked_out = 0;
                $newProject->checked_out_time = $db->getNullDate();
                $newProject->modified = Factory::getDate()->toSql();
                $newProject->modified_by = (int) $app->getIdentity()->id;

                $db->insertObject('#__sportsmanagement_project', $newProject, 'id');
                $newProjectId = (int) ($newProject->id ?? 0);

                if ($newProjectId <= 0) {
                    $newProjectId = (int) $db->insertid();
                }

                if ($newProjectId <= 0) {
                    continue;
                }

                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__sportsmanagement_division'))
                    ->set($db->quoteName('project_id') . ' = ' . $newProjectId)
                    ->where($db->quoteName('id') . ' = ' . $divisionId)
                    ->where($db->quoteName('project_id') . ' = ' . $projectId);
                $db->setQuery($query)->execute();

                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__sportsmanagement_project_team'))
                    ->set($db->quoteName('project_id') . ' = ' . $newProjectId)
                    ->where($db->quoteName('division_id') . ' = ' . $divisionId)
                    ->where($db->quoteName('project_id') . ' = ' . $projectId);
                $db->setQuery($query)->execute();
            } catch (\Throwable $e) {
                $app->enqueueMessage(
                    Text::sprintf(
                        'COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED',
                        $e->getCode(),
                        $e->getMessage()
                    ),
                    'error'
                );
            }
        }

        return '';
    }

    public function massadd()
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $post = $input->post->getArray();
        $projectId = (int) $app->getUserState(
            'com_sportsmanagement.pid',
            $input->getInt('pid', 0)
        );
        $count = max(0, (int) ($post['add_division_count'] ?? 0));

        if ($count === 0 || $projectId <= 0) {
            return '';
        }

        $nextOrdering = $this->getMaxDivision($projectId) + 1;
        $created = 0;

        for ($i = 0; $i < $count; $i++, $nextOrdering++) {
            $table = $this->getTable();
            $table->project_id = $projectId;
            $table->ordering = $nextOrdering;
            $table->name = Text::sprintf(
                'COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_CTRL_DIVISION_NAME',
                $nextOrdering
            );
            $table->alias = OutputFilter::stringURLSafe($table->name);
            $table->modified = Factory::getDate()->toSql();
            $table->modified_by = (int) $app->getIdentity()->id;

            try {
                if (!$table->check() || !$table->store()) {
                    throw new \RuntimeException($table->getError());
                }

                $created++;
            } catch (\Throwable $e) {
                $app->enqueueMessage(
                    Text::sprintf(
                        'COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED',
                        $e->getCode(),
                        $e->getMessage()
                    ),
                    'notice'
                );

                return Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_CTRL_ERROR_ADD') . $e->getMessage();
            }
        }

        return Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_CTRL_ROUNDS_ADDED', $created);
    }

    public function getMaxDivision($project_id)
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COALESCE(MAX(' . $db->quoteName('ordering') . '), 0)')
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    public function count_teams_division($division_id = 0)
    {
        $divisionId = (int) $division_id;

        if ($divisionId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $teamIds = [];

        foreach (['projectteam1_id', 'projectteam2_id'] as $field) {
            $query = $db->getQuery(true)
                ->select($db->quoteName($field))
                ->from($db->quoteName('#__sportsmanagement_match'))
                ->where($db->quoteName('division_id') . ' = ' . $divisionId)
                ->where($db->quoteName($field) . ' > 0')
                ->group($db->quoteName($field));
            $db->setQuery($query);

            foreach ($db->loadColumn() ?: [] as $id) {
                $teamIds[(int) $id] = true;
            }
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_project_team'))
            ->where($db->quoteName('division_id') . ' = ' . $divisionId);
        $db->setQuery($query);

        foreach ($db->loadColumn() ?: [] as $id) {
            $teamIds[(int) $id] = true;
        }

        return count($teamIds);
    }

    public function saveshort()
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $input->post->get('cid', [], 'array')))));
        $post = $input->post->getArray();

        if (!$ids) {
            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_SAVE_NO_SELECT');
        }

        foreach ($ids as $id) {
            $table = $this->getTable();

            if (!$table->load($id)) {
                return false;
            }

            $table->name = (string) ($post['name' . $id] ?? $table->name);
            $table->alias = OutputFilter::stringURLSafe($table->name);
            $table->modified = Factory::getDate()->toSql();
            $table->modified_by = (int) $app->getIdentity()->id;

            if (!$table->check() || !$table->store()) {
                $this->setError($table->getError());
                return false;
            }
        }

        return Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_SAVE');
    }

    protected function prepareSportsManagementData(array $data): array
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $post = $input->post->getArray();

        if (empty($data['id'])) {
            $data['project_id'] = (int) $app->getUserState(
                'com_sportsmanagement.pid',
                $input->getInt('pid', 0)
            );
        }

        if (isset($post['extended']) && is_array($post['extended'])) {
            $registry = new Registry();
            $registry->loadArray($post['extended']);
            $data['rankingparams'] = (string) $registry;
        }

        if (!empty($data['picture'])) {
            $data['picture'] = MediaHelper::getCleanMediaFieldValue((string) $data['picture']);
        }

        return parent::prepareSportsManagementData($data);
    }
}
