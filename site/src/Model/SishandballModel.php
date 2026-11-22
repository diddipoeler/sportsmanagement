<?php
/**
 * Joomla 5/6 model for the legacy SIS handball XML integration.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use DOMDocument;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Http\HttpFactory;
use Throwable;

/**
 * Joomla 5/6 model for the legacy SIS handball XML integration.
 */
final class SishandballModel extends BaseDatabaseModel
{
    private const CACHE_TTL = 1800;

    /**
     * Resolve the Joomla site application used for SIS integration feedback.
     *
     * @return CMSApplicationInterface Active frontend application.
     */
    private function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Build the historical SIS handball XML export endpoint.
     *
     * Encode user, password, competition type and league number with
     * RFC 3986 query encoding. Return an empty URL when no base server
     * address was supplied.
     *
     * @param mixed $clubNumber SIS club login identifier.
     * @param mixed $clubPassword SIS login secret.
     * @param mixed $leagueNumber Selected handball league.
     * @param mixed $sisType SIS export type.
     * @param mixed $xmlBaseUrl SIS server URL from configuration.
     * @return string Fully encoded SIS XML endpoint or empty string.
     */
    public function getLink($clubNumber, $clubPassword, $leagueNumber, $sisType, $xmlBaseUrl): string
    {
        $baseUrl = rtrim((string) $xmlBaseUrl, '/');
        if ($baseUrl === '') {
            return '';
        }

        return $baseUrl . '/xmlexport/xml_dyn.aspx?'
            . http_build_query(
                [
                    'user' => (string) $clubNumber,
                    'pass' => (string) $clubPassword,
                    'art' => (string) $sisType,
                    'auf' => (string) $leagueNumber,
                ],
                '',
                '&',
                PHP_QUERY_RFC3986
            );
    }

    /**
     * Read the SIS standings document using a league-specific cache key.
     *
     * @param mixed $linkResults SIS XML endpoint.
     * @param mixed $leagueNumber Competition or league identifier.
     * @param mixed $sisType Export content type.
     * @return \SimpleXMLElement|false Cached or downloaded XML standings.
     */
    public function getTabelle($linkResults, $leagueNumber, $sisType)
    {
        return $this->loadXml(
            (string) $linkResults,
            'tab_sis_art_' . $this->safeToken($sisType) . '_ln_' . $this->safeToken($leagueNumber) . '.xml'
        );
    }

    /**
     * Read the SIS statistics document using its safe cache filename.
     *
     * @param mixed $linkResults SIS XML endpoint.
     * @param mixed $leagueNumber Competition or league identifier.
     * @return \SimpleXMLElement|false Cached or downloaded statistics XML.
     */
    public function getStatistik($linkResults, $leagueNumber)
    {
        return $this->loadXml(
            (string) $linkResults,
            'stat_' . $this->safeToken($leagueNumber) . '.xml'
        );
    }

    /**
     * Read and adapt SIS fixtures for the legacy frontend templates.
     *
     * Load the cached XML document, then derive display-friendly German
     * date and time fields from the start and end timestamps for each match.
     *
     * @param mixed $linkResults SIS XML endpoint.
     * @param mixed $leagueNumber Competition or league identifier.
     * @param mixed $sisType SIS export content type.
     * @return \SimpleXMLElement|false Fixtures XML with display date fields.
     */
    public function getSpielplan($linkResults, $leagueNumber, $sisType)
    {
        $result = $this->loadXml(
            (string) $linkResults,
            'sp_sis_art_' . $this->safeToken($sisType) . '_ln_' . $this->safeToken($leagueNumber) . '.xml'
        );

        if (!$result || !isset($result->Spiel)) {
            return $result;
        }

        foreach ($result->Spiel as $match) {
            $date = substr((string) ($match->SpielVon ?? ''), 0, 10);
            $timestamp = strtotime($date);
            $match->Datum = $timestamp !== false ? date('d.m.Y', $timestamp) : $date;
            $match->vonUhrzeit = substr((string) ($match->SpielVon ?? ''), 11, 8);
            $match->bisUhrzeit = substr((string) ($match->SpielBis ?? ''), 11, 8);
        }

        return $result;
    }

