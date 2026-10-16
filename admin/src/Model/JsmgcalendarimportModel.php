<?php
/**
 * Joomla 5/6 administrator Google Calendar OAuth/import model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Log\Log;
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
    private const MAX_CALENDAR_PAGES = 10;
    private const MAX_OAUTH_CODE_LENGTH = 4096;

    public function import(): bool
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $session = $app->getSession();
        $code = $input->get('code', '', 'raw');

        // A callback parameter must be a single bounded string, never an
        // array or a control-character-containing HTTP request value.
        if (!is_string($code) || strlen($code) > self::MAX_OAUTH_CODE_LENGTH
            || preg_match('/[\\x00-\\x1F\\x7F]/', $code)) {
            $app->enqueueMessage('Invalid Google OAuth response.', 'error');

            return false;
        }

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
            // OAuth transport exceptions may include tokens and credentials.
            Log::add('Google Calendar OAuth authentication failed (' . get_class($e) . ').', Log::WARNING, 'jsmerror');
            $app->enqueueMessage('Google OAuth authentication failed.', 'error');
            $session->set(self::OAUTH_CONTEXT_KEY, null);

            return false;
        }

        $tokenData = is_string($token) ? json_decode($token, true) : $token;
        $tokenData = is_array($tokenData) ? $tokenData : [];
        $rawRefreshToken = $tokenData['refresh_token'] ?? null;
        $refreshToken = is_string($rawRefreshToken) ? trim($rawRefreshToken) : '';
        $calendarService = new \Google_Service_Calendar($client);
        $db = $this->getDatabase();
        $userId = (int) $app->getIdentity()->id;
        $session->set(self::OAUTH_CONTEXT_KEY, null);
        $transactionStarted = false;
        $processed = 0;
        $skippedWithoutToken = 0;

        try {
            $db->transactionStart();
            $transactionStarted = true;
            $pageToken = null;
            $seenTokens = [];
            $seenCalendarIds = [];
            $pageCount = 0;

            do {
                if ($pageToken !== null) {
                    if (isset($seenTokens[$pageToken])) {
                        break;
                    }

                    $seenTokens[$pageToken] = true;
                }

                ++$pageCount;
                $options = $pageToken !== null ? ['pageToken' => $pageToken] : [];
                $calendarList = $calendarService->calendarList->listCalendarList($options);

                foreach ($calendarList->getItems() as $entry) {
                    $calendarId = (string) $entry->getID();
                    $title = (string) $entry->getSummary();

                    if ($calendarId === '' || isset($seenCalendarIds[$calendarId])) {
                        continue;
                    }

                    // Malformed API pagination can return the same calendar
                    // more than once. Never insert or update it twice.
                    $seenCalendarIds[$calendarId] = true;

                    $lookup = $db->createQuery()
                        ->select([
                            $db->quoteName('id'),
                            $db->quoteName('params'),
                        ])
                        ->from($db->quoteName('#__sportsmanagement_gcalendar'))
                        ->where($db->quoteName('calendar_id') . ' = :calendarId')
                        ->bind(':calendarId', $calendarId, ParameterType::STRING);
                    $db->setQuery($lookup, 0, 1);
                    $existing = $db->loadObject() ?: null;
                    $existingId = (int) ($existing->id ?? 0);

                    // Google may omit refresh_token on repeated consent.
                    // Preserve the stored token when updating an existing
                    // calendar instead of overwriting it with null.
                    $effectiveRefreshToken = $refreshToken;

                    if ($effectiveRefreshToken === '' && is_string($existing->params ?? null)) {
                        try {
                            $storedParams = new Registry();
                            $storedParams->loadString($existing->params);
                            $storedToken = $storedParams->get('refreshToken', '');
                            $effectiveRefreshToken = is_string($storedToken) ? trim($storedToken) : '';
                        } catch (\Throwable) {
                            $effectiveRefreshToken = '';
                        }
                    }

                    // A new calendar without OAuth refresh credentials cannot
                    // be read later; skip rather than storing a broken record.
                    if ($effectiveRefreshToken === '') {
                        ++$skippedWithoutToken;
                        continue;
                    }

                    $calendarParams = new Registry();
                    $calendarParams->set('refreshToken', $effectiveRefreshToken);
                    $calendarParams->set('client-id', $clientId);
                    $calendarParams->set('client-secret', $clientSecret);
                    $calendarParams->set('calendarId', $calendarId);
                    $calendarParams->set('action-create', true);
                    $calendarParams->set('action-edit', true);
                    $calendarParams->set('action-delete', true);
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

                    ++$processed;
                }

                $nextToken = $calendarList->getNextPageToken();
                $pageToken = is_string($nextToken) && $nextToken !== '' ? $nextToken : null;
            } while ($pageToken !== null && $pageCount < self::MAX_CALENDAR_PAGES);

            $db->transactionCommit();

            if ($skippedWithoutToken > 0) {
                $app->enqueueMessage('Some Google calendars were skipped because no refresh token was available.', 'warning');
            }

            return $processed > 0;
        } catch (\Throwable $e) {
            if ($transactionStarted) {
                try {
                    $db->transactionRollback();
                } catch (\Throwable) {
                    // Preserve the original import error.
                }
            }

            Log::add('Google Calendar import failed (' . get_class($e) . ').', Log::WARNING, 'jsmerror');
            $this->setError('Google Calendar import failed.');
            $app->enqueueMessage('Google Calendar import failed.', 'error');

            return false;
        }
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();

        return $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_gcalendar'));
    }
}
