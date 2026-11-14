<?php
/**
 * Joomla 5/6 SportsManagement site helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Render Clubinfo fusion/history relations without legacy route or tree helpers.
 */
final class ClubHistoryPresentationHelper
{
    /**
     * Render the known predecessor clubs as a nested, route-aware HTML tree.
     *
     * Groups historical club relations by successor, then walks backward from
     * the selected club. The recursion tracks visited IDs to avoid cycles;
     * names and years are escaped before they enter the frontend markup.
     *
     * @param array<int,mixed> $relations Club records with id and new_club_id.
     * @param int $clubId Current club whose predecessors should be shown.
     * @param int $treeMode Legacy switch controlling collapsible tree buttons.
     * @param int $databaseSelector Selected SportsManagement database.
     * @return string Nested HTML list or an empty string for no predecessors.
     */
    public static function renderPredecessorTree(
        array $relations,
        int $clubId,
        int $treeMode,
        int $databaseSelector
    ): string {
        $children = self::childrenBySuccessor($relations);

        return self::renderChildren($children, $clubId, $treeMode, $databaseSelector, []);
    }

    /**
     * Show a club's successor together with its historical predecessor chain.
     *
     * Returns no markup when the club has no successor; otherwise creates the
     * translated successor link and a nested predecessor list. Club names are
     * escaped and the URL is built by the native SportsManagement route helper.
     *
     * @param array<int,mixed> $relations Historical predecessor relations.
     * @param int $clubId Club at the start of the displayed history.
     * @param object|null $successor Successor club record, when available.
     * @param int $databaseSelector Database used to build club links.
     * @return string Accessible successor/history list or an empty string.
     */
    public static function renderSuccessorHistory(
        array $relations,
        int $clubId,
        ?object $successor,
        int $databaseSelector
    ): string {
        if (!$successor) {
            return '';
        }

        $link = self::clubLink($successor, $databaseSelector);
        $title = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_HISTORY_FROM');
        $predecessors = self::renderHistoryList(
            self::childrenBySuccessor($relations),
            $clubId,
            $databaseSelector,
            []
        );

        return '<ul><li>'
            . HTMLHelper::image(
                'media/com_sportsmanagement/jl_images/club_from.png',
                $title,
                ['title' => $title]
            )
            . '&nbsp;' . HTMLHelper::link($link, self::escape((string) ($successor->name ?? '')))
            . ($predecessors !== '' ? '<ul>' . $predecessors . '</ul>' : '')
            . '</li></ul>';
    }

    /**
     * Index each valid predecessor beneath the club that succeeded it.
     *
     * Ignores malformed records, nonpositive IDs and self-referential links,
     * then sorts each sibling group naturally by the club's display name.
     *
     * @param array<int,mixed> $relations Club records from the history reader.
     * @return array<int,array<int,object>> Predecessors keyed by successor ID.
     */
    private static function childrenBySuccessor(array $relations): array
    {
        $children = [];
        foreach ($relations as $club) {
            if (!is_object($club)) {
                continue;
            }
            $successorId = (int) ($club->new_club_id ?? 0);
            $clubId = (int) ($club->id ?? 0);
            if ($successorId > 0 && $clubId > 0 && $successorId !== $clubId) {
                $children[$successorId][] = $club;
            }
        }

        foreach ($children as &$clubs) {
            usort(
                $clubs,
                static fn (object $a, object $b): int => strnatcasecmp(
                    (string) ($a->name ?? ''),
                    (string) ($b->name ?? '')
                )
            );
        }
        unset($clubs);

        return $children;
    }

