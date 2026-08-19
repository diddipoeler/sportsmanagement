<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 administrator model for prediction-game members. */
final class PredictionmemberModel extends SportsManagementAdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return $this->loadForm(
            'com_sportsmanagement.predictionmember',
            'predictionmember',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    public function getTable($type = 'predictionmember', $prefix = 'sportsmanagementTable', $config = [])
    {
        $config['dbo'] = $this->getDatabase();

        return Table::getInstance($type, $prefix, $config);
    }

    /** Add selected Joomla users to a prediction game, preserving existing members. */
    public function save_memberlist(array $memberIds = [], int $predictionId = 0): int
    {
        $input = Factory::getApplication()->getInput();

        if (!$memberIds) {
            $memberIds = (array) $input->post->get('prediction_members', [], 'array');
        }

        if ($predictionId <= 0) {
            $predictionId = $input->post->getInt('cid');
        }

        $memberIds = $this->normaliseIds($memberIds);

        if ($predictionId <= 0 || !$memberIds) {
            return 0;
        }

        $db = $this->getDatabase();
        $date = Factory::getDate()->toSql();
        $userId = (int) Factory::getApplication()->getIdentity()->id;
        $errors = 0;

        foreach ($memberIds as $memberId) {
            try {
                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__sportsmanagement_prediction_member'))
                    ->where($db->quoteName('prediction_id') . ' = ' . $predictionId)
                    ->where($db->quoteName('user_id') . ' = ' . $memberId);
                $db->setQuery($query);

                if ((int) $db->loadResult() > 0) {
                    continue;
                }

                $record = (object) [
                    'prediction_id' => $predictionId,
                    'user_id' => $memberId,
                    'registerDate' => $date,
                    'approved' => 1,
                    'fav_team' => '',
                    'champ_tipp' => '',
                    'final4_tipp' => '',
                    'modified' => $date,
                    'modified_by' => $userId,
                ];
                $db->insertObject('#__sportsmanagement_prediction_member', $record);
            } catch (\Throwable $e) {
                Factory::getApplication()->enqueueMessage(
                    Text::sprintf(
                        'COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED',
                        $e->getCode(),
                        $e->getMessage()
                    ),
                    'error'
                );
                $errors++;
            }
        }

        return $errors;
    }

    /** Send prediction reminder content through Joomla's current mailer factory. */
    public function sendEmailtoMembers($cid, $prediction_id): int
    {
        $memberIds = $this->normaliseIds((array) $cid);
        $predictionId = (int) $prediction_id;

        if (!$memberIds || $predictionId <= 0) {
            return 0;
        }

        $entryConfig = $this->getPredictionTemplateConfig($predictionId, 'predictionentry');
        $overallConfig = $this->getPredictionTemplateConfig($predictionId, 'predictionoverall');
        $configPrediction = array_merge($overallConfig, $entryConfig);
        $predictionProject = $this->getFirstPredictionProject($predictionId);
        $predictionGame = $this->getPredictionGame($predictionId);
        $projectIds = $this->getPredictionProjectIds($predictionId);

        if (!$predictionProject || !$predictionGame || !$projectIds) {
            return 0;
        }

        $app = Factory::getApplication();
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $reminderText = (string) $componentParams->get('pred_reminder_mail_text', '');
        $config = Factory::getContainer()->get('config');
        $senderEmail = (string) $config->get('mailfrom');
        $senderName = (string) $config->get('fromname');
        $mailerFactory = Factory::getContainer()->get(MailerFactoryInterface::class);
        $sent = 0;

        foreach ($memberIds as $memberId) {
            $member = $this->getPredictionMemberContact($memberId);

            if (!$member || empty($member->email)) {
                continue;
            }

            $reminderFound = 0;
            $fromDate = '';
            $projectCount = 0;
            $body = '<html>';

            foreach ($projectIds as $projectId) {
                $settings = $this->getPredictionProjectSettings($predictionId, $projectId);

                if (!$settings) {
                    continue;
                }

                $matches = $this->getPredictionGamesMatches($predictionId, $projectId, (int) $member->user_id);
                $totalPoints = 0;
                $lastMatch = null;
                $body .= "<table class='table' width='100%' cellpadding='0' cellspacing='0'>";
                $body .= '<tr>';
                $body .= "<th class='sectiontableheader' style='text-align:left;'>" . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_DATE_TIME') . '</th>';
                $body .= "<th class='sectiontableheader' style='text-align:left;' colspan='5'>" . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_MATCH') . '</th>';
                $body .= "<th class='sectiontableheader' style='text-align:left;'>" . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_RESULT') . '</th>';
                $body .= "<th class='sectiontableheader' style='text-align:left;'>" . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_YOURS') . '</th>';
                $body .= "<th class='sectiontableheader' style='text-align:left;'>" . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_POINTS') . '</th>';
                $body .= '</tr>';
                $rowIndex = 0;

                foreach ($matches as $result) {
                    $lastMatch = $result;
                    $resultHome = isset($result->team1_result) ? $result->team1_result : '-';
                    $resultAway = isset($result->team2_result) ? $result->team2_result : '-';

                    if (isset($result->team1_result_decision)) {
                        $resultHome = $result->team1_result_decision;
                    }

                    if (isset($result->team2_result_decision)) {
                        $resultAway = $result->team2_result_decision;
                    }

                    if ($resultHome !== '-' || $resultAway !== '-') {
                        continue;
                    }

                    if (
                        ((int) $predictionProject->mode === 0
                            && (isset($result->tipp_home) || isset($result->tipp_away)))
                        || ((int) $predictionProject->mode === 1 && isset($result->tipp))
                    ) {
                        continue;
                    }

                    $reminderFound++;
                    $class = $rowIndex === 0 ? 'sectiontableentry1' : 'sectiontableentry2';
                    $body .= "<tr class='" . $class . "'>";
                    $body .= "<td class='td_c'>" . HTMLHelper::date($result->match_date, 'd.m.Y H:i', false) . ' - </td>';
                    $body .= "<td nowrap='nowrap' class='td_r'>" . htmlspecialchars((string) $result->home_name) . '</td>';
                    $body .= "<td nowrap='nowrap' class='td_c'>" . $this->teamLogo((string) $result->home_logo_big, (string) $result->home_name) . '</td>';
                    $body .= "<td nowrap='nowrap' class='td_c'><b>-</b></td>";
                    $body .= "<td nowrap='nowrap' class='td_c'>" . $this->teamLogo((string) $result->away_logo_big, (string) $result->away_name) . '</td>';
                    $body .= "<td nowrap='nowrap' class='td_l'>" . htmlspecialchars((string) $result->away_name) . '</td>';
                    $body .= "<td class='td_c'>" . $resultHome . '-' . $resultAway . '</td>';
                    $body .= "<td class='td_c'>";

                    if ((int) $predictionProject->mode === 0) {
                        $body .= (string) $result->tipp_home
                            . (string) ($configPrediction['seperator'] ?? ':')
                            . (string) $result->tipp_away;
                    } else {
                        $body .= (string) $result->tipp;
                    }

                    $points = $this->calculatePredictionPoints($settings, $result);
                    $totalPoints += $points;
                    $body .= "</td><td class='td_c'>" . $points . '</td></tr>';

                    if (!empty($configPrediction['show_tipp_tendence'])) {
                        $totalCount = $this->getTippCount($predictionId, $projectId, (int) $result->id, 3);
                        $homeCount = $this->getTippCount($predictionId, $projectId, (int) $result->id, 1);
                        $awayCount = $this->getTippCount($predictionId, $projectId, (int) $result->id, 2);
                        $drawCount = $this->getTippCount($predictionId, $projectId, (int) $result->id, 0);
                        $percentageH = $totalCount > 0 ? round($homeCount * 100 / $totalCount, 2) : 0;
                        $percentageD = $totalCount > 0 ? round($drawCount * 100 / $totalCount, 2) : 0;
                        $percentageA = $totalCount > 0 ? round($awayCount * 100 / $totalCount, 2) : 0;
                        $body .= "<tr class='tipp_tendence'><td class='td_c'>&nbsp;</td><td class='td_l' colspan='8'>";
                        $body .= Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_HOME_WIN', $percentageH, $homeCount) . '<br />';
                        $body .= Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_DRAW', $percentageD, $drawCount) . '<br />';
                        $body .= Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_AWAY_WIN', $percentageA, $awayCount);
                        $body .= '</td></tr>';
                    }

                    $rowIndex = 1 - $rowIndex;
                }

                if ($lastMatch) {
                    if ($projectCount > 0) {
                        $fromDate .= ' und ';
                    }

                    $fromDate .= HTMLHelper::date($lastMatch->round_date_first, 'd.m.Y', false)
                        . '-' . HTMLHelper::date($lastMatch->round_date_last, 'd.m.Y', false);
                }

                $body .= "<tr><td colspan='8'>&nbsp;</td><td class='td_c'>"
                    . Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_TOTAL_POINTS_COUNT', $totalPoints)
                    . '</td></tr></table>';
                $projectCount++;
            }

            $body .= '</html><br>';

            if ($reminderFound <= 0) {
                continue;
            }

            $message = str_replace(
                ['[PREDICTIONMEMBER]', '[PREDICTIONRESULTS]', '[FROMDATE]', '[PREDICTIONENTRIES]', '[PREDICTIONADMIN]'],
                [(string) $member->username, $body, $fromDate, '', (string) $config->get('sitename')],
                $reminderText
            );

            try {
                $mailer = $mailerFactory->createMailer();

                if (method_exists($mailer, 'isHTML')) {
                    $mailer->isHTML(true);
                }

                $mailer->setSender($senderEmail, $senderName);
                $mailer->addRecipient((string) $member->email);
                $mailer->setSubject(Text::sprintf(
                    'COM_SPORTSMANAGEMENT_EMAIL_PREDICTION_REMINDER_TIPS_RESULTS',
                    (string) $predictionGame->name
                ));
                $mailer->setBody($message);
                $mailer->send();
                $app->enqueueMessage(
                    Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_MAIL_SEND_OK', (string) $member->email),
                    'notice'
                );
                $sent++;
            } catch (\Throwable $e) {
                $app->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return $sent;
    }

    public function getPredictionGroups(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_groups'))
            ->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /** Update approval state and notify the affected prediction members. */
    public function publishpredmembers($cid = [], $publish = 1, $predictionGameID = 0): bool
    {
        $ids = $this->normaliseIds((array) $cid);

        if (!$ids) {
            return true;
        }

        $db = $this->getDatabase();
        $userId = (int) Factory::getApplication()->getIdentity()->id;
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__sportsmanagement_prediction_member'))
            ->set($db->quoteName('approved') . ' = ' . ((int) $publish === 1 ? 1 : 0))
            ->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')')
            ->where('(' . $db->quoteName('checked_out') . ' = 0 OR ' . $db->quoteName('checked_out') . ' = ' . $userId . ')');

        try {
            $db->setQuery($query)->execute();
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }

        $this->sendApprovalStateMails($ids, (int) $publish === 1, (int) $predictionGameID);

        return true;
    }

    public function deletePredictionMembers($cid = []): bool
    {
        $ids = $this->normaliseIds((array) $cid);

        if (!$ids) {
            return true;
        }

        return $this->deleteByIds('#__sportsmanagement_prediction_member', 'id', $ids);
    }

    public function deletePredictionResults($cid = [], $prediction_id = 0): bool
    {
        $memberIds = $this->normaliseIds((array) $cid);
        $predictionId = (int) $prediction_id;

        if (!$memberIds || $predictionId <= 0) {
            return true;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('user_id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_member'))
            ->where($db->quoteName('id') . ' IN (' . implode(',', $memberIds) . ')')
            ->where($db->quoteName('prediction_id') . ' = ' . $predictionId);
        $db->setQuery($query);
        $userIds = $this->normaliseIds($db->loadColumn() ?: []);

        if (!$userIds) {
            return true;
        }

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__sportsmanagement_prediction_result'))
            ->where($db->quoteName('user_id') . ' IN (' . implode(',', $userIds) . ')')
            ->where($db->quoteName('prediction_id') . ' = ' . $predictionId);

        try {
            $db->setQuery($query)->execute();

            return true;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

    private function sendApprovalStateMails(array $memberIds, bool $approved, int $predictionGameId): void
    {
        $config = Factory::getContainer()->get('config');
        $mailerFactory = Factory::getContainer()->get(MailerFactoryInterface::class);
        $senderEmail = (string) $config->get('mailfrom');
        $senderName = (string) $config->get('fromname');
        $bcc = array_values(array_unique(array_merge(
            $this->getSystemMailRecipients(),
            $this->getPredictionAdminEmails($predictionGameId)
        )));

        foreach ($memberIds as $memberId) {
            $memberEmails = $this->getPredictionMemberEmails($memberId);

            if (!$memberEmails) {
                continue;
            }

            try {
                $mailer = $mailerFactory->createMailer();
                $mailer->setSender($senderEmail, $senderName);

                foreach ($memberEmails as $memberEmail) {
                    $mailer->addRecipient($memberEmail);
                }

                foreach ($bcc as $bccEmail) {
                    $mailer->addBcc($bccEmail);
                }

                $mailer->setSubject(Text::_($approved
                    ? 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_APPROVED'
                    : 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_REJECTED'
                ));
                $mailer->setBody(Text::_($approved
                    ? 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_REQ_APPROVED'
                    : 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_APPROVEMENT_REJECTED'
                ));
                $mailer->send();
            } catch (\Throwable $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }
    }

    private function getPredictionMemberContact(int $memberId): ?object
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pm.user_id'),
                $db->quoteName('u.email'),
                $db->quoteName('u.username'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_member', 'pm'))
            ->join(
                'INNER',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('pm.user_id')
            )
            ->where($db->quoteName('pm.id') . ' = ' . $memberId)
            ->where($db->quoteName('u.block') . ' = 0');
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private function getPredictionMemberEmails(int $memberId): array
    {
        $member = $this->getPredictionMemberContact($memberId);

        return $member && !empty($member->email) ? [(string) $member->email] : [];
    }

    private function getSystemMailRecipients(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('email'))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('sendEmail') . ' = 1')
            ->where($db->quoteName('block') . ' = 0')
            ->where($db->quoteName('email') . ' <> ' . $db->quote(''));
        $db->setQuery($query);

        return array_values(array_filter(array_map('strval', $db->loadColumn() ?: [])));
    }

    private function getPredictionAdminEmails(int $predictionGameId): array
    {
        if ($predictionGameId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('u.email'))
            ->from($db->quoteName('#__sportsmanagement_prediction_admin', 'pa'))
            ->join(
                'INNER',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('pa.user_id')
            )
            ->where($db->quoteName('pa.prediction_id') . ' = ' . $predictionGameId)
            ->where($db->quoteName('u.block') . ' = 0')
            ->where($db->quoteName('u.email') . ' <> ' . $db->quote(''));
        $db->setQuery($query);

        return array_values(array_filter(array_map('strval', $db->loadColumn() ?: [])));
    }

    private function getFirstPredictionProject(int $predictionId): ?object
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_prediction_project'))
            ->where($db->quoteName('prediction_id') . ' = ' . $predictionId)
            ->order($db->quoteName('id') . ' ASC');
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private function getPredictionProjectSettings(int $predictionId, int $projectId): ?object
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_prediction_project'))
            ->where($db->quoteName('prediction_id') . ' = ' . $predictionId)
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private function getPredictionGame(int $predictionId): ?object
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_prediction_game'))
            ->where($db->quoteName('id') . ' = ' . $predictionId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private function getPredictionProjectIds(int $predictionId): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('project_id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_project'))
            ->where($db->quoteName('prediction_id') . ' = ' . $predictionId)
            ->order($db->quoteName('id') . ' ASC');
        $db->setQuery($query);

        return $this->normaliseIds($db->loadColumn() ?: []);
    }

    private function getPredictionTemplateConfig(int $predictionId, string $template): array
    {
        $db = $this->getDatabase();
        $loadParams = function (int $gameId) use ($db, $template): string {
            if ($gameId <= 0) {
                return '';
            }

            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__sportsmanagement_prediction_template'))
                ->where($db->quoteName('template') . ' = ' . $db->quote($template))
                ->where($db->quoteName('prediction_id') . ' = ' . $gameId);
            $db->setQuery($query, 0, 1);

            return (string) $db->loadResult();
        };

        $params = $loadParams($predictionId);

        if ($params === '') {
            $query = $db->getQuery(true)
                ->select($db->quoteName('master_template'))
                ->from($db->quoteName('#__sportsmanagement_prediction_game'))
                ->where($db->quoteName('id') . ' = ' . $predictionId);
            $db->setQuery($query, 0, 1);
            $masterTemplateId = (int) $db->loadResult();
            $params = $loadParams($masterTemplateId);
        }

        if ($params === '') {
            return [];
        }

        $registry = new Registry();
        $registry->loadString($params);
        $values = $registry->toArray();

        if ($template === 'predictionoverall' && !array_key_exists('sort_order_1', $values)) {
            $values['sort_order_1'] = 'points';
            $values['sort_order_2'] = 'correct_tipps';
            $values['sort_order_3'] = 'correct_diffs';
            $values['sort_order_4'] = 'correct_tend';
            $values['sort_order_5'] = 'count_tipps_p';
        }

        return $values;
    }

    private function getPredictionGamesMatches(int $predictionId, int $projectId, int $userId): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.match_date'),
                $db->quoteName('m.team1_result'),
                $db->quoteName('m.team2_result'),
                $db->quoteName('m.team1_result_decision'),
                $db->quoteName('m.team2_result_decision'),
                $db->quoteName('r.round_date_first'),
                $db->quoteName('r.round_date_last'),
                $db->quoteName('pr.tipp'),
                $db->quoteName('pr.tipp_home'),
                $db->quoteName('pr.tipp_away'),
                $db->quoteName('pr.joker'),
                $db->quoteName('t1.name', 'home_name'),
                $db->quoteName('t2.name', 'away_name'),
                $db->quoteName('c1.logo_big', 'home_logo_big'),
                $db->quoteName('c2.logo_big', 'away_logo_big'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_prediction_result', 'pr')
                . ' ON ' . $db->quoteName('pr.match_id') . ' = ' . $db->quoteName('m.id')
                . ' AND ' . $db->quoteName('pr.prediction_id') . ' = ' . $predictionId
                . ' AND ' . $db->quoteName('pr.user_id') . ' = ' . $userId
                . ' AND ' . $db->quoteName('pr.project_id') . ' = ' . $projectId)
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c1') . ' ON ' . $db->quoteName('c1.id') . ' = ' . $db->quoteName('t1.club_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c2') . ' ON ' . $db->quoteName('c2.id') . ' = ' . $db->quoteName('t2.club_id'))
            ->where($db->quoteName('r.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('m.match_date') . ' <> ' . $db->quote('0000-00-00 00:00:00'))
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)')
            ->order($db->quoteName('m.match_date') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    private function getTippCount(int $predictionId, int $projectId, int $matchId, int $type): int
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_prediction_result'))
            ->where($db->quoteName('prediction_id') . ' = ' . $predictionId)
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->where($db->quoteName('match_id') . ' = ' . $matchId);

        if ($type === 3) {
            $query->where($db->quoteName('tipp') . ' IS NOT NULL');
        } else {
            $query->where($db->quoteName('tipp') . ' = ' . $db->quote((string) $type));
        }

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    private function calculatePredictionPoints(object $settings, object $result): int
    {
        $resultHome = $result->team1_result ?? null;
        $resultAway = $result->team2_result ?? null;

        if ((int) $settings->mode === 1) {
            if ($resultHome === null || $resultAway === null || !isset($result->tipp)) {
                return 0;
            }

            if ($resultHome > $resultAway && (string) $result->tipp === '1') {
                return (int) $settings->points_tipp;
            }

            if ($resultHome < $resultAway && (string) $result->tipp === '2') {
                return (int) $settings->points_tipp;
            }

            if ($resultHome == $resultAway && (string) $result->tipp === '0') {
                return (int) $settings->points_tipp;
            }

            return 0;
        }

        $tipHome = $result->tipp_home ?? null;
        $tipAway = $result->tipp_away ?? null;

        if ($resultHome === null || $resultAway === null || $tipHome === null || $tipAway === null) {
            return 0;
        }

        $suffix = !empty($result->joker) ? '_joker' : '';

        if ($resultHome == $tipHome && $resultAway == $tipAway) {
            return (int) $settings->{'points_correct_result' . $suffix};
        }

        if ($resultHome == $resultAway && ($resultHome - $resultAway) == ($tipHome - $tipAway)) {
            return (int) $settings->{'points_correct_draw' . $suffix};
        }

        if (($resultHome - $resultAway) == ($tipHome - $tipAway)) {
            return (int) $settings->{'points_correct_diff' . $suffix};
        }

        if (
            (($resultHome - $resultAway) > 0 && ($tipHome - $tipAway) > 0)
            || (($resultHome - $resultAway) < 0 && ($tipHome - $tipAway) < 0)
        ) {
            return (int) $settings->{'points_correct_tendence' . $suffix};
        }

        return (int) $settings->{'points_tipp' . $suffix};
    }

    private function teamLogo(string $path, string $teamName): string
    {
        if ($path === '' || !is_file(JPATH_ROOT . '/' . ltrim($path, '/'))) {
            $path = 'images/com_sportsmanagement/database/placeholders/placeholder_150.png';
        }

        $title = Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_LOGO_OF', $teamName);

        return HTMLHelper::_('image', Uri::root() . ltrim($path, '/'), $title, ['title' => $title, 'width' => 30]);
    }

    private function deleteByIds(string $table, string $column, array $ids): bool
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName($table))
            ->where($db->quoteName($column) . ' IN (' . implode(',', $ids) . ')');

        try {
            $db->setQuery($query)->execute();

            return true;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0
        )));
    }
}
