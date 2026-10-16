<?php
/**
 * Native Joomla 5/6 controller for the DFB-key schedule importer.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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
        $this->assertImportPermission();

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
        $this->assertImportPermission();

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
        $this->assertImportPermission();

        $app = $this->app;
        $post = $app->getInput()->post->getArray();
        $projectId = self::positiveId($post['projectid'] ?? null);
        $divisionId = self::optionalId($post['divisionid'] ?? 0);
        $roundCodes = $this->rows($post, 'roundcode', 500);
        $names = $this->rows($post, 'name', 500);
        $firstDates = $this->rows($post, 'round_date_first', 500);
        $lastDates = $this->rows($post, 'round_date_last', 500);

        if ($projectId <= 0 || $divisionId === null || $roundCodes === []
            || count($names) !== count($roundCodes)
            || ($firstDates !== [] && count($firstDates) !== count($roundCodes))
            || ($lastDates !== [] && count($lastDates) !== count($roundCodes))) {
            throw new \UnexpectedValueException('Invalid DFB import round selection.', 400);
        }

        $modified = Factory::getDate()->toSql();
        $userId = (int) $app->getIdentity()->id;
        $records = [];

        // Validate all rows before writing any of them.
        foreach ($roundCodes as $index => $roundCode) {
            $code = self::positiveId($roundCode);
            $name = $names[$index];
            $first = $firstDates[$index] ?? '';
            $last = $lastDates[$index] ?? '';

            if ($code <= 0 || !is_string($name)
                || trim($name) === '' || strlen($name) > 255
                || !self::validDateInput($first) || !self::validDateInput($last)) {
                throw new \UnexpectedValueException('Invalid DFB import round record.', 400);
            }

            $records[] = (object) [
                'roundcode' => $code,
                'project_id' => $projectId,
                'name' => trim($name),
                'round_date_first' => $this->normaliseDate($first, '0000-00-00'),
                'round_date_last' => $this->normaliseDate($last, '0000-00-00'),
                'modified' => $modified,
                'modified_by' => $userId,
            ];
        }

        $model = $this->getModel();

        if ($model === false) {
            throw new \RuntimeException('DFB import model is unavailable.', 500);
        }

        $db = $model->getDatabase();

        foreach ($records as $record) {
            try {
                if (!$db->insertObject('#__sportsmanagement_round', $record, 'id')) {
                    throw new \RuntimeException('Round insertion failed.');
                }
            } catch (\Throwable) {
                $this->setRedirect(
                    'index.php?option=com_sportsmanagement&view=jlextdfbkeyimport',
                    Text::_('JLIB_DATABASE_ERROR_STORE_FAILED'),
                    'error'
                );

                return false;
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
        $this->assertImportPermission();

        $app = $this->app;
        $post = $app->getInput()->post->getArray();
        $roundIds = $this->rows($post, 'round_id', 5000);
        $matchNumbers = $this->rows($post, 'match_number', 5000);
        $team1Ids = $this->rows($post, 'projectteam1_id', 5000);
        $team2Ids = $this->rows($post, 'projectteam2_id', 5000);
        $matchDates = $this->rows($post, 'match_date', 5000);

        if ($roundIds === [] || count($matchNumbers) !== count($roundIds)
            || count($team1Ids) !== count($roundIds)
            || count($team2Ids) !== count($roundIds)
            || ($matchDates !== [] && count($matchDates) !== count($roundIds))) {
            throw new \UnexpectedValueException('Invalid DFB import match selection.', 400);
        }

        $modified = Factory::getDate()->toSql();
        $userId = (int) $app->getIdentity()->id;
        $records = [];

        foreach ($roundIds as $index => $roundId) {
            $round = self::positiveId($roundId);
            $number = self::positiveId($matchNumbers[$index]);
            $home = self::positiveId($team1Ids[$index]);
            $away = self::positiveId($team2Ids[$index]);
            $date = $matchDates[$index] ?? '';

            if ($round <= 0 || $number <= 0 || $home <= 0 || $away <= 0
                || $home === $away || !self::validDateInput($date)) {
                throw new \UnexpectedValueException('Invalid DFB import match record.', 400);
            }

            $records[] = (object) [
                'round_id' => $round,
                'match_number' => $number,
                'projectteam1_id' => $home,
                'projectteam2_id' => $away,
                'published' => 1,
                'match_date' => $this->normaliseDate($date, null),
                'modified' => $modified,
                'modified_by' => $userId,
            ];
        }

        $model = $this->getModel();

        if ($model === false) {
            throw new \RuntimeException('DFB import model is unavailable.', 500);
        }

        $db = $model->getDatabase();

        foreach ($records as $record) {
            try {
                if (!$db->insertObject('#__sportsmanagement_match', $record, 'id')) {
                    throw new \RuntimeException('Match insertion failed.');
                }
            } catch (\Throwable) {
                $this->setRedirect(
                    'index.php?option=com_sportsmanagement&view=jlextdfbkeyimport',
                    Text::_('JLIB_DATABASE_ERROR_STORE_FAILED'),
                    'error'
                );

                return false;
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

    private function assertImportPermission(): void
    {
        $this->checkToken();
        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    private static function positiveId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);

        if (!preg_match('/^[1-9][0-9]*$/D', $raw)) {
            return 0;
        }

        $id = filter_var($raw, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $id === false ? 0 : $id;
    }

    private static function optionalId(mixed $value): ?int
    {
        if ($value === '' || $value === 0 || $value === '0' || $value === null) {
            return 0;
        }

        $id = self::positiveId($value);

        return $id > 0 ? $id : null;
    }

    private function rows(array $post, string $key, int $maximum): array
    {
        $raw = $post[$key] ?? [];

        if (!is_array($raw) || count($raw) > $maximum) {
            throw new \UnexpectedValueException('Invalid or oversized DFB import rows.', 400);
        }

        return array_values($raw);
    }

    private static function validDateInput(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', trim($value));

        return $date instanceof \DateTimeImmutable
            && $date->format('Y-m-d') === trim($value);
    }

    private function normaliseDate($value, ?string $fallback): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : $fallback;
    }
}