    /**
     * Load SIS XML from the bounded local cache or refresh it from HTTP.
     *
     * Initialise the component data directory, refresh files older than
     * the configured cache lifetime and validate downloaded XML before
     * writing it. Reparse cached documents with external network access
     * disabled, returning false when no usable cache file exists.
     *
     * @param string $url Remote SIS XML address.
     * @param string $fileName Validated cache basename generated by the model.
     * @return \SimpleXMLElement|false XML document or false when unavailable.
     */
    private function loadXml(string $url, string $fileName)
    {
        $cacheDirectory = JPATH_SITE . '/components/com_sportsmanagement/data';
        $cacheFile = $cacheDirectory . '/' . $fileName;

        if (!is_dir($cacheDirectory) && !Folder::create($cacheDirectory)) {
            $this->reportError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_FOLDER_NOT_FOUND'));
            return false;
        }

        $needsRefresh = !is_file($cacheFile)
            || (time() - (int) @filemtime($cacheFile)) > self::CACHE_TTL;

        if ($needsRefresh && $url !== '') {
            $content = $this->downloadXml($url);

            if ($content !== null) {
                $normalisedXml = $this->validateXml($content);
                if ($normalisedXml !== null) {
                    try {
                        File::write($cacheFile, $normalisedXml);
                    } catch (Throwable $e) {
                        $this->reportError($e->getMessage());
                    }
                }
            }
        }

        if (!is_file($cacheFile)) {
            return false;
        }

        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_file($cacheFile, \SimpleXMLElement::class, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return $xml ?: false;
    }

    /**
     * Fetch one SIS XML response using Joomla's HTTP driver.
     *
     * Require an HTTP 2xx status and a nonempty body, handling driver
     * errors without displaying sensitive URL credentials from exception
     * messages in the Joomla frontend.
     *
     * @param string $url SIS XML endpoint, potentially containing credentials.
     * @return string|null XML response body or null on a failed request.
     */
    private function downloadXml(string $url): ?string
    {
        try {
            $http = (new HttpFactory())->getAvailableDriver();
            $response = $http->get($url, [], 30);
            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode >= 300) {
                $this->reportError('SIS HTTP status ' . $statusCode);
                return null;
            }

            $body = (string) $response->getBody();
            if ($body === '') {
                $this->reportError('SIS returned an empty response.');
                return null;
            }

            return $body;
        } catch (Throwable $e) {
            // The request URL carries the SIS password. HTTP exceptions may
            // include that URL; never echo their details to site visitors.
            $this->reportError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'));
            return null;
        }
    }

    /**
     * Parse and normalise SIS XML before storing it in the cache.
     *
     * Use DOMDocument with network access disabled and restore the
     * previous libxml error state after every parsing attempt.
     *
     * @param string $content Untrusted remote XML document.
     * @return string|null Serialised valid XML or null on a parsing error.
     */
    private function validateXml(string $content): ?string
    {
        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument();

        try {
            if (!$document->loadXML($content, LIBXML_NONET)) {
                $this->reportError('SIS returned invalid XML.');
                return null;
            }

            return $document->saveXML() ?: null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    /**
     * Normalise SIS league/type values for safe local cache filenames.
     *
     * Replace characters outside the restricted alphanumeric filename
     * alphabet with underscores and provide a fallback for empty tokens.
     *
     * @param mixed $value Untrusted cache token.
     * @return string Safe filename fragment.
     */
    private function safeToken($value): string
    {
        $token = preg_replace('/[^A-Za-z0-9_.-]/', '_', (string) $value);
        return $token !== null && $token !== '' ? $token : '0';
    }

    /**
     * Show a safe SIS integration error in Joomla's message queue.
     *
     * Use a translated fallback when the caller supplies an empty message;
     * network exceptions containing credential-bearing URLs must be
     * sanitised by the caller before reaching this method.
     *
     * @param string $message Non-sensitive user-facing error message.
     * @return void
     */
    private function reportError(string $message): void
    {
        $this->siteApplication()->enqueueMessage(
            $message !== '' ? $message : Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'),
            'error'
        );
    }
}
