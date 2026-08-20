<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\Database\DatabaseInterface;

/** Sends notifications only for newly created referee assignments. */
final class MatchRefereeNotificationService
{
    public function __construct(
        private DatabaseInterface $db,
        private MailerFactoryInterface $mailerFactory,
        private CMSApplicationInterface $app
    ) {
    }

    /** @param array<int,array{project_referee_id:int,project_position_id:int}> $assignments */
    public function notifyNewAssignments(int $matchId, array $assignments): void
    {
        $template = (string) ComponentHelper::getParams('com_sportsmanagement')->get('ishd_referee_insert_match_mail', '');
        if ($matchId <= 0 || !$assignments || trim($template) === '') {
            return;
        }

        foreach ($assignments as $assignment) {
            $projectRefereeId = (int) ($assignment['project_referee_id'] ?? 0);
            if ($projectRefereeId <= 0) {
                continue;
            }

            $context = $this->context($matchId, $projectRefereeId);
            if (!$context || trim((string) $context->email) === '') {
                continue;
            }

            $timestamp = (int) ($context->match_timestamp ?? 0);
            if ($timestamp <= 0) {
                $timestamp = strtotime((string) ($context->match_date ?? '')) ?: 0;
            }
            $when = $timestamp > 0 ? date('d.m.Y - H:i', $timestamp) : (string) ($context->match_date ?? '');

            $body = sprintf(
                $template,
                (string) $context->firstname,
                (string) $context->lastname,
                'Schiedsrichterverein',
                'Schiedsrichterstufe',
                $when,
                (string) ($context->playground_name ?? ''),
                'Ligakurzname',
                (string) ($context->team1 ?? ''),
                (string) ($context->team2 ?? '')
            );

            try {
                $mailer = $this->mailerFactory->createMailer();
                $mailFrom = (string) $this->app->get('mailfrom', '');
                $fromName = (string) $this->app->get('fromname', '');
                if ($mailFrom !== '') {
                    $mailer->setSender([$mailFrom, $fromName]);
                }
                $mailer->addRecipient((string) $context->email);
                $mailer->setSubject('Neueinteilung Schiedsrichtereinsatz am : ' . $when);
                $mailer->isHTML(true);
                $mailer->setBody($body);
                $mailer->send();
            } catch (\Throwable $e) {
                $this->app->enqueueMessage($e->getMessage(), 'warning');
            }
        }
    }

    private function context(int $matchId, int $projectRefereeId): ?object
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('person.firstname'),
                $this->db->quoteName('person.lastname'),
                $this->db->quoteName('person.email'),
                $this->db->quoteName('m.match_date'),
                $this->db->quoteName('m.match_timestamp'),
                $this->db->quoteName('pg.name', 'playground_name'),
                $this->db->quoteName('t1.name', 'team1'),
                $this->db->quoteName('t2.name', 'team2'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_playground', 'pg') . ' ON ' . $this->db->quoteName('pg.id') . ' = ' . $this->db->quoteName('m.playground_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $this->db->quoteName('pt1.id') . ' = ' . $this->db->quoteName('m.projectteam1_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $this->db->quoteName('pt2.id') . ' = ' . $this->db->quoteName('m.projectteam2_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $this->db->quoteName('st1.id') . ' = ' . $this->db->quoteName('pt1.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $this->db->quoteName('st2.id') . ' = ' . $this->db->quoteName('pt2.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $this->db->quoteName('t1.id') . ' = ' . $this->db->quoteName('st1.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $this->db->quoteName('t2.id') . ' = ' . $this->db->quoteName('st2.team_id'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_match_referee', 'mr') . ' ON ' . $this->db->quoteName('mr.match_id') . ' = ' . $this->db->quoteName('m.id') . ' AND ' . $this->db->quoteName('mr.project_referee_id') . ' = ' . $projectRefereeId)
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_project_referee', 'pref') . ' ON ' . $this->db->quoteName('pref.id') . ' = ' . $this->db->quoteName('mr.project_referee_id'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_season_person_id', 'spi') . ' ON ' . $this->db->quoteName('spi.id') . ' = ' . $this->db->quoteName('pref.person_id'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_person', 'person') . ' ON ' . $this->db->quoteName('person.id') . ' = ' . $this->db->quoteName('spi.person_id'))
            ->where($this->db->quoteName('m.id') . ' = ' . $matchId);
        $this->db->setQuery($query, 0, 1);
        return $this->db->loadObject() ?: null;
    }
}
