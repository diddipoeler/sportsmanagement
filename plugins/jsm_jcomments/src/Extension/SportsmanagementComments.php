<?php
namespace Diddipoeler\Plugin\Content\SportsmanagementComments\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/** Joomla 5/6 content plugin bridge for SportsManagement JComments integration. */
final class SportsmanagementComments extends CMSPlugin implements SubscriberInterface
{
    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onMatchReportComments' => 'onMatchReportComments',
            'onNextMatchComments' => 'onNextMatchComments',
            'onMatchComments' => 'onMatchComments',
        ];
    }

    public function onMatchReportComments(Event $event): void
    {
        $this->handleCommentsEvent(
            $event,
            1,
            'com_sportsmanagement_matchreport'
        );
    }

    public function onNextMatchComments(Event $event): void
    {
        $this->handleCommentsEvent(
            $event,
            1,
            'com_sportsmanagement_nextmatch'
        );
    }

    public function onMatchComments(Event $event): void
    {
        $this->handleCommentsEvent(
            $event,
            0,
            'com_sportsmanagement'
        );
    }

    private function handleCommentsEvent(Event $event, int $separateComments, string $context): void
    {
        $arguments = $event->getArguments();
        $keys = array_keys($arguments);
        $values = array_values($arguments);
        $match = $values[0] ?? null;
        $title = (string) ($values[1] ?? '');
        $html = (string) ($values[2] ?? '');

        if ((int) $this->params->get('separate_comments', 0) !== $separateComments) {
            $this->addEventResult($event, null);
            return;
        }

        $result = is_object($match)
            ? $this->renderComments($match, $context, $title, $html)
            : false;

        // These SportsManagement custom events historically pass the HTML
        // argument by reference. Writing the third event argument keeps that
        // contract while using Joomla's subscriber-based event API.
        if (isset($keys[2])) {
            $event->setArgument($keys[2], $html);
        }

        $this->addEventResult($event, $result);
    }

    private function renderComments(object $match, string $context, string $title, string &$html): bool
    {
        $comments = JPATH_SITE . '/components/com_jcomments/jcomments.php';

        if (!is_file($comments)) {
            return false;
        }

        include_once $comments;

        if (!class_exists('JComments')) {
            return false;
        }

        $html = '<div>' . \JComments::show((int) ($match->id ?? 0), $context, $title) . '</div>';

        return true;
    }

    private function addEventResult(Event $event, mixed $value): void
    {
        if ($event instanceof ResultAwareInterface) {
            $event->addResult($value);
            return;
        }

        $results = $event->getArgument('result') ?: [];
        $results = is_array($results) ? $results : [$results];
        $results[] = $value;
        $event->setArgument('result', $results);
    }
}
