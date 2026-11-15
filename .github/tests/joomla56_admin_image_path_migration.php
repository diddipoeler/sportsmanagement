<?php
/**
 * Isolated Joomla 5/6 regressions for the protected image-path migration.
 *
 * Usage: php .github/tests/joomla56_admin_image_path_migration.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace {
    \define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Helper/ImagePathMigrationHelper.php';

    /**
     * Record one Joomla query-builder update without connecting to SQL.
     *
     * The fake records quoted SQL segments so that identifier, column and
     * string-literal safety can be checked without a Joomla installation.
     */
    final class ImagePathMigrationTestQuery
    {
        public string $table = '';
        public string $assignment = '';
        public string $condition = '';

        /**
         * Capture the update target used for one image-path replacement.
         * @param string $table Joomla-quoted target table identifier.
         * @return self This query builder for chaining.
         */
        public function update(string $table): self
        {
            $this->table = $table;
            return $this;
        }

        /**
         * Capture the SQL assignment used for the legacy path replacement.
         * @param string $assignment REPLACE expression with quoted values.
         * @return self This query builder for chaining.
         */
        public function set(string $assignment): self
        {
            $this->assignment = $assignment;
            return $this;
        }

        /**
         * Capture the affected-row condition for a single replacement.
         * @param string $condition SQL LIKE expression for source path.
         * @return self This query builder for chaining.
         */
        public function where(string $condition): self
        {
            $this->condition = $condition;
            return $this;
        }
    }

    /**
     * Simulate Joomla's database service and record each committed query.
     *
     * A configurable failure index verifies that an explicit database
     * failure stops further updates instead of reporting partial success.
     */
    final class ImagePathMigrationTestDatabase implements \Joomla\Database\DatabaseInterface
    {
        /** @var list<ImagePathMigrationTestQuery> */
        public array $queries = [];
        public ?int $failAt = null;

        /**
         * Return a fresh update query builder for the next migration step.
         * @return ImagePathMigrationTestQuery Mutable fake query builder.
         */
        public function createQuery(): ImagePathMigrationTestQuery
        {
            return new ImagePathMigrationTestQuery();
        }

        /**
         * Mimic Joomla identifier quoting for fixed table and field names.
         * @param string $identifier Unquoted database identifier.
         * @return string Backtick-quoted identifier.
         */
        public function quoteName(string $identifier): string
        {
            return chr(96) . str_replace(chr(96), chr(96) . chr(96), $identifier) . chr(96);
        }

        /**
         * Quote a migration replacement value as a SQL string literal.
         * @param string $literal Source or replacement path component.
         * @return string SQL-quoted string.
         */
        public function quote(string $literal): string
        {
            return "'" . str_replace("'", "''", $literal) . "'";
        }

        /**
         * Collect the pending UPDATE statement without executing real SQL.
         * @param ImagePathMigrationTestQuery $query Prepared update query.
         * @return void
         */
        public function setQuery(ImagePathMigrationTestQuery $query): void
        {
            $this->queries[] = $query;
        }

        /**
         * Report simulated success except at the configured failure index.
         * @return bool Whether the current migration statement succeeded.
         */
        public function execute(): bool
        {
            return $this->failAt !== count($this->queries);
        }

        /**
         * Return a fixed row count to test summary aggregation.
         * @return int Number of changed rows in this simulated operation.
         */
        public function getAffectedRows(): int
        {
            return 2;
        }
    }

    $class = \Diddipoeler\Component\SportsManagement\Administrator\Helper\ImagePathMigrationHelper::class;
    $database = new ImagePathMigrationTestDatabase();

    // Loading the helper must never execute updates without an explicit call.
    if ($database->queries !== []) {
        throw new \RuntimeException('Image-path migration executed without an administrator action.');
    }

    $summary = $class::migrate($database);

    if ($summary !== ['operations' => 15, 'updatedRows' => 30] || count($database->queries) !== 15) {
        throw new \RuntimeException('Migration step count or affected-row summary changed.');
    }

    $quoted = chr(96);
    $first = $database->queries[0];
    $last = $database->queries[14];

    if ($first->table !== $quoted . '#__sportsmanagement_person' . $quoted
        || $first->assignment !== $quoted . 'picture' . $quoted . ' = REPLACE(' . $quoted . 'picture' . $quoted . ", 'placeholders', 'persons')"
        || $first->condition !== $quoted . 'picture' . $quoted . " LIKE '%placeholders%'"
        || $last->table !== $quoted . '#__sportsmanagement_club' . $quoted
        || $last->assignment !== $quoted . 'trikot_away' . $quoted . ' = REPLACE(' . $quoted . 'trikot_away' . $quoted . ", 'media', 'images')") {
        throw new \RuntimeException('Historical migration order or SQL quoting was changed.');
    }

    foreach ($database->queries as $query) {
        if (!in_array($query->table, [$quoted . '#__sportsmanagement_person' . $quoted, $quoted . '#__sportsmanagement_club' . $quoted], true)) {
            throw new \RuntimeException('Unexpected table in the path migration.');
        }

        if (preg_match('/^' . preg_quote($quoted, '/') . '(?:picture|logo_big|logo_middle|logo_small|trikot_home|trikot_away)'
            . preg_quote($quoted, '/') . ' = REPLACE\(/', $query->assignment) !== 1) {
            throw new \RuntimeException('Unexpected column in the path migration.');
        }
    }

    $failure = new ImagePathMigrationTestDatabase();
    $failure->failAt = 4;

    try {
        $class::migrate($failure);
        throw new \RuntimeException('Migration did not fail when a database update returned false.');
    } catch (\RuntimeException $exception) {
        if (!str_contains($exception->getMessage(), 'image-path migration failed')) {
            throw $exception;
        }
    }

    if (count($failure->queries) !== 4) {
        throw new \RuntimeException('Migration continued after an SQL failure.');
    }

    // The legacy screen must never modify SQL through an unprotected GET.
    $view = file_get_contents(dirname(__DIR__, 2) . '/admin/assets/updates/jsm_update_picture_path.php');
    if (!is_string($view)
        || !str_contains($view, "->post->getInt('run_image_path_migration'")
        || !str_contains($view, "->checkToken('post')")
        || !str_contains($view, "authorise('core.admin', 'com_sportsmanagement')")
        || str_contains($view, 'Factory::getApplication()')
        || str_contains($view, 'sportsmanagementHelper::getDBConnection()')
        || str_contains($view, '$this->jsmdb')) {
        throw new \RuntimeException('Legacy image-path screen lost its Joomla 5/6 security guards.');
    }

    echo "Joomla 5/6 administrator image-path migration regressions passed.\n";
}
