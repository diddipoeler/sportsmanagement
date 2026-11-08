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
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class RquotesHelper
{
    private const MAX_CATEGORY_IDS = 100;
    private const MAX_CATEGORY_VALUES = 200;
    private const MAX_CATEGORY_STRING_BYTES = 4096;

    /**
     * Create the random-quote layout context for the Joomla site module.
     *
     * Chooses text or database content, validates media-server configuration, rotates quotes according to the module settings and falls back to empty results on database errors.
     *
     * @param Registry $params Module display and selection settings.
     * @param Registry $componentParams SportsManagement component configuration.
     * @param CMSApplicationInterface $app Current Joomla application.
     * @param DatabaseInterface $fallbackDatabase Default database connection.
     * @return array<string, mixed> Source, style, quotes, text and picture server.
     */
    public function getData(
        Registry $params,
        Registry $componentParams,
        CMSApplicationInterface $app,
        DatabaseInterface $fallbackDatabase
    ): array {
        $rawSource = $params->get('source', 'text');
        $source = is_string($rawSource) ? strtolower(trim($rawSource)) : 'text';
        $rawStyle = $params->get('template', 'default');
        $style = $this->normaliseStyle(is_string($rawStyle) ? $rawStyle : 'default');
        $databaseSelector = self::normaliseDatabaseSelector($params->get(
            'cfg_which_database',
            $componentParams->get('cfg_which_database', 0)
        ));
        $pictureServer = self::pictureServer(
            $databaseSelector,
            $componentParams->get('cfg_which_database_server', ''),
            Uri::root()
        );

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

        $categorySelection = $params->get('category', []);
        $categoryIds = $this->normaliseIds($categorySelection);

        // An explicitly malformed filter must not broaden the query to
        // every published quote when no valid category survives parsing.
        if (self::invalidCategorySelection($categorySelection, $categoryIds)) {
            return [
                'source' => 'db',
                'style' => $style,
                'list' => [],
                'textLine' => '',
                'pictureServer' => $pictureServer,
            ];
        }

        $rawRotation = $params->get('rotate', 'single_random');
        $rotation = is_string($rawRotation) ? strtolower($rawRotation) : 'single_random';

        try {
            $db = $this->database($databaseSelector, $fallbackDatabase);
            $list = match ($rotation) {
                'multiple_random' => $this->multipleRandom(
                    $db,
                    $this->randomCategory($categoryIds),
                    self::normalisePositiveLimit($params->get('num_of_random', 2), 2, 2500)
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
            // SQL, credentials and filesystem details belong in the server log,
            // never in a message displayed to anonymous site visitors.
            Log::add(
                'SportsManagement Rquotes query failed: ' . $e->getMessage(),
                Log::ERROR,
                'com_sportsmanagement'
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

    /**
     * Resolve the safe media base for local or remote quotation pictures.
     *
     * The native dispatcher and retained legacy module helper use the same
     * policy: only a valid positive database selector opts into a remote
     * HTTP(S) media server, and that URL cannot contain credentials, query
     * strings, fragments or control characters. Invalid remote settings
     * disable relative image links instead of falling back to a wrong host.
     *
     * @param mixed $selector Configured SportsManagement database selector.
     * @param mixed $remoteBase Remote image-server URL from component settings.
     * @param string $localBase Trusted Joomla site root URL.
     * @return string Normalised base URL with a slash, or an empty string.
     */
    public static function pictureServer(mixed $selector, mixed $remoteBase, string $localBase): string
    {
        if (self::normaliseDatabaseSelector($selector) === 0) {
            return $localBase;
        }

        if (!is_string($remoteBase)) {
            return '';
        }

        $url = trim($remoteBase);

        if (filter_var($url, FILTER_VALIDATE_URL) === false
            || !in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)
            || parse_url($url, PHP_URL_USER) !== null
            || parse_url($url, PHP_URL_PASS) !== null
            || parse_url($url, PHP_URL_QUERY) !== null
            || parse_url($url, PHP_URL_FRAGMENT) !== null
            || preg_match('/[\x00-\x1F\x7F]/', $url)) {
            return '';
        }

        return rtrim($url, '/') . '/';
    }

    /**
     * Validate the database selector without lossy PHP type conversion.
     *
     * Only nonnegative integer values and decimal strings within the PHP integer range are accepted.
     *
     * @param mixed $value Raw database selector.
     * @return int Nonnegative selector, defaulting to zero.
     */
    private static function normaliseDatabaseSelector(mixed $value): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return 0;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $selector = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $selector === false ? 0 : $selector;
    }

    /**
     * Choose a bounded positive quote count.
     *
     * Rejects malformed values and integer overflow while limiting accepted counts to the configured maximum.
     *
     * @param mixed $value Raw configured count.
     * @param int $default Count used for invalid input.
     * @param int $maximum Upper bound for the count.
     * @return int Bounded number of quotes.
     */
    private static function normalisePositiveLimit(mixed $value, int $default, int $maximum): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return $default;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $number === false ? $default : min($maximum, max(1, $number));
    }

    /**
     * Select one random quote from the requested categories.
     *
     * Loads eligible records and returns a single randomly selected object or an empty list.
     *
     * @param DatabaseInterface $db Quotes database.
     * @param array<int, int> $categoryIds Category filters.
     * @return array<int, object> Zero or one quote.
     */
    private function singleRandom(DatabaseInterface $db, array $categoryIds): array
    {
        $rows = $this->quoteRows($db, $categoryIds);
        if (!$rows) {
            return [];
        }

        return [$rows[array_rand($rows)]];
    }

    /**
     * Select a limited random sample of published quotes.
     *
     * Shuffles the eligible rows and returns at most the requested number without duplicates.
     *
     * @param DatabaseInterface $db Quotes database.
     * @param array<int, int> $categoryIds Category filters.
     * @param int $count Maximum number of quotes.
     * @return array<int, object> Random quote sample.
     */
    private function multipleRandom(DatabaseInterface $db, array $categoryIds, int $count): array
    {
        $rows = $this->quoteRows($db, $categoryIds);
        if (!$rows) {
            return [];
        }

        shuffle($rows);
        return array_slice($rows, 0, min($count, count($rows)));
    }

    /**
     * Display the next quote in a cookie-tracked sequence.
     *
     * Keeps the selected index within current bounds and writes a short-lived cookie only when response headers are available.
     *
     * @param DatabaseInterface $db Quotes database.
     * @param array<int, int> $categoryIds Category filters.
     * @param CMSApplicationInterface $app Current Joomla application.
     * @return array<int, object> The selected quote or an empty list.
     */
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
        $next = self::nextSequentialIndex($cookie->get('rquote', null, 'RAW'), count($rows));
        // Do not interpret malformed or outdated cookie data as a valid index.
        $index = $next ?? random_int(0, count($rows) - 1);

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

    /**
     * Derive the next sequential quote index from a validated cookie value.
     *
     * Accepts only nonnegative integers or decimal strings within the current
     * quote count. Rejects arrays, booleans, partial numbers and overflow;
     * null indicates that the caller should choose a fresh random position.
     *
     * @param mixed $rawIndex Untrusted sequence position from the cookie.
     * @param int $count Number of available quotes.
     * @return int|null Next zero-based index, or null for invalid input.
     */
    private static function nextSequentialIndex(mixed $rawIndex, int $count): ?int
    {
        if ($count <= 0 || (!is_int($rawIndex)
            && (!is_string($rawIndex) || preg_match('/^[0-9]+$/D', $rawIndex) !== 1))) {
            return null;
        }

        if (is_string($rawIndex)) {
            $digits = ltrim($rawIndex, '0');
            $rawIndex = $digits === '' ? '0' : $digits;
        }

        $index = filter_var($rawIndex, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $index === false || $index >= $count ? null : ($index + 1) % $count;
    }

    /**
     * Check whether persisted rotation metadata already matches a quote index.
     *
     * Accepts an integer or zero-padded decimal string representing the same
     * positive index. Invalid values, zeros and positions beyond the current
     * collection never match, so they can be repaired in storage.
     *
     * @param mixed $savedNumber Value from the rotation metadata row.
     * @param int $number Positive, bounded current quote index.
     * @return bool Whether the saved position is already correct.
     */
    private static function savedPositionMatches(mixed $savedNumber, int $number): bool
    {
        return (is_int($savedNumber) && $savedNumber === $number)
            || (is_string($savedNumber)
                && preg_match('/^[0-9]+$/D', $savedNumber) === 1
                && ltrim($savedNumber, '0') === (string) $number);
    }

    /**
     * Select a stable quote for a daily, weekly, monthly or yearly period.
     *
     * Loads saved rotation metadata, advances or clamps its position when necessary and returns one quote for the current token.
     *
     * @param DatabaseInterface $db Quotes database.
     * @param array<int, int> $categoryIds Category filters.
     * @param int $metaId Saved rotation record identifier.
     * @param string $dateFormat PHP date format used for the rotation token.
     * @param CMSApplicationInterface $app Current Joomla application.
     * @return array<int, object> Quote for this period or an empty list.
     */
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
        $savedNumber = $meta->number_reached ?? 1;
        $number = self::normalisePositiveLimit($savedNumber, 1, $count);
        // Invalid, zero or out-of-range saved values must be persisted even
        // if their fallback position happens to match the current index.
        $savedNumberMatches = self::savedPositionMatches($savedNumber, $number);

        if (!$meta) {
            $this->storeMeta($db, $metaId, $number, $token, false);
        } elseif (!is_string($meta->date_modified ?? null) || $meta->date_modified !== $token) {
            $number = $number >= $count ? 1 : $number + 1;
            $this->storeMeta($db, $metaId, $number, $token, true);
        } elseif (!$savedNumberMatches) {
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

    /**
     * Return the quote associated with the current day of the year.
     *
     * Uses a one-based day number for numbered records and a stable fallback when no matching quote exists.
     *
     * @param DatabaseInterface $db Quotes database.
     * @param array<int, int> $categoryIds Category filters.
     * @param CMSApplicationInterface $app Current Joomla application.
     * @return array<int, object> Zero or one quote for the day.
     */
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

    /**
     * Query published quote records and their associated person picture.
     *
     * Applies allowed category filters and an optional daily number,
     * limiting numbered lookups to one record. Malformed database rows are
     * discarded before quote selection or image URL generation. Each quote
     * must also have a valid positive record ID to participate in rotations.
     *
     * @param DatabaseInterface $db Quotes database.
     * @param array<int, int> $categoryIds Optional category IDs.
     * @param int|null $dailyNumber Optional one-based daily quote number.
     * @return array<int, object> Quote records.
     */
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
        $rows = $db->loadObjectList();

        return self::validQuoteRows($rows);
    }

    /**
     * Keep only quote objects with valid positive primary keys.
     *
     * Some old or custom database drivers may return malformed entries.
     * Excluding them before selecting a quote avoids blank quote records and
     * incorrect collection sizes in sequential or periodic rotations.
     *
     * @param mixed $rows Raw result of a Joomla database quote query.
     * @return array<int, object> Quote objects with validated numeric IDs.
     */
    private static function validQuoteRows(mixed $rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $valid = [];

        foreach ($rows as $row) {
            if (!is_object($row)) {
                continue;
            }

            $id = $row->id ?? null;

            if (!is_int($id) && (!is_string($id) || preg_match('/^[0-9]+$/D', $id) !== 1)) {
                continue;
            }

            $digits = is_string($id) ? ltrim($id, '0') : $id;

            if ($digits === '' || $digits === 0) {
                continue;
            }

            $number = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($number !== false) {
                $valid[] = $row;
            }
        }

        return $valid;
    }

    /**
     * Load the stored position of a periodic quote rotation.
     *
     * Retrieves the selected metadata record or returns null if not found.
     *
     * @param DatabaseInterface $db Quotes database.
     * @param int $metaId Rotation metadata identifier.
     * @return object|null Persisted rotation record.
     */
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

    /**
     * Persist the quote index and time token for a rotation.
     *
     * Updates an existing metadata record or inserts the first record for that period.
     *
     * @param DatabaseInterface $db Quotes database.
     * @param int $metaId Rotation record identifier.
     * @param int $number One-based selected quote position.
     * @param string $token Current date period token.
     * @param bool $exists Whether to update an existing metadata row.
     * @return void
     */
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

    /**
     * Choose a line from the configured safe local quote text file.
     *
     * Restricts filenames, rejects symbolic links and oversized files, then selects a random or date-indexed nonblank line.
     *
     * @param Registry $params Text-file and display settings.
     * @param CMSApplicationInterface $app Joomla application for local date.
     * @return string Selected text line or an empty string.
     */
    private function textLine(Registry $params, CMSApplicationInterface $app): string
    {
        $configured = $params->get('filename', 'rquotes.txt');

        // The module displays plain-text quotes, not arbitrary source files
        // or files outside its directory reached through symbolic links.
        if (!is_string($configured)) {
            return '';
        }

        $filename = trim($configured);

        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]*\.txt$/iD', $filename)
            || str_contains($filename, '..')) {
            return '';
        }

        $root = realpath(dirname(__DIR__, 2));

        if ($root === false) {
            return '';
        }

        $candidate = $root . DIRECTORY_SEPARATOR . $filename;

        if (is_link($candidate)) {
            return '';
        }

        $path = realpath($candidate);

        if ($path === false || dirname($path) !== $root || !is_file($path) || !is_readable($path)) {
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

    /**
     * Build a safe image URL for an individual quote.
     *
     * Accepts only string-based image fields, validates absolute HTTP(S)
     * URLs and encodes safe relative segments against the media server.
     * Missing or malformed image values result in an empty URL. This public
     * method is shared with retained Joomla 5/6 legacy module overrides.
     *
     * @param object $quote Quote record with image fields.
     * @param string $pictureServer Validated image base URL.
     * @return string Safe image URL or an empty string.
     */
    public function pictureUrl(object $quote, string $pictureServer): string
    {
        $personPicture = $quote->person_picture ?? '';
        $path = is_string($personPicture) ? trim($personPicture) : '';
        if ($path === '') {
            $quotePicture = $quote->picture ?? '';
            $path = is_string($quotePicture) ? trim($quotePicture) : '';
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

    /**
     * Choose one configured quote category randomly.
     *
     * Returns a one-element category filter or an empty filter when none is selected.
     *
     * @param array<int, int> $ids Allowed category IDs.
     * @return array<int, int> One category ID or an empty list.
     */
    private function randomCategory(array $ids): array
    {
        return $ids ? [$ids[array_rand($ids)]] : [];
    }

    /**
     * Select the first configured quote category.
     *
     * Provides a consistent category for persisted rotation modes.
     *
     * @param array<int, int> $ids Allowed category IDs.
     * @return array<int, int> First ID or an empty list.
     */
    private function firstCategory(array $ids): array
    {
        return $ids ? [(int) reset($ids)] : [];
    }

    /**
     * Parse and limit configured quote category identifiers.
     *
     * Accepts numeric IDs, zero-padded legacy identifiers and Joomla ID:alias
     * strings, removes duplicates and rejects zero, malformed or overflowing
     * selectors before using category IDs in bound SQL filters.
     *
     * @param mixed $values Raw scalar or array module selection.
     * @return array<int, int> Unique positive category IDs.
     */
    private function normaliseIds(mixed $values): array
    {
        $values = is_array($values) ? $values : [$values];
        $ids = [];

        foreach (array_slice($values, 0, self::MAX_CATEGORY_VALUES) as $value) {
            if ((!is_int($value) && !is_string($value))
                || (is_string($value) && strlen($value) > self::MAX_CATEGORY_STRING_BYTES)) {
                continue;
            }

            foreach (preg_split('/[\s,;]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                // Joomla selectors may include leading zeroes and ID:alias
                // values. Validate the complete field before normalising IDs.
                if (!preg_match('/^([0-9]+)(?::[A-Za-z0-9_-]+)?$/D', $part, $match)) {
                    continue;
                }

                $digits = ltrim($match[1], '0');

                if ($digits === '') {
                    continue;
                }

                $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                if ($id !== false) {
                    $ids[$id] = $id;

                    if (count($ids) >= self::MAX_CATEGORY_IDS) {
                        return array_values($ids);
                    }
                }
            }
        }

        return array_values($ids);
    }

    /**
     * Detect an explicitly invalid category filter that selects no valid IDs.
     *
     * Blank selectors and Joomla's numeric zero mean no category restriction.
     * A malformed nonblank selector must instead return no quotes, avoiding a
     * query against all categories after validation rejects its entries.
     *
     * @param mixed $selection Original scalar or array module selection.
     * @param array<int, int> $categoryIds Validated positive category IDs.
     * @return bool Whether a configured but invalid filter would be broadened.
     */
    private static function invalidCategorySelection(mixed $selection, array $categoryIds): bool
    {
        if ($categoryIds !== []) {
            return false;
        }

        foreach (is_array($selection) ? $selection : [$selection] as $entry) {
            if ($entry === null || $entry === 0) {
                continue;
            }

            if (is_string($entry)) {
                $text = trim($entry);

                if ($text === '' || preg_match('/^0+$/D', $text) === 1) {
                    continue;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Allow only supported random-quote template styles.
     *
     * Unknown styles fall back to the default template rather than arbitrary layout names.
     *
     * @param string $style Requested style.
     * @return string Allowed style identifier.
     */
    private function normaliseStyle(string $style): string
    {
        return in_array($style, ['default', 'bold', 'italic', 'style', 'sticker'], true)
            ? $style
            : 'default';
    }

    /**
     * Return the current time in the Joomla site's configured timezone.
     *
     * Falls back to UTC if the configured timezone cannot be instantiated.
     *
     * @param CMSApplicationInterface $app Application providing timezone settings.
     * @return \DateTimeImmutable Current local date and time.
     */
    private function now(CMSApplicationInterface $app): \DateTimeImmutable
    {
        try {
            $timezone = new \DateTimeZone((string) $app->get('offset', 'UTC'));
        } catch (\Throwable) {
            $timezone = new \DateTimeZone('UTC');
        }

        return new \DateTimeImmutable('now', $timezone);
    }

    /**
     * Resolve the database configured for quote lookup.
     *
     * Delegates connection selection to the SportsManagement database resolver.
     *
     * @param int $selector Database configuration identifier.
     * @param DatabaseInterface $fallbackDatabase Default Joomla connection.
     * @return DatabaseInterface Resolved quote database.
     */
    private function database(int $selector, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve($fallbackDatabase, $selector);
    }
}
