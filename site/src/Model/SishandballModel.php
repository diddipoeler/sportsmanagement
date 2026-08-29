<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use DOMDocument;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Http\HttpFactory;
use Throwable;

/**
 * Joomla 5/6 model for the legacy SIS handball XML integration.
 */
final class SishandballModel extends BaseDatabaseModel
{
    private const CACHE_TTL = 1800;

    private function siteApplication(): SiteApplication
    {
        return Factory::getContainer()->get(SiteApplication::class);
    }

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

    public function getTabelle($linkResults, $leagueNumber, $sisType)
    {
        return $this->loadXml(
            (string) $linkResults,
            'tab_sis_art_' . $this->safeToken($sisType) . '_ln_' . $this->safeToken($leagueNumber) . '.xml'
        );
    }

    public function getStatistik($linkResults, $leagueNumber)
    {
        return $this->loadXml(
            (string) $linkResults,
            'stat_' . $this->safeToken($leagueNumber) . '.xml'
        );
    }

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
            $xml = simplexml_load_file($cacheFile);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return $xml ?: false;
    }

    private function downloadXml(string $url): ?string
    {
        try {
            $http = (new HttpFactory())->getHttp();
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
            $this->reportError($e->getMessage());
            return null;
        }
    }

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

    private function safeToken($value): string
    {
        $token = preg_replace('/[^A-Za-z0-9_.-]/', '_', (string) $value);
        return $token !== null && $token !== '' ? $token : '0';
    }

    private function reportError(string $message): void
    {
        $this->siteApplication()->enqueueMessage(
            $message !== '' ? $message : Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'),
            'error'
        );
    }
}
