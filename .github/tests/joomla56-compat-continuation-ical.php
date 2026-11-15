<?php
/**
 * Check the iCal raw view's typed Joomla 5/6 database dependency.
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

namespace Joomla\CMS {
    final class Factory
    {
        public static object $service;

        /** Expose the configured public dependency container to the raw view. */
        public static function getContainer(): object
        {
            return new class {
                /** Return the test service for the named Joomla interface. */
                public function get(string $name): object { return \Joomla\CMS\Factory::$service; }
            };
        }
    }
}

namespace Joomla\CMS\MVC\View {
    class HtmlView
    {
        public mixed $attachedModel = null;
        public bool $rendered = false;

        /** Record which Joomla model was attached to this view. */
        public function setModel(object $model, bool $default = false): void
        {
            $this->attachedModel = $model;
        }

        /** Record the template render request without any Joomla bootstrap. */
        public function display($tpl = null): void
        {
            $this->rendered = true;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    final class EventModel
    {
        public ?\Joomla\Database\DatabaseInterface $database = null;

        /** Store the DI-provided database instead of resolving a legacy global. */
        public function setDatabase(\Joomla\Database\DatabaseInterface $database): void
        {
            $this->database = $database;
        }

        /** Return an authorised event fixture after database dependency setup. */
        public function getGCalendar(): ?array
        {
            return ['event' => 'sample'];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/View/Ical/RawView.php';

    \Joomla\CMS\Factory::$service = new class implements \Joomla\Database\DatabaseInterface {};
    $view = new \Diddipoeler\Component\SportsManagement\Site\View\Ical\RawView();
    $view->display();

    if (!$view->rendered || $view->event !== ['event' => 'sample']
        || !$view->attachedModel instanceof \Diddipoeler\Component\SportsManagement\Site\Model\EventModel
        || $view->attachedModel->database !== \Joomla\CMS\Factory::$service) {
        throw new \RuntimeException('iCal raw view lost its validated EventModel database binding.');
    }

    \Joomla\CMS\Factory::$service = new \stdClass();
    $invalidView = new \Diddipoeler\Component\SportsManagement\Site\View\Ical\RawView();

    try {
        $invalidView->display();
        throw new \RuntimeException('iCal view accepted an invalid Joomla database service.');
    } catch (\RuntimeException $error) {
        if ($error->getCode() !== 500 || !str_contains($error->getMessage(), 'database service')) {
            throw $error;
        }
    }

    if ($invalidView->attachedModel !== null || $invalidView->rendered) {
        throw new \RuntimeException('Invalid iCal database rendered a partially initialised view.');
    }

    echo "Site iCal raw-view database injection regression passed.\n";
}
