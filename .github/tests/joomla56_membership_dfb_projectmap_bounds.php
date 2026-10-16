<?php
/**
 * Joomla 5/6 regression checks for prediction notification failures, DFB identifiers, and map bounds.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS\Mail {
    interface MailerFactoryInterface {}
}
namespace Joomla\CMS {
    class Factory {
        public static object $mailer;
        public static function getContainer(): object {
            return new class {
                public function get(string $name): object {
                    return new class {
                        public function createMailer(): object { return \Joomla\CMS\Factory::$mailer; }
                    };
                }
            };
        }
    }
}
namespace Joomla\Registry {
    class Registry {
        public function get(string $name, mixed $default = null): mixed {
            return $name === 'mailfrom' ? 'sender@example.org' : ($name === 'fromname' ? 'Sports' : $default);
        }
    }
}
namespace Joomla\Database {
    class ParameterType {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
    interface DatabaseInterface {}
}
namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}
namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class PredictionentryModel {
        protected int $predictionGameId = 7;
        protected function getPredictionGame(): object { return (object) ['auto_approve' => true]; }
        protected function siteApplication(): object { return $GLOBALS['testMembershipApp']; }
        protected function getDatabase(): object { return $GLOBALS['testMembershipDb']; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementListModel {
        public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null) {}
        protected function getDatabase(): object { return $GLOBALS['testImportDb']; }
        protected function administratorApplication(): object {
            return new class {
                public function enqueueMessage(string $error, string $type): void {}
            };
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    class SiteRouteHelper {
        public static function view(string $view, array $params): string {
            return $view . '?' . http_build_query($params);
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Model/PredictionmembershipModel.php';
    require_once $root . '/admin/src/Model/JlextdfbkeyimportModel.php';
    require_once $root . '/modules/mod_sportsmanagement_projectmap/src/Helper/ProjectMapHelper.php';

    class QueryStub {
        public array $bindings = [];
        public array $ids = [];
        public function select(mixed $what): self { return $this; }
        public function from(string $what): self { return $this; }
        public function join(string $kind, string $what): self { return $this; }
        public function where(string $what): self { return $this; }
        public function whereIn(string $where, array $ids, string $type): self { $this->ids = $ids; return $this; }
        public function bind(string $key, mixed $value, string $type): self {
            $this->bindings[$key] = $value;
            return $this;
        }
        public function order(string $what): self { return $this; }
    }
    class StubDatabase implements \Joomla\Database\DatabaseInterface {
        public array $limits = [];
        public int $queryCount = 0;
        public int $inserts = 0;
        public bool $mailLookupThrows = true;
        public bool $mailUserAvailable = false;
        public int $adminEmailCount = 150;
        public int $lastInsertId = 45;
        public function createQuery(): QueryStub { ++$this->queryCount; return new QueryStub(); }
        public function quoteName(string $name, string $alias = ''): string { return $name; }
        public function setQuery(QueryStub $query, int $offset = 0, int $limit = 0): void {
            $this->limits[] = $limit;
        }
        public function loadResult(): int { return 0; }
        public function loadColumn(): array {
            return array_merge(
                array_fill(0, 5, 'duplicate@example.org'),
                array_map(static fn (int $i): string => "person$i@example.org", range(1, $this->adminEmailCount)),
                ['malformed-email', '']
            );
        }
        public function loadObject(): ?object {
            if ($this->mailLookupThrows) { throw new \RuntimeException('Simulated mail query outage'); }
            return $this->mailUserAvailable ? (object) ['email' => 'member@example.org', 'name' => 'Member'] : null;
        }
        public function loadObjectList(): array { return []; }
        public function insertObject(string $table, object $member): bool { ++$this->inserts; return true; }
        public function insertid(): int { return $this->lastInsertId; }
    }

    $GLOBALS['testMembershipDb'] = new StubDatabase();
    $GLOBALS['testMembershipApp'] = new class {
        public function getIdentity(): object { return (object) ['id' => 21]; }
        public function getConfig(): \Joomla\Registry\Registry { return new \Joomla\Registry\Registry(); }
    };
    $member = new \Diddipoeler\Component\SportsManagement\Site\Model\PredictionmembershipModel();
    // Notification failure after a successful INSERT must not invalidate signup.
    if ($member->registerCurrentUser() !== 45 || $GLOBALS['testMembershipDb']->inserts !== 1) {
        throw new \RuntimeException('Mail-query failure incorrectly aborted a successful member registration.');
    }

    $GLOBALS['testMembershipDb']->mailLookupThrows = false;
    $GLOBALS['testMembershipDb']->mailUserAvailable = true;
    \Joomla\CMS\Factory::$mailer = new class {
        public array $bcc = [];
        public function setSender(array $address): void {}
        public function addRecipient(string $address): void {}
        public function addBcc(array $addresses): void { $this->bcc = $addresses; }
        public function setSubject(string $value): void {}
        public function setBody(string $value): void {}
        public function send(): bool { return true; }
    };
    $method = new \ReflectionMethod($member, 'sendMembershipConfirmation');
    if ($method->invoke($member, 45, 21) !== true
        || count(\Joomla\CMS\Factory::$mailer->bcc) > 101
        || count(array_unique(\Joomla\CMS\Factory::$mailer->bcc)) !== count(\Joomla\CMS\Factory::$mailer->bcc)
        || in_array('malformed-email', \Joomla\CMS\Factory::$mailer->bcc, true)) {
        throw new \RuntimeException('Prediction confirmation allowed invalid or excessive BCC recipients.');
    }

    $GLOBALS['testImportDb'] = new StubDatabase();
    $importer = new \Diddipoeler\Component\SportsManagement\Administrator\Model\JlextdfbkeyimportModel();
    foreach (['12junk', [], -1, '999999999999999999999999', true, '1:alias'] as $bad) {
        $calls = $GLOBALS['testImportDb']->queryCount;
        if ($importer->getProjectType($bad) !== false
            || $importer->getProjectteams($bad) !== []
            || $importer->getMatchdays($bad) !== []
            || $importer->getMatches($bad) !== 0
            || $GLOBALS['testImportDb']->queryCount !== $calls) {
            throw new \RuntimeException('Malformed DFB project identifier was cast to a valid project.');
        }
    }
    $calls = $GLOBALS['testImportDb']->queryCount;
    if ($importer->getProjectteams(12, '9bad') !== []
        || $GLOBALS['testImportDb']->queryCount !== $calls) {
        throw new \RuntimeException('Invalid DFB division unexpectedly queried unfiltered teams.');
    }
    $importer->getProjectteams('12', '3');
    if ($GLOBALS['testImportDb']->limits[array_key_last($GLOBALS['testImportDb']->limits)] !== 1000) {
        throw new \RuntimeException('DFB importer team query was unbounded.');
    }

    $map = new \Diddipoeler\Module\SportsManagementProjectMap\Site\Helper\ProjectMapHelper();
    $ids = new \ReflectionMethod($map, 'normaliseSeasonIds');
    if ($ids->invoke($map, [implode(',', range(1, 100))]) !== range(1, 50)
        || $ids->invoke($map, [str_repeat('1', 4200), 8]) !== [8]) {
        throw new \RuntimeException('Project-map filter IDs were not bounded.');
    }
    $db = new StubDatabase();
    $map->getData([1, 2], $db);
    if ($db->limits !== [5000]) {
        throw new \RuntimeException('Project map did not bound its intermediate query rows.');
    }

    echo "Joomla 5/6 membership, DFB importer and project-map guards OK\n";
}
