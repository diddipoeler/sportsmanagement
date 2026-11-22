<?php
/**
 * Native Joomla 5/6 prediction membership model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\Database\ParameterType;

final class PredictionmembershipModel extends PredictionentryModel
{
    /**
     * Register the authenticated Joomla user for the selected prediction game.
     *
     * Reject guest users and unavailable games, check for an existing
     * membership using bound query parameters, and insert an initial member
     * record when needed. Registration confirmation is best-effort so an
     * SMTP failure cannot undo an already committed membership.
     *
     * @return int Existing or newly created membership ID, or zero if insertion fails.
     * @throws \RuntimeException If no user or prediction game is authorised.
     */
    public function registerCurrentUser(): int
    {
        $identity = $this->siteApplication()->getIdentity();
        $userId = (int) ($identity->id ?? 0);

        // Reject guests before reading the game or opening a database connection.
        if ($userId <= 0) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $game = $this->getPredictionGame();
        if (!$game || $this->predictionGameId <= 0) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_member'))
            ->where($db->quoteName('prediction_id') . ' = :membershipPredictionId')
            ->where($db->quoteName('user_id') . ' = :membershipUserId')
            ->bind(':membershipPredictionId', $predictionGameId, ParameterType::INTEGER)
            ->bind(':membershipUserId', $userId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $existing = (int) $db->loadResult();
        if ($existing > 0) {
            return $existing;
        }

        $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $member = new \stdClass();
        $member->prediction_id = $predictionGameId;
        $member->user_id = $userId;
        $member->registerDate = $now;
        $member->approved = !empty($game->auto_approve) ? 1 : 0;
        $member->show_profile = 1;
        $member->published = 1;
        $member->modified = $now;
        $member->modified_by = $userId;

        if (!$db->insertObject('#__sportsmanagement_prediction_member', $member)) {
            return 0;
        }

        $memberId = (int) $db->insertid();
        if ($memberId > 0) {
            // An email/SMTP or notification-query failure must not turn an
            // already persisted registration into a reported failed signup.
            try {
                $this->sendMembershipConfirmation($memberId, $userId);
            } catch (\Throwable) {
                // Membership is committed; notification is best-effort.
            }
        }

        return $memberId;
    }

    /**
     * Notify the new prediction member and eligible game administrators.
     *
     * Query the active unblocked user, collect at most 100 valid deduplicated
     * administrator email addresses, configure the Joomla mailer factory
     * and send the configured translated membership confirmation.
     * Notification errors are handled without affecting persisted membership.
     *
     * @param int $memberId Newly created prediction membership record ID.
     * @param int $userId Authenticated Joomla user ID to notify.
     * @return bool Whether the Joomla mailer reported successful delivery.
     */
    private function sendMembershipConfirmation(int $memberId, int $userId): bool
    {
        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([$db->quoteName('email'), $db->quoteName('name')])
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('id') . ' = :confirmationUserId')
            ->where($db->quoteName('block') . ' = 0')
            ->bind(':confirmationUserId', $userId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $user = $db->loadObject();
        if (!$user || empty($user->email)) {
            return false;
        }

        $adminQuery = $db->createQuery()
            ->select($db->quoteName('u.email'))
            ->from($db->quoteName('#__users', 'u'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_prediction_admin', 'pa')
                . ' ON ' . $db->quoteName('pa.user_id') . ' = ' . $db->quoteName('u.id'))
            ->where($db->quoteName('pa.prediction_id') . ' = :adminPredictionId')
            ->where($db->quoteName('u.block') . ' = 0')
            ->where($db->quoteName('u.sendEmail') . ' = 1')
            ->order($db->quoteName('u.email') . ' ASC')
            ->bind(':adminPredictionId', $predictionGameId, ParameterType::INTEGER);
        $db->setQuery($adminQuery);
        // Administrators can number in the thousands. Bound mail recipients
        // and reject malformed addresses returned by old user databases.
        $bcc = [];
        foreach ($db->loadColumn() ?: [] as $candidate) {
            if (!is_string($candidate)
                || strlen($candidate) > 254
                || filter_var($candidate, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }

            $bcc[strtolower($candidate)] = $candidate;

            if (count($bcc) >= 100) {
                break;
            }
        }

        $bcc = array_values($bcc);

        try {
            $app = $this->siteApplication();
            $mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
            $config = $app->getConfig();
            $mailFrom = (string) $config->get('mailfrom', '');
            $fromName = (string) $config->get('fromname', '');
            $mailer->setSender([$mailFrom, $fromName]);
            $mailer->addRecipient((string) $user->email);
            if ($mailFrom !== '' && strcasecmp($mailFrom, (string) $user->email) !== 0) {
                $bcc[] = $mailFrom;
            }
            $bcc = array_values(array_unique(array_filter($bcc)));
            if ($bcc) {
                $mailer->addBcc($bcc);
            }
            $mailer->setSubject(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_MEMBERSHIP_SUBJECT'));
            $mailer->setBody(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_MEMBERSHIP'));
            return $mailer->send() === true;
        } catch (\Throwable) {
            return false;
        }
    }
}
