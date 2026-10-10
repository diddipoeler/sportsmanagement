<?php
/**
 * Joomla 5/6-native data helper for the random quotes module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementRquotes\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class RquotesHelper
{
    public function getData(
        Registry $params,
        Registry $componentParams,
        CMSApplicationInterface $app,
        DatabaseInterface $fallbackDatabase
    ): array {
        $source = strtolower(trim((string) $params->get('source', 'text')));
        $style = $this->normaliseStyle((string) $params->get('template', 'default'));
        $databaseSelector = (int) $params->get(
            'cfg_which_database',
            $componentParams->get('cfg_which_database', 0)
        );
        $remotePictureServer = trim((string) $componentParams->get('cfg_which_database_server', ''));
        // Do not construct image links from an invalid or unsafe remote base URL.
        $remotePictureServer = filter_var($remotePictureServer, FILTER_VALIDATE_URL)
            && in_array(strtolower((string) parse_url($remotePictureServer, PHP_URL_SCHEME)), ['http', 'https'], true)
            && parse_url($remotePictureServer, PHP_URL_QUERY) === null
            && parse_url($remotePictureServer, PHP_URL_FRAGMENT) === null
            // Embedded credentials must not be exposed in generated image URLs.
            && parse_url($remotePictureServer, PHP_URL_USER) === null
            && parse_url($remotePictureServer, PHP_URL_PASS) === null
            ? $remotePictureServer
            : '';
        $pictureServer = $databaseSelector
            ? ($remotePictureServer !== '' ? rtrim($remotePictureServer, '/') . '/' : '')
            : Uri::root();

        if ($source === 'text') {
            return [
                'source' => 'text',
                'style' => $style,
                'list' => [],
                'textLine' => $this->textLine($params, $app),
                'pictureServer' => $pictureServer,
            ];
        }

        if ($source !== 'db') {
            return [
                'source' => $source,
                'style' => $style,
                'list' => [],
                'textLine' => '',
                'pictureServer' => $pictureServer,
            ];
        }

        $categoryIds = $this->normaliseIds($params->get('category', []));
        $rotation = strtolower((string) $params->get('rotate', 'single_random'));

        try {
            $db = $this->database($databaseSelector, $fallbackDatabase);
            $list = match ($rotation) {
                'multiple_random' => $this->multipleRandom(
                    $db,
                    $this->randomCategory($categoryIds),
                    min(2500, max(1, (int) $params->get('num_of_random', 2)))
                ),
                'sequential' => $this->sequential($db, $categoryIds, $app),
                'daily' => $this->periodic($db, $this->firstCategory($categoryIds), 1, 'Y-m-d', $app),
                'weekly' => $this->periodic($db, $this->firstCategory($categoryIds), 2, 'o-W', $app),
                'monthly' => $this->periodic($db, $this->firstCategory($categoryIds), 3, 'Y-m', $app),
                'yearly' => $this->periodic($db, $this->firstCategory($categoryIds), 4, 'Y', $app),
                'today' => $this->todayQuote($db, $this->firstCategory($categoryIds), $app),
                default => $this->singleRandom($db, $this->randomCategory($categoryIds)),
            };

            // Keep image URL normalisation inside the same error boundary as
            // the database fetch so malformed legacy data cannot break the page.
            foreach ($list as $quote) {
                $quote->picture_url = $this->pictureUrl($quote, $pictureServer);
            }
        } catch (\Throwable $e) {
            $app->enqueueMessage(
                Text::sprintf(
                    'COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED',
                    $e->getCode(),
                    $e->getMessage()
                ),
                'error'
            );
            $list = [];
        }

        return [
            'source' => 'db',
            'style' => $style,
            'list' => $list,
            'textLine' => '',
            'pictureServer' => $pictureServer,
        ];
    }

    private function singleRandom(DatabaseInterface $db, array $categoryIds): array
    {
        $rows = $this->quoteRows($db, $categoryIds);
        if (!$rows) {
            return [];
        }

        return [$rows[array_rand($rows)]];
    }

    private function multipleRandom(DatabaseInterface $db, array $categoryIds, int $count): array
    {
        $rows = $this->quoteRows($db, $categoryIds);
        if (!$rows) {
            return [];
        }

        shuffle($rows);
        return array_slice($rows, 0, min($count, count($rows)));
    }

    private function sequential(DatabaseInterface $db, array $categoryIds, CMSApplicationInterface $app): array
    {
        if (count($categoryIds) > 1) {
            $app->enqueueMessage(
                Text::_('MOD_SPORTSMANAGEMENT_RQUOTES_SAVE_DISPLAY_INFORMATION_ONE'),
                'notice'
            );
            $categoryIds = [];
        }

        $rows = $this->quoteRows($db, $categoryIds);
        if (!$rows) {
            return [];
        }

        $cookie = $app->getInput()->cookie;
        $current = $cookie->getInt('rquote', -1);
        // Reject an out-of-range or stale index if the quotes list changed.
        $index = $current >= 0 && $current < count($rows)
            ? ($current + 1) % count($rows)
            : random_int(0, count($rows) - 1);

        // Modules may render after Joomla has started sending the response.
        // Avoid PHP header warnings while still returning the selected quote.
        if (!headers_sent()) {
            setcookie('rquote', (string) $index, [
                'expires' => time() + 3600,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        return [$rows[$index]];
    }

    private function periodic(
        DatabaseInterface $db,
        array $categoryIds,
        int $metaId,
        string $dateFormat,
        CMSApplicationInterface $app
    ): array {
        $rows = $this->quoteRows($db, $categoryIds);
        $count = count($rows);
        if ($count === 0) {
            return [];
        }

        $token = $this->now($app)->format($dateFormat);
        $meta = $this->loadMeta($db, $metaId);
        // A category may have fewer quotes than when this rotation was saved.
        // Keep the stored position within the currently available quote list.
        $number = min($count, max(1, (int) ($meta->number_reached ?? 1)));

        if (!$meta) {
            $this->storeMeta($db, $metaId, $number, $token, false);
        } elseif ((string) ($meta->date_modified ?? '') !== $token) {
            $number = $number >= $count ? 1 : $number + 1;
            $this->storeMeta($db, $metaId, $number, $token, true);
        } elseif ((int) $meta->number_reached !== $number) {
            // Persist the clamped index so obsolete values do not linger.
            $this->storeMeta($db, $metaId, $number, $token, true);
        }

        $selected = $this->quoteRows($db, $categoryIds, $number);
        // A rotation period displays one quote, even if several records
        // happen to share the same daily_number.
        $selected = array_slice($selected, 0, 1);
        // Missing daily_number assignments must not produce a different
        // random quote on each page view during the same rotation period.
        return $selected ?: [$rows[$number - 1]];
    }

    private function todayQuote(DatabaseInterface $db, array $categoryIds, CMSApplicationInterface $app): array
    {
        // PHP's z is zero-based, while configured daily_number values are one-based.
        $dayOfYear = (int) $this->now($app)->format('z') + 1;
        $rows = $this->quoteRows($db, $categoryIds, $dayOfYear);
        if ($rows) {
            // Duplicate daily_number values must not expand a single quote
            // of the day into a list of multiple quotes.
            return [reset($rows)];
        }

        // Without a matching daily_number, retain a stable quote for the day.
        $available = $this->quoteRows($db, $categoryIds);
        return $available ? [$available[($dayOfYear - 1) % count($available)]] : [];
    }

    private function quoteRows(DatabaseInterface $db, array $categoryIds, ?int $dailyNumber = null): array
    {
        $query = $db->createQuery()
            ->select([
                $db->quoteName('obj') . '.*',
                $db->quoteName('p.picture', 'person_picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_rquote', 'obj'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_person', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('obj.person_id')
            )
            ->where($db->quoteName('obj.published') . ' = 1')
            ->order($db->quoteName('obj.id') . ' ASC');

        if ($categoryIds) {
            $query->whereIn($db->quoteName('obj.catid'), $categoryIds, ParameterType::INTEGER);
        }
        if ($dailyNumber !== null) {
            $query
                ->where($db->quoteName('obj.daily_number') . ' = :dailyNumber')
                ->bind(':dailyNumber', $dailyNumber, ParameterType::INTEGER);
        }

        // Periodic and today rotations display a single quote. Limit
        // numbered lookups at the database instead of loading duplicates.
        $db->setQuery($query, 0, $dailyNumber !== null ? 1 : 0);
        return $db->loadObjectList() ?: [];
    }

    private function loadMeta(DatabaseInterface $db, int $metaId): ?object
    {
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('number_reached'),
                $db->quoteName('date_modified'),
            ])
            ->from($db->quoteName('#__rquote_meta'))
            ->where($db->quoteName('id') . ' = :metaId')
            ->bind(':metaId', $metaId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private function storeMeta(
        DatabaseInterface $db,
        int $metaId,
        int $number,
        string $token,
        bool $exists
    ): void {
        $record = (object) [
            'id' => $metaId,
            'number_reached' => $number,
            'date_modified' => $token,
        ];

        if ($exists) {
            $db->updateObject('#__rquote_meta', $record, 'id', true);
            return;
        }

        $db->insertObject('#__rquote_meta', $record);
    }

    private function textLine(Registry $params, CMSApplicationInterface $app): string
    {
        $filename = basename(trim((string) $params->get('filename', 'rquotes.txt')));
        if ($filename === '') {
            return '';
        }

        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $filename;
        if (!is_file($path) || !is_readable($path)) {
            return '';
        }

        // Quote text files should be small; do not load unbounded legacy
        // files into memory on every module rendering.
        $maxBytes = 5 * 1024 * 1024;
        $size = filesize($path);
        if ($size === false || $size > $maxBytes) {
            return '';
        }

        $contents = file_get_contents($path, false, null, 0, $maxBytes + 1);
        if ($contents === false || strlen($contents) > $maxBytes) {
            return '';
        }

        // Legacy quote files may use non-UTF-8 encodings. Splitting lines
        // must not discard the complete file on an invalid UTF-8 byte.
        $lines = preg_split('/\R/', $contents) ?: [];
        $lines = array_values(array_filter($lines, static fn(string $line): bool => trim($line) !== ''));
        if (!$lines) {
            return '';
        }

        if ((bool) $params->get('randomtext', 0)) {
            // Wrap the day-of-month index instead of repeating the final
            // line for the rest of the month when fewer than 31 exist.
            $index = ((int) $this->now($app)->format('j') - 1) % count($lines);
            return $lines[$index];
        }

        return $lines[array_rand($lines)];
    }

    private function pictureUrl(object $quote, string $pictureServer): string
    {
        $path = trim((string) ($quote->person_picture ?? ''));
        if ($path === '') {
            $path = trim((string) ($quote->picture ?? ''));
        }
        if ($path === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $path)) {
            // Reject embedded credentials and control characters from
            // quote-provided absolute URLs before they reach HTML output.
            return filter_var($path, FILTER_VALIDATE_URL)
                && parse_url($path, PHP_URL_USER) === null
                && parse_url($path, PHP_URL_PASS) === null
                && !preg_match('/[\x00-\x1F\x7F]/', $path)
                ? $path
                : '';
        }

        // Never treat a URL with another scheme or a protocol-relative URL
        // as a local image path on the configured picture server.
        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $path) || str_starts_with($path, '//')) {
            return '';
        }

        // Do not allow relative picture paths to escape the media base or
        // introduce query strings and fragments into the configured URL.
        $segments = explode('/', str_replace('\\', '/', $path));
        if (str_starts_with($path, '/')
            || str_contains($path, '\\')
            || str_contains($path, ':')
            || str_contains($path, '%')
            || in_array('.', $segments, true)
            || in_array('..', $segments, true)
            || strpbrk($path, '?#') !== false
            || preg_match('/[\x00-\x1F\x7F]/', $path)
            || preg_match('/%(?:2e|2f|5c|00|0[ad])/i', $path)) {
            return '';
        }

        // An empty external media server must not produce a root-relative URL.
        if (trim($pictureServer) === '') {
            return '';
        }

        // Encode spaces and other unsafe bytes per path segment while
        // retaining directory separators for legacy nested image paths.
        $encodedPath = implode('/', array_map('rawurlencode', explode('/', ltrim($path, '/'))));

        return rtrim($pictureServer, '/') . '/' . $encodedPath;
    }

    private function randomCategory(array $ids): array
    {
        return $ids ? [$ids[array_rand($ids)]] : [];
    }

    private function firstCategory(array $ids): array
    {
        return $ids ? [(int) reset($ids)] : [];
    }

    private function normaliseIds(mixed $values): array
    {
        $values = is_array($values) ? $values : [$values];
        $ids = [];
        foreach ($values as $value) {
            if (!is_scalar($value)) {
                continue;
            }

            foreach (preg_split('/[\s,;]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                if (!preg_match('/^(\d+)(?::[A-Za-z0-9_-]+)?$/', $part, $match)) {
                    continue;
                }

                $id = filter_var($match[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                if ($id !== false) {
                    $ids[$id] = $id;
                }
            }
        }

        return array_values($ids);
    }

    private function normaliseStyle(string $style): string
    {
        return in_array($style, ['default', 'bold', 'italic', 'style', 'sticker'], true)
            ? $style
            : 'default';
    }

    private function now(CMSApplicationInterface $app): \DateTimeImmutable
    {
        try {
            $timezone = new \DateTimeZone((string) $app->get('offset', 'UTC'));
        } catch (\Throwable) {
            $timezone = new \DateTimeZone('UTC');
        }

        return new \DateTimeImmutable('now', $timezone);
    }

    private function database(int $selector, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve($fallbackDatabase, $selector);
    }
}
