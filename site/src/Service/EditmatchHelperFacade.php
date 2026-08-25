<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;

/** Narrow compatibility helper for unchanged editmatch templates. */
final class EditmatchHelperFacade
{
    public static function formatName($prefix, $firstName, $nickName, $lastName, $format = 0): string
    {
        return PersonNameFormatter::format(
            $prefix !== null ? (string) $prefix : null,
            (string) $firstName,
            (string) $nickName,
            (string) $lastName,
            $format
        );
    }
}
