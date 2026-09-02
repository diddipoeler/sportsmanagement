<?php
/**
 * Joomla 5/6-native data helper for the random quotes module.
 *
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementRquotes\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
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
        $pictureServer = $databaseSelector
            ? rtrim((string) $componentParams->get('cfg_which_database_server', ''), '/') . '/'
            : Uri::root();

        if ($source === 'text') {
            return [
                'source' => 'text',
                'style' => $style,
                'list' => [],
                'textLine' => $this->textLine($params),
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

        $db = $this->database($databaseSelector, $fallbackDatabase);
        $categoryIds = $this->normaliseIds($params->get('category', []));
        $rotation = strtolower((string) $params->get('rotate', 'single_random'));

        try {
            $list = match ($rotation) {
                'multiple_random' => $this->multipleRandom(
                    $db,
                    $this->randomCategory($categoryIds),
                    max(1, (int) $params->get('num_of_random', 2))
                ),
                'sequential' => $this->sequential($db, $categoryIds, $app),
                'daily' => $this->periodic($db, $this->firstCategory($categoryIds), 1, 'j', $app),
                'weekly' => $this->periodic($db, $this->firstCategory($categoryIds), 2, 'W', $app),
                'monthly' => $this->periodic($db, $this->firstCategory($categoryIds), 3, 'n', $app),
                'yearly' => $this->periodic($db, $this->firstCategory($categoryIds), 4, 'Y', $app),
                'today' => $this->todayQuote($db, $this->firstCategory($categoryIds), $app),
                default => $this->singleRandom($db, $this->randomCategory($categoryIds)),
            };
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

        foreach ($list as $quote) {
            $quote->picture_url = $this->pictureUrl($quote, $pictureServer);
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
        $index = $current >= 0
            ? ($current + 1) % count($rows)
            : random_int(0, count($rows) - 1);

        setcookie('rquote', (string) $index, [
            'expires' => time() + 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

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
        $number = max(1, (int) ($meta->number_reached ?? 1));

        if (!$meta) {
            $this->storeMeta($db, $metaId, $number, $token, false);
        } elseif ((string) ($meta->date_modified ?? '') !== $token) {
            $number = $number >= $count ? 1 : $number + 1;
            $this->storeMeta($db, $metaId, $number, $token, true);
        }

        $selected = $this->quoteRows($db, $categoryIds, $number);
        return $selected ?: $this->singleRandom($db, $categoryIds);
    }

    private function todayQuote(DatabaseInterface $db, array $categoryIds, CMSApplicationInterface $app): array
    {
        $dayOfYear = (int) $this->now($app)->format('z');
        $rows = $this->quoteRows($db, $categoryIds, $dayOfYear);
        return $rows ?: $this->singleRandom($db, $categoryIds);
    }

    private function quoteRows(DatabaseInterface $db, array $categoryIds, ?int $dailyNumber = null): array
    {
        $query = $db->getQuery(true)
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
            $query->where($db->quoteName('obj.catid') . ' IN (' . implode(',', $categoryIds) . ')');
        }
        if ($dailyNumber !== null) {
            $query->where($db->quoteName('obj.daily_number') . ' = ' . (int) $dailyNumber);
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    private function loadMeta(DatabaseInterface $db, int $metaId): ?object
    {
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('number_reached'),
                $db->quoteName('date_modified'),
            ])
            ->from($db->quoteName('#__rquote_meta'))
            ->where($db->quoteName('id') . ' = ' . $metaId);
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

    private function textLine(Registry $params): string
    {
        $filename = basename(trim((string) $params->get('filename', 'rquotes.txt')));
        if ($filename === '') {
            return '';
        }

        $path = dirname(__DIR__, 2) . '/mod_sportsmanagement_rquotes/' . $filename;
        if (!is_file($path) || !is_readable($path)) {
            return '';
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return '';
        }

        $lines = preg_split('/\R/u', $contents) ?: [];
        $lines = array_values(array_filter($lines, static fn(string $line): bool => trim($line) !== ''));
        if (!$lines) {
            return '';
        }

        if ((bool) $params->get('randomtext', 0)) {
            $index = min((int) date('j') - 1, count($lines) - 1);
            return $lines[max(0, $index)];
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
            return $path;
        }

        return rtrim($pictureServer, '/') . '/' . ltrim($path, '/');
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
            foreach (preg_split('/[\s,;]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                $id = (int) $part;
                if ($id > 0) {
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
