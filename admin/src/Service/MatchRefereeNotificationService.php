<?php
/**
 * Joomla 5/6 referee notification service for newly assigned match officials.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/** Sends notifications only for newly created referee assignments. */
final class MatchRefereeNotificationService
{
    /**
     * Create the match-referee notification service with Joomla dependencies.
     * Reuses the database, mailer factory and application for all mail operations; no messages are sent until notifyNewAssignments() is called.
     *
     * @param DatabaseInterface $db Joomla database connection for referee assignments.
     * @param MailerFactoryInterface $mailerFactory Joomla mailer factory.
     * @param CMSApplicationInterface $app Administrator application providing mail configuration.
     * @return void
     */
    public function __construct(
        private DatabaseInterface $db,
        private MailerFactoryInterface $mailerFactory,
        private CMSApplicationInterface $app
    ) {
    }

    /**
     * Email newly assigned referees without disrupting the match save operation.
     * Rejects malformed referee identifiers, looks up each assigned person with bound database parameters, validates the recipient address and sends at most one notification per referee. Lookup, formatting and mail failures are logged without exposing private diagnostics to the browser.
     *
     * @param int $matchId Numeric identifier of the saved match.
     * @param array<int,mixed> $assignments New referee assignments; invalid entries are ignored.
     * @return void
     */
    public function notifyNewAssignments(int $matchId, array $assignments): void
    {
        $template = (string) ComponentHelper::getParams('com_sportsmanagement')->get('ishd_referee_insert_match_mail', '');
        if ($matchId <= 0 || !$assignments || trim($template) === '') {
            return;
        }

        $notifiedReferees = [];

        foreach ($assignments as $assignment) {
            if (!is_array($assignment)) {
                continue;
            }

            $projectRefereeId = self::positiveRefereeId($assignment['project_referee_id'] ?? null);
            if ($projectRefereeId <= 0 || isset($notifiedReferees[$projectRefereeId])) {
                continue;
            }

            try {
                $context = $this->context($matchId, $projectRefereeId);
            } catch (\Throwable $error) {
                Log::add(__METHOD__ . ': referee lookup failed: ' . $error->getMessage(), Log::WARNING, 'jsmerror');
                continue;
            }
            $email = $context !== null ? ($context->email ?? null) : null;
            if (!is_string($email) || filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }

            $timestamp = (int) ($context->match_timestamp ?? 0);
            if ($timestamp <= 0) {
                $timestamp = strtotime((string) ($context->match_date ?? '')) ?: 0;
            }
            $when = $timestamp > 0 ? date('d.m.Y - H:i', $timestamp) : (string) ($context->match_date ?? '');

            // Invalid legacy sprintf templates must not interrupt match persistence.
            try {
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
                $mailer = $this->mailerFactory->createMailer();
                $mailFrom = (string) $this->app->get('mailfrom', '');
                $fromName = (string) $this->app->get('fromname', '');
                if ($mailFrom !== '') {
                    $mailer->setSender([$mailFrom, $fromName]);
                }
                $mailer->addRecipient(trim($email));
                $mailer->setSubject('Neueinteilung Schiedsrichtereinsatz am : ' . $when);
                $mailer->isHTML(true);
                $mailer->setBody($body);
                $mailer->send();
                $notifiedReferees[$projectRefereeId] = true;
            } catch (\Throwable $e) {
                // SMTP and template errors belong in the administrator log,
                // never in a user-facing message with possible credentials.
                Log::add(__METHOD__ . ': notification failed: ' . $e->getMessage(), Log::WARNING, 'jsmerror');
                $this->app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'warning');
            }
        }
    }

    /**
     * Parse an untrusted positive referee identifier without lossy type coercion.
     * Accepts positive integers and zero-padded decimal strings, and rejects booleans, arrays, negative values, partial numbers and values larger than PHP_INT_MAX.
     *
     * @param mixed $value Raw project-referee ID from the form.
     * @return int Valid positive identifier or zero.
     */
    private static function positiveRefereeId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);
        if ($raw === '' || preg_match('/^[0-9]+$/D', $raw) !== 1) {
            return 0;
        }

        $digits = ltrim($raw, '0');
        if ($digits === '') {
            return 0;
        }

        $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Read contact and fixture details for a specific match referee.
     * Joins the match, project referee, season person and team records while binding both identifiers; the resulting object supplies the email-template placeholders.
     *
     * @param int $matchId Identifier of the match.
     * @param int $projectRefereeId Project-referee assignment identifier.
     * @return object|null Assignment and match details, or null if unavailable.
     */
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
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_match_referee', 'mr') . ' ON ' . $this->db->quoteName('mr.match_id') . ' = ' . $this->db->quoteName('m.id') . ' AND ' . $this->db->quoteName('mr.project_referee_id') . ' = :projectRefereeId')
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_project_referee', 'pref') . ' ON ' . $this->db->quoteName('pref.id') . ' = ' . $this->db->quoteName('mr.project_referee_id'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_season_person_id', 'spi') . ' ON ' . $this->db->quoteName('spi.id') . ' = ' . $this->db->quoteName('pref.person_id'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_person', 'person') . ' ON ' . $this->db->quoteName('person.id') . ' = ' . $this->db->quoteName('spi.person_id'))
            ->where($this->db->quoteName('m.id') . ' = :matchId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER)
            ->bind(':projectRefereeId', $projectRefereeId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);
        return $this->db->loadObject() ?: null;
    }
}
