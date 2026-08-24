<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

$googleAutoload = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/libraries/google-php/vendor/autoload.php';

if (is_file($googleAutoload)) {
    require_once $googleAutoload;
}

/** Native Joomla 5/6 Google Calendar OAuth/import model. */
final class JsmgcalendarimportModel extends SportsManagementListModel
{
    private const OAUTH_STATE_KEY = 'com_sportsmanagement.jsmgcalendar.oauth_state';
    private const OAUTH_CONTEXT_KEY = 'com_sportsmanagement.jsmgcalendar.oauth_context';

    public function import(): bool
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $session = $app->getSession();
        $code = (string) $input->get('code', '', 'raw');
        $oauthError = trim((string) $input->getString('error'));
        $isCallback = $code !== '' || $oauthError !== '' || trim((string) $input->getString('state')) !== '';

        if (!$isCallback) {
            $clientId = trim((string) $input->post->getString(
                'google_api_clientid',
                (string) $params->get('google_api_clientid', '')
            ));
            $clientSecret = trim((string) $input->post->getString(
                'google_api_clientsecret',
                (string) $params->get('google_api_clientsecret', '')
            ));
            $mailAccount = trim((string) $input->post->getString(
                'user',
                (string) $params->get('google_mail_account', '')
            ));
            $session->set(self::OAUTH_CONTEXT_KEY, [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'mail_account' => $mailAccount,
            ]);
        } else {
            $context = $session->get(self::OAUTH_CONTEXT_KEY, []);
            $context = is_array($context) ? $context : [];
            $clientId = trim((string) ($context['client_id'] ?? $params->get('google_api_clientid', '')));
            $clientSecret = trim((string) ($context['client_secret'] ?? $params->get('google_api_clientsecret', '')));
            $mailAccount = trim((string) ($context['mail_account'] ?? $params->get('google_mail_account', '')));
        }

        if (!class_exists('Google_Client') || !class_exists('Google_Service_Calendar')) {
            $app->enqueueMessage('Google API client is not available.', 'error');
            $session->set(self::OAUTH_CONTEXT_KEY, null);

            return false;
        }

        if ($clientId === '' || $clientSecret === '') {
            $app->enqueueMessage('Google API client credentials are not configured.', 'error');
            $session->set(self::OAUTH_CONTEXT_KEY, null);

            return false;
        }

        $client = new \Google_Client(['ioFileCache_directory' => (string) $app->get('tmp_path')]);
        $client->setApplicationName('JSMCalendar');
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setScopes(['https://www.googleapis.com/auth/calendar']);
        $client->setAccessType('offline');

        if (method_exists($client, 'setPrompt')) {
            $client->setPrompt('consent');
        } elseif (method_exists($client, 'setApprovalPrompt')) {
            $client->setApprovalPrompt('force');
        }

        $uri = Uri::getInstance();

        if (filter_var($uri->getHost(), FILTER_VALIDATE_IP)) {
            $uri->setHost('localhost');
        }

        $client->setRedirectUri(
            $uri->toString(['scheme', 'host', 'port', 'path'])
            . '?option=com_sportsmanagement&task=jsmgcalendarimport.import'
        );

        if (!$isCallback) {
            if (!method_exists($client, 'setState')) {
                $app->enqueueMessage('Installed Google API client does not support OAuth state validation.', 'error');
                $session->set(self::OAUTH_CONTEXT_KEY, null);

                return false;
            }

            $state = bin2hex(random_bytes(32));
            $session->set(self::OAUTH_STATE_KEY, $state);
            $client->setState($state);
            $app->redirect($client->createAuthUrl());
            $app->close();

            return true;
        }

        $expectedState = (string) $session->get(self::OAUTH_STATE_KEY, '');
        $receivedState = (string) $input->getString('state');
        $session->set(self::OAUTH_STATE_KEY, null);

