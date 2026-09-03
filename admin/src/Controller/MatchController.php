<?php
/**
 * Native Joomla 5/6 form controller for one match.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Service\GoogleCalendarMatchSynchronizer;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\Archive\Archive;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

/** Native Joomla 5/6 form controller for one match. */
final class MatchController extends SportsManagementFormController
{
    private const MAX_PRESS_REPORT_BYTES = 52428800;

    public function cancelmassadd(): void
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=matches&massadd=0');
    }

    public function massadd(): void
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=matches&layout=massadd&massadd=1');
    }

    public function remove()
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $pks = array_values(array_filter(array_map(
            'intval',
            (array) $input->post->get('cid', [], 'array')
        )));
        $model = $this->getModel('Match', 'Administrator', ['ignore_request' => false]);
        $success = $model !== false && $pks !== [] && $model->delete($pks);

        if (!$success) {
            $message = $model && method_exists($model, 'getError') && $model->getError()
                ? (string) $model->getError()
                : Text::_('JLIB_APPLICATION_ERROR_DELETE_FAILED');
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=matches',
                $message,
                'error'
            );

            return false;
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=matches');

        return true;
    }

    public function picture()
    {
        $matchId = $this->app->getInput()->getInt('id', 0);
        $destination = JPATH_ROOT . '/images/com_sportsmanagement/database/matchreport/' . $matchId;

        if (!Folder::exists($destination) && !Folder::create($destination)) {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=matches',
                Text::_('JLIB_FILESYSTEM_ERROR_FOLDER_CREATE'),
                'error'
            );

            return false;
        }

        $folder = 'matchreport/' . $matchId;
        $this->setRedirect(
            'index.php?option=com_media&view=images&tmpl=component&asset=com_sportsmanagement&author=&folder=com_sportsmanagement/database/' . $folder,
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_MATCHPICTURE')
        );

        return true;
    }

    public function copyfrom()
    {
        $this->checkToken();

        $app = $this->app;
        $input = $app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement');
        $post = $input->post->getArray();
        $projectId = (int) $app->getUserState($option . '.pid', 0);
        $roundId = $input->getInt('rid');
        $model = $this->getModel('Match', 'Administrator', ['ignore_request' => false]);
        $redirect = 'index.php?option=com_sportsmanagement&view=matches';

        if ($model === false) {
            $this->setRedirect($redirect, Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'), 'error');

            return false;
        }

        $post['project_id'] = $projectId;
        $post['round_id'] = $roundId;
        $addType = (int) ($post['addtype'] ?? 0);
        $success = true;
        $message = '';

        try {
            /** @var DatabaseInterface $joomlaDatabase */
            $joomlaDatabase = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);
            $databaseSelector = $input->getInt(
                'cfg_which_database',
                (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
            );
            $db = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $databaseSelector);

            if ($addType === 1) {
                $addMatchCount = max(0, (int) ($post['add_match_count'] ?? 0));
                $projectRounds = $model->getProjectRoundCodes($projectId);

                if ($addMatchCount <= 0 || !$projectRounds) {
                    $success = false;
                    $message = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_ADD_MATCH');
                } else {
                    if (!empty($post['autoPublish'])) {
                        $post['published'] = 1;
                    }

                    $convertedDate = SportsManagementDateHelper::convertDate(
                        (string) ($post['match_date'] ?? ''),
                        0
                    );
                    $post['match_date'] = trim($convertedDate . ' ' . (string) ($post['startTime'] ?? ''));
                    $matchNumber = max(1, $input->getInt('firstMatchNumber', 1));
                    $roundFound = false;
                    $saved = 0;

                    foreach ($projectRounds as $projectRound) {
                        if ((int) $projectRound->id === $roundId) {
                            $roundFound = true;
                        }

                        if (!$roundFound) {
                            continue;
                        }

                        for ($index = 0; $index < $addMatchCount; $index++) {
                            $matchData = $post;
                            $matchData['id'] = 0;
                            $matchData['round_id'] = (int) $projectRound->id;
                            $matchData['roundcode'] = (int) ($projectRound->roundcode ?? 0);

                            if (!empty($post['firstMatchNumber'])) {
                                $matchData['match_number'] = $matchNumber;
                            }

                            if (!$model->save($matchData)) {
                                $success = false;
                                $message = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_ADD_MATCH');
                                break 2;
                            }

                            $saved++;
                            $matchNumber++;
                        }

                        if (empty($post['addToRound'])) {
                            break;
                        }
                    }

                    if ($saved === 0) {
                        $success = false;
                        $message = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_ADD_MATCH');
                    } elseif ($success) {
                        $message = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_MATCH');
                    }
                }
            } elseif ($addType === 2) {
                $matches = $model->getRoundMatches($roundId);

                if (!$matches) {
                    $success = false;
                    $message = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_COPY_MATCH2');
                } else {
                    $convertedDate = SportsManagementDateHelper::convertDate(
                        (string) ($post['date'] ?? ''),
                        0
                    );
                    $matchDate = trim($convertedDate . ' ' . (string) ($post['startTime'] ?? ''));
                    $targetRoundId = $roundId;

                    if (!empty($post['create_new'])) {
                        $round = new \stdClass();
                        $round->project_id = $projectId;
                        $round->roundcode = '';
                        $round->name = (string) ($post['start_round_name'] ?? '');
                        $round->modified = Factory::getDate()->toSql();
                        $round->modified_by = (int) $app->getIdentity()->id;
                        $db->insertObject('#__sportsmanagement_round', $round);
                        $targetRoundId = (int) $db->insertid();
                    }

                    $startMatchNumber = $post['start_match_number'] ?? '';

                    foreach ($matches as $match) {
                        $matchData = [
                            'id' => 0,
                            'match_date' => $matchDate,
                            'projectteam1_id' => !empty($post['mirror'])
                                ? (int) $match->projectteam2_id
                                : (int) $match->projectteam1_id,
                            'projectteam2_id' => !empty($post['mirror'])
                                ? (int) $match->projectteam1_id
                                : (int) $match->projectteam2_id,
                            'project_id' => $projectId,
                            'round_id' => $targetRoundId,
                        ];

                        if ($startMatchNumber !== '') {
                            $matchData['match_number'] = (int) $startMatchNumber;
                            $startMatchNumber = (int) $startMatchNumber + 1;
                        }

                        if (!$model->save($matchData)) {
                            $success = false;
                        }
                    }

                    $message = Text::_($success
                        ? 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_COPY_MATCH'
                        : 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_COPY_MATCH');
                }
            } else {
                $success = false;
                $message = Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED');
            }
        } catch (\Throwable $exception) {
            Log::add(__METHOD__ . ': ' . $exception->getMessage(), Log::ERROR, 'jsmerror');
            $success = false;
            $message = $exception->getMessage();
        }

        $this->setRedirect($redirect, $message, $success ? 'message' : 'error');

        return $success;
    }

    public function addmatch()
    {
        $this->checkToken();

        $app = $this->app;
        $input = $app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement');
        $data = $input->post->getArray();
        $data['project_id'] = (int) $app->getUserState($option . '.pid', 0);
        $data['round_id'] = (int) $app->getUserState($option . '.rid', 0);
        $data['count_result'] = 1;
        $data['published'] = 1;
        $data['summary'] = '-';
        $data['preview'] = '-';

        $model = $this->getModel('Match', 'Administrator', ['ignore_request' => false]);
        $success = $model !== false && $model->save($data);
        $message = $success
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_MATCH')
            : Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_ADD_MATCH')
                . ($model && method_exists($model, 'getError') ? (string) $model->getError() : '');

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=matches',
            $message,
            $success ? 'message' : 'error'
        );

        return $success;
    }

    public function insertgooglecalendar()
    {
        $this->checkToken();

        $app = $this->app;
        $input = $app->getInput();
        $matchIds = (array) $input->post->get('cid', [], 'array');
        $projectId = $input->post->getInt('project_id');
        $calendarId = $input->post->getInt('calendar_id');
        $redirect = 'index.php?option=com_sportsmanagement&view=matches';

        if ($calendarId <= 0) {
            $this->setRedirect(
                $redirect,
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_NO_GOOGLECALENDAR_ID'),
                'warning'
            );

            return false;
        }

        try {
            /** @var DatabaseInterface $joomlaDatabase */
            $joomlaDatabase = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);
            $databaseSelector = $input->getInt(
                'cfg_which_database',
                (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
            );
            $db = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $databaseSelector);
            $synchronizer = new GoogleCalendarMatchSynchronizer($db);
            $synchronizer->synchronize($matchIds, $projectId, $calendarId);

            $this->setRedirect(
                $redirect,
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_GOOGLE_EVENT'),
                'message'
            );

            return true;
        } catch (\Throwable $exception) {
            Log::add(__METHOD__ . ': ' . $exception->getMessage(), Log::ERROR, 'jsmerror');
            $this->setRedirect($redirect, $exception->getMessage(), 'error');

            return false;
        }
    }

    public function pressebericht()
    {
        $input = $this->app->getInput();
        $input->set('hidemainmenu', 1);
        $input->set('layout', 'pressebericht');
        $input->set('view', 'match');
        $input->set('edit', true);

        return parent::display();
    }

    public function savepressebericht()
    {
        $this->checkToken();

        $input = $this->app->getInput();
        $matchId = $input->getInt('match_id', $input->getInt('id', 0));
        $upload = (array) $input->files->get('import_package', [], 'array');
        $failureRedirect = 'index.php?option=com_sportsmanagement&view=match&layout=pressebericht'
            . '&tmpl=component&id=' . $matchId;
        $successRedirect = 'index.php?option=com_sportsmanagement&view=match&layout=readpressebericht'
            . '&tmpl=component&match_id=' . $matchId;

        if ($matchId <= 0
            || (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            || empty($upload['tmp_name'])
            || !is_uploaded_file((string) $upload['tmp_name'])) {
            $this->setRedirect(
                $failureRedirect,
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_CANT_UPLOAD'),
                'error'
            );

            return false;
        }

        $originalName = basename(str_replace('\\', '/', (string) ($upload['name'] ?? '')));
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, ['zip', 'csv'], true)) {
            $this->setRedirect(
                $failureRedirect,
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_WRONG_EXTENSION'),
                'error'
            );

            return false;
        }

        $uploadSize = (int) ($upload['size'] ?? 0);
        $maxBytes = $this->maxPressReportBytes();

        if ($uploadSize <= 0 || $uploadSize > $maxBytes) {
            $this->setRedirect(
                $failureRedirect,
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_CANT_UPLOAD'),
                'error'
            );

            return false;
        }

        $targetDirectory = JPATH_SITE
            . DIRECTORY_SEPARATOR . 'media'
            . DIRECTORY_SEPARATOR . 'com_sportsmanagement'
            . DIRECTORY_SEPARATOR . 'pressebericht';

        if (!Folder::exists($targetDirectory) && !Folder::create($targetDirectory)) {
            $this->setRedirect(
                $failureRedirect,
                Text::_('JLIB_FILESYSTEM_ERROR_FOLDER_CREATE'),
                'error'
            );

            return false;
        }

        try {
            $token = bin2hex(random_bytes(12));
        } catch (\Throwable $exception) {
            Log::add(__METHOD__ . ': ' . $exception->getMessage(), Log::ERROR, 'jsmerror');
            $this->setRedirect($failureRedirect, Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'), 'error');

            return false;
        }

        $temporaryDirectory = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp'
            . DIRECTORY_SEPARATOR . 'sportsmanagement-pressebericht-' . $token;

        if (!Folder::create($temporaryDirectory)) {
            $this->setRedirect(
                $failureRedirect,
                Text::_('JLIB_FILESYSTEM_ERROR_FOLDER_CREATE'),
                'error'
            );

            return false;
        }

        $stagedUpload = $temporaryDirectory . DIRECTORY_SEPARATOR . 'upload.' . $extension;
        $sourceReport = null;

        try {
            if (!File::upload((string) $upload['tmp_name'], $stagedUpload)) {
                throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_CANT_UPLOAD'));
            }

            if ($extension === 'csv') {
                $sourceReport = $stagedUpload;
            } else {
                $extractDirectory = $temporaryDirectory . DIRECTORY_SEPARATOR . 'extracted';

                if (!Folder::create($extractDirectory)) {
                    throw new \RuntimeException(Text::_('JLIB_FILESYSTEM_ERROR_FOLDER_CREATE'));
                }

                $archive = new Archive();

                if ($archive->extract($stagedUpload, $extractDirectory) === false) {
                    throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_EXTRACT_ERROR'));
                }

                $candidates = Folder::files($extractDirectory, '\.jlg$', true, true);

                foreach ($candidates as $candidate) {
                    if ($this->isSafePressReportPath($extractDirectory, (string) $candidate)
                        && $this->isValidPressReportFile((string) $candidate, $maxBytes)) {
                        $sourceReport = (string) $candidate;
                        break;
                    }
                }

                if ($sourceReport === null) {
                    throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_EXTRACT_NOJLG'));
                }
            }

            if (!$this->isValidPressReportFile($sourceReport, $maxBytes)) {
                throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_CANT_UPLOAD'));
            }

            $target = $targetDirectory . DIRECTORY_SEPARATOR . $matchId . '.jlg';
            $replacement = $targetDirectory . DIRECTORY_SEPARATOR . '.' . $matchId . '-' . $token . '.jlg';

            if (!File::copy($sourceReport, $replacement)) {
                throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_RENAME_FAILED'));
            }

            if (File::exists($target) && !File::delete($target)) {
                File::delete($replacement);
                throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_RENAME_FAILED'));
            }

            if (!File::move($replacement, $target)) {
                File::delete($replacement);
                throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_RENAME_FAILED'));
            }
        } catch (\Throwable $exception) {
            Log::add(__METHOD__ . ': ' . $exception->getMessage(), Log::WARNING, 'jsmerror');
            $this->setRedirect($failureRedirect, $exception->getMessage(), 'error');

            return false;
        } finally {
            if (Folder::exists($temporaryDirectory)) {
                try {
                    Folder::delete($temporaryDirectory);
                } catch (\Throwable $cleanupException) {
                    Log::add(__METHOD__ . ': cleanup failed: ' . $cleanupException->getMessage(), Log::WARNING, 'jsmerror');
                }
            }
        }

        $this->setRedirect($successRedirect);

        return true;
    }

    private function maxPressReportBytes(): int
    {
        $configured = (int) ComponentHelper::getParams('com_media')->get('upload_maxsize', 10);

        if ($configured <= 0) {
            return 10 * 1024 * 1024;
        }

        $bytes = $configured >= 1024 * 1024
            ? $configured
            : $configured * 1024 * 1024;

        return min($bytes, self::MAX_PRESS_REPORT_BYTES);
    }

    private function isSafePressReportPath(string $baseDirectory, string $candidate): bool
    {
        $base = realpath($baseDirectory);
        $path = realpath($candidate);

        if ($base === false || $path === false || !is_file($path)) {
            return false;
        }

        $base = rtrim(str_replace('\\', '/', $base), '/') . '/';
        $path = str_replace('\\', '/', $path);

        return str_starts_with($path, $base);
    }

    private function isValidPressReportFile(string $path, int $maxBytes): bool
    {
        if (!is_file($path)) {
            return false;
        }

        $size = filesize($path);

        if ($size === false || $size <= 0 || $size > $maxBytes) {
            return false;
        }

        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            return false;
        }

        try {
            $sample = fread($handle, min(8192, $size));
        } finally {
            fclose($handle);
        }

        return is_string($sample) && $sample !== '' && !str_contains($sample, "\0");
    }
}
