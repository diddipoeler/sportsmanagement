<?php
/**
 * Regression tests for safe Joomla 5/6 ranking IDs and module database services.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $items = []) {}

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->items[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS {
    final class Factory
    {
        public static function getContainer(): object
        {
            return new class {
                public function get(string $service): object
                {
                    return new \stdClass();
                }
            };
        }
    }
}

namespace {
    use Diddipoeler\Module\SportsManagementLiveticker\Site\Helper\LivetickerHelper;
    use Diddipoeler\Module\SportsManagementRanking\Site\Helper\RankingHelper;
    use Joomla\CMS\Application\CMSApplicationInterface;
    use Joomla\Database\DatabaseInterface;
    use Joomla\Registry\Registry;

    \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_ranking/src/Helper/RankingHelper.php';
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_liveticker/src/Helper/LivetickerHelper.php';

    $ranking = new RankingHelper();
    $app = new class implements CMSApplicationInterface {
        public function isClient(string $client): bool
        {
            return $client === 'site';
        }
    };

    $invalidProjectIds = [
        0, -1, null, false, true, 7.5,
        '0', '-8', '3extra', '3:bad:slug', '3.0', ' 3:bad slug ',
        '999999999999999999999999999999',
        [], ['5garbage'], [['5']],
    ];

    foreach ($invalidProjectIds as $id) {
        $data = $ranking->getData(new Registry(['p' => $id]), (object) ['id' => 4], $app);
        if ($data !== []) {
            throw new \RuntimeException('Invalid project ID returned ranking data: ' . var_export($id, true));
        }
    }

    $firstId = new \ReflectionMethod($ranking, 'firstId');
    foreach ([3, '3', '3:alias', ['3:alias'], ['3', '6']] as $id) {
        if ($firstId->invoke($ranking, $id) !== 3) {
            throw new \RuntimeException('Valid Joomla project selection was rejected.');
        }
    }

    // Both initial rendering and authenticated AJAX refresh must validate
    // the project selector through the same canonical ID parser.
    $source = file_get_contents(
        dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_ranking/src/Helper/RankingHelper.php'
    );
    if ($source === false || substr_count($source, "\$projectId = \$this->firstId(\$params->get('p', 0));") !== 2) {
        throw new \RuntimeException('Ranking project validation differs between display and refresh.');
    }

    $validDatabase = new class implements DatabaseInterface {};
    $liveticker = new LivetickerHelper();
    $livetickerDatabase = new \ReflectionMethod($liveticker, 'joomlaDatabase');

    if ($livetickerDatabase->invoke($liveticker, $validDatabase) !== $validDatabase) {
        throw new \RuntimeException('Liveticker rejected an explicitly supplied Joomla database.');
    }

    foreach ([
        ['Ranking', new \ReflectionMethod(RankingHelper::class, 'joomlaDatabase'), null],
        ['Liveticker', $livetickerDatabase, $liveticker],
    ] as [$label, $method, $instance]) {
        try {
            $method->invoke($instance);
        } catch (\RuntimeException $exception) {
            if ($exception->getCode() === 500 && str_contains($exception->getMessage(), 'database service')) {
                continue;
            }

            throw $exception;
        }

        throw new \RuntimeException($label . ' accepted a non-DatabaseInterface container service.');
    }

    echo "Ranking project IDs and Joomla 5/6 module database guards OK.\n";
}
