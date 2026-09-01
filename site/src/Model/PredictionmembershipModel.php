<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailerFactoryInterface;

final class PredictionmembershipModel extends PredictionentryModel
{
    public function registerCurrentUser(): int
    {
        $identity = $this->siteApplication()->getIdentity();
        $userId = (int) $identity->id;
        $game = $this->getPredictionGame();
        if ($userId <= 0 || !$game || $this->predictionGameId <= 0) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_member'))
            ->where($db->quoteName('prediction_id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('user_id') . ' = ' . $userId);
        $db->setQuery($query, 0, 1);
        $existing = (int) $db->loadResult();
        if ($existing > 0) {
            return $existing;
        }

        $now = Factory::getDate()->toSql();
        $member = new \stdClass();
        $member->prediction_id = $this->predictionGameId;
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
            $this->sendMembershipConfirmation($memberId, $userId);
        }
        return $memberId;
    }

    private function sendMembershipConfirmation(int $memberId, int $userId): bool
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('email'), $db->quoteName('name')])
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('id') . ' = ' . $userId)
            ->where($db->quoteName('block') . ' = 0');
        $db->setQuery($query, 0, 1);
        $user = $db->loadObject();
        if (!$user || empty($user->email)) {
            return false;
        }

        $adminQuery = $db->getQuery(true)
            ->select($db->quoteName('u.email'))
            ->from($db->quoteName('#__users', 'u'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_prediction_admin', 'pa')
                . ' ON ' . $db->quoteName('pa.user_id') . ' = ' . $db->quoteName('u.id'))
            ->where($db->quoteName('pa.prediction_id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('u.block') . ' = 0')
            ->where($db->quoteName('u.sendEmail') . ' = 1')
            ->order($db->quoteName('u.email') . ' ASC');
        $db->setQuery($adminQuery);
        $bcc = array_values(array_filter(array_map('strval', $db->loadColumn() ?: [])));

        try {
            $app = $this->siteApplication();
            $mailer = \Joomla\CMS\Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
            $mailFrom = (string) $app->get('mailfrom', '');
            $fromName = (string) $app->get('fromname', '');
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
