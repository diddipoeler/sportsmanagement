<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native Joomla 5/6 controller for the DFB-key schedule importer. */
final class JlextdfbkeyimportController extends BaseController
{
    public function getdivisionfirst()
    {
        $this->checkToken();

        $post = $this->app->getInput()->post;
        $divisionId = $post->getInt('divisionid');
        $projectId = $post->getInt('projectid');
        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=jlextdfbkeyimport&layout=default'
            . '&pid=' . $projectId . '&divisionid=' . $divisionId
        );

        return true;
    }

    public function apply()
    {
        $this->checkToken();

        $app = $this->app;
        $post = $app->getInput()->post->getArray();
        $app->setUserState('com_sportsmanagement.first_post', $post);
        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=jlextdfbkeyimport&layout=default_savematchdays'
            . '&pid=' . (int) ($post['projectid'] ?? 0)
            . '&divisionid=' . (int) ($post['divisionid'] ?? 0)
        );

        return true;
    }

    public function save()
    {
        $this->checkToken();

        $app = $this->app;
        $post = $app->getInput()->post->getArray();
        $model = $this->getModel();
        $db = $model->getDatabase();
        $roundCodes = array_values((array) ($post['roundcode'] ?? []));
        $names = array_values((array) ($post['name'] ?? []));
        $firstDates = array_values((array) ($post['round_date_first'] ?? []));
        $lastDates = array_values((array) ($post['round_date_last'] ?? []));
        $projectId = (int) ($post['projectid'] ?? 0);
        $divisionId = (int) ($post['divisionid'] ?? 0);
        $modified = Factory::getDate()->toSql();
        $userId = (int) $app->getIdentity()->id;

        foreach ($roundCodes as $index => $roundCode) {
            $record = (object) [
                'roundcode' => (int) $roundCode,
                'project_id' => $projectId,
                'name' => (string) ($names[$index] ?? ''),
                'round_date_first' => $this->normaliseDate($firstDates[$index] ?? null, '0000-00-00'),
                'round_date_last' => $this->normaliseDate($lastDates[$index] ?? null, '0000-00-00'),
                'modified' => $modified,
                'modified_by' => $userId,
            ];

            try {
                $db->insertObject('#__sportsmanagement_round', $record, 'id');
            } catch (\Throwable $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
            }
        }

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=jlextdfbkeyimport&layout=default_firstmatchday'
            . '&pid=' . $projectId . '&divisionid=' . $divisionId
        );

        return true;
    }

    public function insert()
    {
        $this->checkToken();

        $app = $this->app;
        $post = $app->getInput()->post->getArray();
        $model = $this->getModel();
        $db = $model->getDatabase();
        $roundIds = array_values((array) ($post['round_id'] ?? []));
        $matchNumbers = array_values((array) ($post['match_number'] ?? []));
        $team1Ids = array_values((array) ($post['projectteam1_id'] ?? []));
        $team2Ids = array_values((array) ($post['projectteam2_id'] ?? []));
        $matchDates = array_values((array) ($post['match_date'] ?? []));
        $modified = Factory::getDate()->toSql();
        $userId = (int) $app->getIdentity()->id;

        foreach ($roundIds as $index => $roundId) {
            $record = (object) [
                'round_id' => (int) $roundId,
                'match_number' => (int) ($matchNumbers[$index] ?? 0),
                'projectteam1_id' => (int) ($team1Ids[$index] ?? 0),
                'projectteam2_id' => (int) ($team2Ids[$index] ?? 0),
                'published' => 1,
                'match_date' => $this->normaliseDate($matchDates[$index] ?? null, null),
                'modified' => $modified,
                'modified_by' => $userId,
            ];

            try {
                $db->insertObject('#__sportsmanagement_match', $record, 'id');
            } catch (\Throwable $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
            }
        }

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=rounds',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INFO_1')
        );

        return true;
    }

    public function getModel($name = 'Jlextdfbkeyimport', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }

    private function normaliseDate($value, ?string $fallback): ?string
    {
        $timestamp = strtotime((string) $value);

        return $timestamp ? date('Y-m-d', $timestamp) : $fallback;
    }
}
