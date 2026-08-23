<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Builds translated country select options without loading the legacy JSMCountries helper.
 */
final class CountryOptionsHelper
{
    public static function getOptions(DatabaseInterface $db): array
    {
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('alpha3'),
                $db->quoteName('name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_countries'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadAssocList() ?: [] as $country) {
            $options[] = HTMLHelper::_(
                'select.option',
                (string) $country['alpha3'],
                Text::_((string) $country['name'])
            );
        }

        usort(
            $options,
            static fn ($left, $right): int => strnatcasecmp((string) $left->text, (string) $right->text)
        );

        return $options;
    }
}
