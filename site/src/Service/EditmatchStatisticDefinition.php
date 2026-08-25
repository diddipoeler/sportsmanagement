<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Lightweight statistic definition used by the frontend match statistics form.
 *
 * The historical editstats templates only require the public identity fields,
 * getCalculated() and getImage(); keeping that contract here avoids loading the
 * legacy administrator statistic framework for a simple input form.
 */
final class EditmatchStatisticDefinition
{
    private static array $calculatedByClass = [];

    public int $id;
    public string $name;
    public string $short;
    public string $icon;
    public int $position_id;
    private int $calculated;

    public function __construct(object $row)
    {
        $this->id = (int) ($row->id ?? 0);
        $this->name = (string) ($row->name ?? '');
        $this->short = (string) ($row->short ?? '');
        $this->icon = (string) ($row->icon ?? '');
        $this->position_id = (int) ($row->posid ?? $row->position_id ?? 0);
        $this->calculated = self::resolveCalculated(
            (string) ($row->class ?? ''),
            (int) ($row->calculated ?? 0)
        );
    }

    public function getCalculated(): int
    {
        return $this->calculated;
    }

    public function getImage(int $eventsPictureHeight = 30): string
    {
        if ($this->icon !== '') {
            $iconPath = $this->icon;

            if (!str_contains($iconPath, '/')) {
                $iconPath = 'images/com_sportsmanagement/database/statistics/' . $iconPath;
            }

            return HTMLHelper::_(
                'image',
                $iconPath,
                Text::_($this->name),
                [
                    'title' => Text::_($this->name),
                    'style' => 'width: auto;height: ' . $eventsPictureHeight . 'px',
                ]
            );
        }

        return '<span class="stat-alternate hasTip" title="'
            . htmlspecialchars(Text::_($this->name), ENT_QUOTES, 'UTF-8')
            . '">'
            . htmlspecialchars(Text::_($this->short), ENT_QUOTES, 'UTF-8')
            . '</span>';
    }

    private static function resolveCalculated(string $class, int $fallback): int
    {
        $class = basename(trim($class));

        if ($class === '') {
            return $fallback;
        }

        if (array_key_exists($class, self::$calculatedByClass)) {
            return self::$calculatedByClass[$class];
        }

        $file = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/statistics/' . $class . '.php';

        if (!is_file($file)) {
            return self::$calculatedByClass[$class] = $fallback;
        }

        $source = file_get_contents($file);

        if ($source !== false && preg_match('/\$_calculated\s*=\s*([01])\s*;/', $source, $matches)) {
            return self::$calculatedByClass[$class] = (int) $matches[1];
        }

        return self::$calculatedByClass[$class] = $fallback;
    }
}