    /**
     * Recursively render one level of the collapsible predecessor club tree.
     *
     * Stops at invalid, missing or previously visited club IDs so corrupted
     * relation cycles cannot recurse forever. Displays optional founding years,
     * club logos and a native club link; descendants stay inside their parent
     * list item so generated markup remains correctly nested.
     *
     * @param array<int,array<int,object>> $children Predecessors grouped by successor.
     * @param int $parentId Club whose immediate predecessors are displayed.
     * @param int $treeMode Whether expand/collapse controls are enabled.
     * @param int $databaseSelector Database used by the generated club URLs.
     * @param array<int,bool> $seen Club IDs already visited in this branch.
     * @return string HTML unordered list, or an empty string without children.
     */
    private static function renderChildren(
        array $children,
        int $parentId,
        int $treeMode,
        int $databaseSelector,
        array $seen
    ): string {
        if ($parentId <= 0 || isset($seen[$parentId]) || empty($children[$parentId])) {
            return '';
        }
        $seen[$parentId] = true;

        $output = '<ul>';
        foreach ($children[$parentId] as $club) {
            $id = (int) ($club->id ?? 0);
            if ($id <= 0 || isset($seen[$id])) {
                continue;
            }

            $name = trim((string) ($club->name ?? ''));
            $year = trim((string) ($club->founded_year ?? ''));
            $label = $year !== '' ? $name . ' (' . $year . ')' : $name;
            $toggle = $treeMode === 0 && !empty($children[$id])
                ? '<button type="button" class="btn btn-sm p-0 border-0 bg-transparent"'
                    . ' data-jsm-clubinfo-tree-toggle aria-expanded="true">'
                    . '<i class="icon-minus-sign" aria-hidden="true"></i>'
                    . HTMLHelper::image('media/com_sportsmanagement/jl_images/arrow_left.png', '')
                    . '<span class="visually-hidden">' . self::escape($name) . '</span>'
                    . '</button>'
                : '';
            $logo = trim((string) ($club->logo_big ?? ''));
            $image = $logo !== ''
                ? HTMLHelper::image($logo, $name, ['width' => 30]) . ' '
                : '';

            $output .= '<li>' . $toggle
                . '<span><a href="' . self::escape(self::clubLink($club, $databaseSelector)) . '">'
                . $image . self::escape($label)
                . '</a></span>'
                . self::renderChildren($children, $id, $treeMode, $databaseSelector, $seen)
                . '</li>';
        }

        return $output . '</ul>';
    }

    /**
     * Build the successor panel's recursive list of predecessor links.
     *
     * Each descendant list is kept inside its corresponding parent list item,
     * producing valid nested HTML. Tracks visited clubs independently along
     * each recursion branch and uses the native route helper for every link.
     *
     * @param array<int,array<int,object>> $children Indexed history relations.
     * @param int $parentId Successor ID from which to trace the predecessors.
     * @param int $databaseSelector SportsManagement database selector.
     * @param array<int,bool> $seen IDs visited on the current traversal path.
     * @return string HTML list items (without a surrounding outer list).
     */
    private static function renderHistoryList(
        array $children,
        int $parentId,
        int $databaseSelector,
        array $seen
    ): string {
        if ($parentId <= 0 || isset($seen[$parentId]) || empty($children[$parentId])) {
            return '';
        }
        $seen[$parentId] = true;
        $output = '';
        $title = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_HISTORY_FROM');

        foreach ($children[$parentId] as $club) {
            $id = (int) ($club->id ?? 0);
            if ($id <= 0 || isset($seen[$id])) {
                continue;
            }

            $output .= '<li>'
                . HTMLHelper::image(
                    'media/com_sportsmanagement/jl_images/club_from.png',
                    $title,
                    ['title' => $title]
                )
                . '&nbsp;' . HTMLHelper::link(
                    self::clubLink($club, $databaseSelector),
                    self::escape((string) ($club->name ?? ''))
                )
                ;
            $nested = self::renderHistoryList($children, $id, $databaseSelector, $seen);
            if ($nested !== '') {
                $output .= '<ul>' . $nested . '</ul>';
            }
            $output .= '</li>';
        }

        return $output;
    }

    /**
     * Generate a native frontend club-info link for a history relation.
     *
     * Retains the historical project and club slug fields and adds the
     * requested database selector without depending on legacy URL globals.
     *
     * @param object $club Historical club record with optional slugs.
     * @param int $databaseSelector Selected component database.
     * @return string Joomla-routed club information URL.
     */
    private static function clubLink(object $club, int $databaseSelector): string
    {
        return SiteRouteHelper::view('clubinfo', [
            'cfg_which_database' => $databaseSelector,
            'p' => (string) ($club->project_slug ?? '0'),
            'cid' => (string) ($club->slug ?? $club->id ?? 0),
        ]);
    }

    /**
     * Encode club-provided text before inserting it into HTML markup.
     *
     * Encodes quotes as well as angle brackets using UTF-8, preventing names,
     * years and generated link attributes from becoming executable HTML.
     *
     * @param string $value Untrusted text or route attribute.
     * @return string HTML-escaped value.
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
