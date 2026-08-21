<?php
namespace Diddipoeler\Plugin\Content\SportsmanagementComments\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

/** Joomla 5/6 content plugin bridge for SportsManagement JComments integration. */
final class SportsmanagementComments extends CMSPlugin
{
    protected $autoloadLanguage = true;

    public function onMatchReportComments(&$match, $title, &$html)
    {
        if ((int) $this->params->get('separate_comments', 0) !== 1) {
            return null;
        }

        return $this->renderComments($match, 'com_sportsmanagement_matchreport', $title, $html);
    }

    public function onNextMatchComments(&$match, $title, &$html)
    {
        if ((int) $this->params->get('separate_comments', 0) !== 1) {
            return null;
        }

        return $this->renderComments($match, 'com_sportsmanagement_nextmatch', $title, $html);
    }

    public function onMatchComments(&$match, $title, &$html)
    {
        if ((int) $this->params->get('separate_comments', 0) !== 0) {
            return null;
        }

        return $this->renderComments($match, 'com_sportsmanagement', $title, $html);
    }

    private function renderComments(&$match, string $context, $title, &$html): bool
    {
        $comments = JPATH_SITE . '/components/com_jcomments/jcomments.php';

        if (!is_file($comments)) {
            return false;
        }

        include_once $comments;

        if (!class_exists('JComments')) {
            return false;
        }

        $html = '<div>' . \JComments::show((int) $match->id, $context, (string) $title) . '</div>';

        return true;
    }
}
