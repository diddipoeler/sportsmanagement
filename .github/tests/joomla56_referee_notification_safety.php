<?php
/**
 * Joomla 5/6 regression tests for safe referee mail notifications.
 *
 * Usage: php .github/tests/joomla56_referee_notification_safety.php
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

namespace Joomla\CMS\Mail {
    interface MailerFactoryInterface {}
}

namespace Joomla\CMS\Component {
    class ComponentHelper
    {
        public static function getParams(string $component): object
        {
            return new class {
                public function get(string $name, mixed $default = ''): mixed
                {
                    return 'Hello %s %s, referee duty at %s';
                }
            };
        }
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Log {
    class Log
    {
        public const WARNING = 'warning';
        public static array $entries = [];
        public static function add(string $message, string $severity, string $category): void
        {
            self::$entries[] = $message;
        }
    }
}

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType { public const INTEGER = 'integer'; }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Service/MatchRefereeNotificationService.php';

    final class NotificationQuery
    {
        public array $joins = [];
        public array $where = [];
        public array $binds = [];
        public function select(mixed $fields): self { return $this; }
        public function from(string $name): self { return $this; }
        public function join(string $type, string $expression): self
        {
            $this->joins[] = $expression;
            return $this;
        }
        public function where(string $expression): self
        {
            $this->where[] = $expression;
            return $this;
        }
        public function bind(string $name, mixed $value, string $type): self
        {
            $this->binds[$name] = $value;
            return $this;
        }
    }

    final class NotificationDatabase implements \Joomla\Database\DatabaseInterface
    {
        public int $loads = 0;
        public bool $throwOnLookup = false;
        public ?NotificationQuery $last = null;
        public function createQuery(): NotificationQuery { return new NotificationQuery(); }
        public function quoteName(string $name, string $alias = ''): string
        {
            return $name;
        }
        public function setQuery(NotificationQuery $query, int $offset = 0, int $limit = 0): void
        {
            $this->last = $query;
        }
        public function loadObject(): ?object
        {
            ++$this->loads;

            if ($this->throwOnLookup) {
                throw new \RuntimeException('Sensitive database connection detail');
            }

            return (object) [
                'email' => 'referee@example.test',
                'firstname' => 'Jana',
                'lastname' => 'Muster',
                'match_timestamp' => 1781172000,
                'match_date' => '2026-06-11 14:00:00',
                'playground_name' => 'Test ground',
                'team1' => 'Home',
                'team2' => 'Away',
            ];
        }
    }

    final class NotificationApplication implements \Joomla\CMS\Application\CMSApplicationInterface
    {
        public array $messages = [];
        public function get(string $name, mixed $default = null): mixed
        {
            return match ($name) {
                'mailfrom' => 'sender@example.test',
                'fromname' => 'SportsManagement',
                default => $default,
            };
        }
        public function enqueueMessage(string $text, string $type): void
        {
            $this->messages[] = [$text, $type];
        }
    }

    final class NotificationMailer
    {
        public function __construct(private NotificationMailFactory $factory) {}
        public function setSender(array $values): void {}
        public function addRecipient(string $recipient): void
        {
            if ($recipient !== 'referee@example.test') {
                throw new \RuntimeException('Unexpected recipient.');
            }
        }
        public function setSubject(string $value): void {}
        public function isHTML(bool $value): void {}
        public function setBody(string $value): void {}
        public function send(): bool
        {
            ++$this->factory->sent;
            if ($this->factory->failSend) {
                throw new \RuntimeException('Private SMTP secret must not reach browser');
            }

            return true;
        }
    }

    final class NotificationMailFactory implements \Joomla\CMS\Mail\MailerFactoryInterface
    {
        public int $sent = 0;
        public bool $failSend = false;
        public function createMailer(): NotificationMailer
        {
            return new NotificationMailer($this);
        }
    }

    $db = new NotificationDatabase();
    $mail = new NotificationMailFactory();
    $app = new NotificationApplication();
    $service = new \Diddipoeler\Component\SportsManagement\Administrator\Service\MatchRefereeNotificationService(
        $db,
        $mail,
        $app
    );

    $service->notifyNewAssignments(71, [
        ['project_referee_id' => 8, 'project_position_id' => 1],
        ['project_referee_id' => 8, 'project_position_id' => 2],
        ['project_referee_id' => 10, 'project_position_id' => 1],
    ]);

    if ($db->loads !== 2 || $mail->sent !== 2
        || ($db->last?->binds[':matchId'] ?? null) !== 71
        || ($db->last?->binds[':projectRefereeId'] ?? null) !== 10
        || !str_contains(implode(' ', $db->last?->joins ?? []), 'mr.project_referee_id = :projectRefereeId')
        || !in_array('m.id = :matchId', $db->last?->where ?? [], true)) {
        throw new \RuntimeException('Referee notifications must bind IDs and deduplicate recipients.');
    }

    $service->notifyNewAssignments(0, [['project_referee_id' => 20]]);
    $service->notifyNewAssignments(71, [['project_referee_id' => 0]]);
    if ($db->loads !== 2 || $mail->sent !== 2) {
        throw new \RuntimeException('Invalid referee or match IDs caused mail/database access.');
    }

    $db->throwOnLookup = true;
    $service->notifyNewAssignments(71, [['project_referee_id' => 12]]);
    if ($mail->sent !== 2 || count(\Joomla\CMS\Log\Log::$entries) !== 1) {
        throw new \RuntimeException('Referee lookup failures must be logged without sending.');
    }

    $db->throwOnLookup = false;
    $mail->failSend = true;
    $service->notifyNewAssignments(71, [['project_referee_id' => 12]]);
    if (count($app->messages) !== 1
        || $app->messages[0] !== ['JERROR_AN_ERROR_HAS_OCCURRED', 'warning']
        || $mail->sent !== 3
        || count(\Joomla\CMS\Log\Log::$entries) !== 2) {
        throw new \RuntimeException('Mailer exceptions must be logged but displayed generically.');
    }

    echo "Joomla 5/6 referee mail notifications and bound IDs OK\n";
}
