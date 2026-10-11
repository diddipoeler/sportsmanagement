<?php
/**
 * Joomla 5/6 prediction-game approval and notification scope regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    final class ParameterType
    {
        public const INTEGER = 'integer';
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
        public const ERROR = 400;
        public static array $records = [];
        public static function add(string $message, int $level, string $category): void
        {
            self::$records[] = $message;
        }
    }
}
namespace Joomla\CMS\Mail {
    interface MailerFactoryInterface
    {
        public function createMailer(): object;
    }
}
namespace Joomla\CMS {
    class Factory
    {
        public static function getContainer(): object
        {
            return new class {
                public function get(string $key): object
                {
                    return match ($key) {
                        'config' => new class {
                            public function get(string $key): string
                            {
                                return $key === 'mailfrom' ? 'joomla@example.org' : 'Sportsmanagement';
                            }
                        },
                        \Joomla\CMS\Mail\MailerFactoryInterface::class => new \TestMailerFactory(),
                        default => throw new \RuntimeException('Unknown container entry: ' . $key),
                    };
                }
            };
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel
    {
        public static mixed $db = null;
        public static mixed $app = null;
        public array $errors = [];
        public function getDatabase(): object { return self::$db; }
        protected function administratorApplication(): object { return self::$app; }
        public function setError(string $message): void { $this->errors[] = $message; }
    }
}
namespace {
    define('_JEXEC', 1);

    class QueryStub
    {
        public string $fromTable = '';
        public string $mode = 'select';
        public array $where = [];
        public array $whereIn = [];
        public array $bind = [];
        public function select(mixed $value): self { $this->mode = 'select'; return $this; }
        public function from(string $table): self { $this->fromTable = $table; return $this; }
        public function join(string $kind, string $fragment): self { return $this; }
        public function update(string $table): self { $this->mode = 'update'; $this->fromTable = $table; return $this; }
        public function set(string $sql): self { return $this; }
        public function where(string $sql): self { $this->where[] = $sql; return $this; }
        public function whereIn(string $column, array $ids, mixed $type): self
        {
            $this->whereIn[$column] = $ids;
            return $this;
        }
        public function bind(string $key, mixed $value, mixed $type = ''): self
        {
            $this->bind[$key] = $value;
            return $this;
        }
    }
    class DbStub
    {
        public array $members = [
            1 => ['prediction_id' => 23, 'approved' => 0, 'checked_out' => 0, 'user_id' => 101],
            2 => ['prediction_id' => 23, 'approved' => 0, 'checked_out' => 99, 'user_id' => 102],
            3 => ['prediction_id' => 23, 'approved' => 1, 'checked_out' => 0, 'user_id' => 103],
            4 => ['prediction_id' => 24, 'approved' => 0, 'checked_out' => 0, 'user_id' => 104],
            5 => ['prediction_id' => 23, 'approved' => 0, 'checked_out' => 7, 'user_id' => 105],
        ];
        public array $updates = [];
        private ?QueryStub $query = null;
        public function createQuery(): QueryStub { return new QueryStub(); }
        public function quoteName(string $name, string $alias = ''): string { return $name; }
        public function quote(string $text): string { return "'" . $text . "'"; }
        public function setQuery(QueryStub $q, int $offset = 0, int $limit = 0): self
        {
            $this->query = $q;
            return $this;
        }
        public function loadColumn(): array
        {
            $q = $this->query;
            if ($q->fromTable !== '#__sportsmanagement_prediction_member') {
                return [];
            }
            $selected = $q->whereIn['id'] ?? [];
            if (isset($q->bind[':selectedPredictionId'])) {
                return array_values(array_filter($selected, fn (int $id): bool =>
                    isset($this->members[$id])
                    && $this->members[$id]['prediction_id'] === $q->bind[':selectedPredictionId']));
            }
            if (isset($q->bind[':eligiblePredictionId'])) {
                return array_values(array_filter($selected, fn (int $id): bool =>
                    isset($this->members[$id])
                    && $this->members[$id]['prediction_id'] === $q->bind[':eligiblePredictionId']
                    && ($this->members[$id]['checked_out'] === 0
                        || $this->members[$id]['checked_out'] === $q->bind[':eligibleUserId'])
                    && $this->members[$id]['approved'] !== $q->bind[':eligibleState']));
            }
            throw new \RuntimeException('Unexpected prediction member selection');
        }
        public function loadObject(): ?object
        {
            $q = $this->query;
            if (!isset($q->bind[':contactMemberId'])) {
                return null;
            }
            $id = $q->bind[':contactMemberId'];
            return isset($this->members[$id]) ? (object) [
                'user_id' => $this->members[$id]['user_id'],
                'email' => 'member' . $id . '@example.org',
                'username' => 'member-' . $id,
            ] : null;
        }
        public function execute(): void
        {
            $q = $this->query;
            if ($q->mode !== 'update') {
                throw new \RuntimeException('Unexpected query write');
            }
            if (!isset($q->bind[':previousApprovalState'])) {
                throw new \RuntimeException('Approval update did not guard previous state');
            }
            $ids = $q->whereIn['id'] ?? [];
            foreach ($ids as $id) {
                if (!isset($this->members[$id])
                    || $this->members[$id]['prediction_id'] !== $q->bind[':publishPredictionId']
                    || ($this->members[$id]['checked_out'] !== 0
                        && $this->members[$id]['checked_out'] !== $q->bind[':publishUserId'])
                    || $this->members[$id]['approved'] === $q->bind[':previousApprovalState']) {
                    continue;
                }
                $this->members[$id]['approved'] = $q->bind[':approved'];
                $this->updates[] = $id;
            }
        }
    }
    class TestMailerFactory implements \Joomla\CMS\Mail\MailerFactoryInterface
    {
        public static array $sent = [];
        public static bool $fail = false;

        public function createMailer(): object
        {
            return new class {
                private array $recipients = [];
                public function setSender(string $email, string $name): void {}
                public function addRecipient(string $email): void { $this->recipients[] = $email; }
                public function addBcc(string $email): void {}
                public function setSubject(string $subject): void {}
                public function setBody(string $body): void {}
                public function send(): bool
                {
                    if (TestMailerFactory::$fail) {
                        throw new \RuntimeException('SMTP password secret-string');
                    }
                    TestMailerFactory::$sent[] = $this->recipients;
                    return true;
                }
            };
        }
    }
    class AppStub
    {
        public array $messages = [];
        public function getIdentity(): object { return (object) ['id' => 7]; }
        public function enqueueMessage(string $message, string $type): void
        {
            $this->messages[] = compact('message', 'type');
        }
    }

    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Model/PredictionmemberModel.php';
    $db = new DbStub();
    $app = new AppStub();
    $base = \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::class;
    $base::$db = $db;
    $base::$app = $app;

    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionmemberModel();
    if (!$model->publishpredmembers([1, 2, 3, 4, 5], 1, 23)) {
        throw new \RuntimeException('Valid approval operation did not complete');
    }
    if ($db->updates !== [1, 5] || TestMailerFactory::$sent !== [
        ['member1@example.org'], ['member5@example.org'],
    ]) {
        throw new \RuntimeException('Approval notified locked, unchanged or foreign-game members.');
    }
    if (!$model->publishpredmembers([1, 2, 3, 4, 5], 1, 23)
        || count(TestMailerFactory::$sent) !== 2 || $db->updates !== [1, 5]) {
        throw new \RuntimeException('Idempotent approval repeated changes or email notifications.');
    }
    if ($model->publishpredmembers([4], 1, 23)) {
        throw new \RuntimeException('Member from another game was accepted.');
    }

    TestMailerFactory::$fail = true;
    if (!$model->publishpredmembers([5], 0, 23) || $db->members[5]['approved'] !== 0) {
        throw new \RuntimeException('Unpublish with failed notification lost database update.');
    }
    if (($app->messages[0]['message'] ?? null) !== 'JERROR_AN_ERROR_HAS_OCCURRED'
        || ($app->messages[0]['type'] ?? null) !== 'error') {
        throw new \RuntimeException('Mailer failure was not reported safely to Joomla.');
    }
    foreach (\Joomla\CMS\Log\Log::$records as $entry) {
        if (str_contains($entry, 'secret-string')) {
            throw new \RuntimeException('Mailer exception leaked credentials to logs.');
        }
    }

    echo "Joomla 5/6 prediction approval game/check-out scoping, idempotency and mail safety OK\n";
}