        if ($expectedState === '' || $receivedState === '' || !hash_equals($expectedState, $receivedState)) {
            $app->enqueueMessage('Google OAuth state validation failed.', 'error');
            $session->set(self::OAUTH_CONTEXT_KEY, null);

            return false;
        }

        if ($oauthError !== '') {
            $app->enqueueMessage('Google OAuth failed: ' . $oauthError, 'error');
            $session->set(self::OAUTH_CONTEXT_KEY, null);

            return false;
        }

        try {
            $token = $client->authenticate($code);
            $client->setAccessToken($token);
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            $session->set(self::OAUTH_CONTEXT_KEY, null);

            return false;
        }

        $tokenData = is_string($token) ? json_decode($token, true) : $token;
        $tokenData = is_array($tokenData) ? $tokenData : [];
        $refreshToken = $tokenData['refresh_token'] ?? null;
        $calendarService = new \Google_Service_Calendar($client);
        $db = $this->getDatabase();
        $userId = (int) $app->getIdentity()->id;
        $session->set(self::OAUTH_CONTEXT_KEY, null);
        $transactionStarted = false;

        try {
            $db->transactionStart();
            $transactionStarted = true;
            $pageToken = null;

            do {
                $options = $pageToken ? ['pageToken' => $pageToken] : [];
                $calendarList = $calendarService->calendarList->listCalendarList($options);

                foreach ($calendarList->getItems() as $entry) {
                    $calendarId = (string) $entry->getID();
                    $title = (string) $entry->getSummary();

                    if ($calendarId === '') {
                        continue;
                    }

                    $calendarParams = new Registry();
                    $calendarParams->set('refreshToken', $refreshToken);
                    $calendarParams->set('client-id', $clientId);
                    $calendarParams->set('client-secret', $clientSecret);
                    $calendarParams->set('calendarId', $calendarId);
                    $calendarParams->set('action-create', true);
                    $calendarParams->set('action-edit', true);
                    $calendarParams->set('action-delete', true);

                    $lookup = $db->getQuery(true)
                        ->select($db->quoteName('id'))
                        ->from($db->quoteName('#__sportsmanagement_gcalendar'))
                        ->where($db->quoteName('calendar_id') . ' = :calendarId')
                        ->bind(':calendarId', $calendarId, ParameterType::STRING);
                    $db->setQuery($lookup, 0, 1);
                    $existingId = (int) $db->loadResult();
                    $now = Factory::getDate()->toSql();

                    if ($existingId <= 0) {
                        $row = (object) [
                            'calendar_id' => $calendarId,
                            'name' => $title,
                            'color' => method_exists($entry, 'getBackgroundColor')
                                ? (string) $entry->getBackgroundColor()
                                : (string) ($entry->backgroundColor ?? ''),
                            'username' => $mailAccount,
                            'params' => $calendarParams->toString(),
                            'title' => $title,
                            'alias' => OutputFilter::stringURLSafe($title),
                            'created' => $now,
                            'created_by' => $userId,
                            'modified' => $now,
                            'modified_by' => $userId,
                        ];
                        $db->insertObject('#__sportsmanagement_gcalendar', $row);
                    } else {
                        $row = (object) [
                            'id' => $existingId,
                            'name' => $title,
                            'params' => $calendarParams->toString(),
                            'title' => $title,
                            'alias' => OutputFilter::stringURLSafe($title),
                            'modified' => $now,
                            'modified_by' => $userId,
                        ];
                        $db->updateObject('#__sportsmanagement_gcalendar', $row, 'id');
                    }
                }

                $pageToken = $calendarList->getNextPageToken();
            } while ($pageToken);

            $db->transactionCommit();

            return true;
        } catch (\Throwable $e) {
            if ($transactionStarted) {
                try {
                    $db->transactionRollback();
                } catch (\Throwable) {
                    // Preserve the original import error.
                }
            }

            $this->setError($e->getMessage());
            $app->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();

        return $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_gcalendar'));
    }
}
