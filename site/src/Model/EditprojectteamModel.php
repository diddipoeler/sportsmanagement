<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\ProjectteamTable;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\AdminModel;

/** Joomla 5/6 frontend model for editing project teams. */
final class EditprojectteamModel extends AdminModel
{
    public $latitude = null;
    public $longitude = null;
    public $name = 'editprojectteam';

    private ?ProjectteamTable $projectTeam = null;

    public function updItem($data): bool
    {
        $data = (array) $data;
        $request = $data['request'] ?? null;

        if (is_array($request)) {
            foreach ($request as $key => $value) {
                $data[$key] = $value;
            }
        } elseif (is_object($request) && method_exists($request, 'getArray')) {
            foreach ((array) $request->getArray() as $key => $value) {
                $data[$key] = $value;
            }
        }

        unset($data['request']);

        $db = $this->getDatabase();
        $db->transactionStart();

        try {
            $table = $this->getTable('projectteam');

            if (!$table->bind($data) || !$table->check() || !$table->store()) {
                throw new \RuntimeException((string) $table->getError());
            }

            $seasonTeamId = (int) ($table->team_id ?? $data['team_id'] ?? 0);

            if ($seasonTeamId > 0 && array_key_exists('picture', $data)) {
                $picture = (string) $data['picture'];
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__sportsmanagement_project_team'))
                    ->set($db->quoteName('picture') . ' = ' . $db->quote($picture))
                    ->where($db->quoteName('team_id') . ' = ' . $seasonTeamId);
                $db->setQuery($query)->execute();

                $db->updateObject(
                    '#__sportsmanagement_season_team_id',
                    (object) [
                        'id' => $seasonTeamId,
                        'picture' => $picture,
                    ],
                    'id'
                );
            }

            $db->transactionCommit();
            $this->projectTeam = $table;

            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());
            Log::add($e->getMessage(), Log::ERROR, 'jsmerror');

            return false;
        }
    }

    public function getTeamInfo(int $projectTeamId): ?object
    {
        if ($projectTeamId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.id'),
                $db->quoteName('t.name'),
                $db->quoteName('t.short_name'),
                $db->quoteName('t.middle_name'),
                $db->quoteName('t.picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->where($db->quoteName('pt.id') . ' = ' . $projectTeamId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public function getTable($type = 'projectteam', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'projectteam') === 0) {
            return new ProjectteamTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function getForm($data = [], $loadData = true)
    {
        FormHelper::addFormPath(JPATH_SITE . '/components/com_sportsmanagement/models/forms');
        FormHelper::addFieldPrefix('Diddipoeler\\Component\\SportsManagement\\Administrator\\Field');

        $form = $this->loadForm(
            'com_sportsmanagement.' . $this->name,
            $this->name,
            ['load_data' => $loadData]
        );

        return $form ?: false;
    }

    protected function loadFormData()
    {
        $app = $this->siteApplication();
        $data = $app->getUserState('com_sportsmanagement.edit.' . $this->name . '.data', []);

        return empty($data) ? $this->getData() : $data;
    }

    public function getData(): ProjectteamTable
    {
        if ($this->projectTeam === null) {
            $projectTeamId = $this->siteApplication()->getInput()->getInt('ptid', 0);
            $this->projectTeam = $this->getTable('projectteam');

            if ($projectTeamId > 0) {
                $this->projectTeam->load($projectTeamId);
            }
        }

        return $this->projectTeam;
    }

    private function siteApplication(): SiteApplication
    {
        return Factory::getContainer()->get(SiteApplication::class);
    }
}
