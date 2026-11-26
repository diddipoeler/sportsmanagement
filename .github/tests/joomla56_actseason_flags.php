<?php
/**
 * Regression for Active Season flag sources and placeholders.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Registry {
    final class Registry {
        /** Store module settings. @param array $values Settings fixture. */
        public function __construct(private array $values) {}
        /** Return the selected setting. @param string $name Setting. @param mixed $default Fallback. @return mixed Value. */
        public function get(string $name, mixed $default = null): mixed { return $this->values[$name] ?? $default; }
    }
}
namespace Joomla\CMS\Uri {
    final class Uri {
        /** Provide the fixture Joomla root. @return string Local site URL. */
        public static function root(): string { return 'https://example.org/'; }
    }
}
namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_act_season/src/Helper/ActSeasonHelper.php';
    $helper = new \Diddipoeler\Module\SportsManagementActSeason\Site\Helper\ActSeasonHelper();
    $source = new \ReflectionMethod($helper, 'flagSource');
    foreach (['../x.png', 'images/%252e%252e/x.png', '%2f%2fevil.example/a.png',
        'https://user:password@example.org/x.png', 'javascript:alert(1)', 'images/%2500.png'] as $bad) {
        if ($source->invoke($helper, $bad) !== '') {
            throw new \RuntimeException('Unsafe flag URL accepted.');
        }
    }
    $render = new \ReflectionMethod($helper, 'flagHtml');
    $row = (object) ['country' => 'DEU', 'alpha2' => '', 'country_picture' => '', 'country_label' => 'A & B'];
    $params = new \Joomla\Registry\Registry(['cfg_flags_css' => ['1'], 'ph_flags' => 'images/placeholder.png']);
    $html = $render->invoke($helper, $row, $params);
    if (!str_contains($html, 'src="https://example.org/images/placeholder.png"')
        || !str_contains($html, 'alt="A &amp; B"')) {
        throw new \RuntimeException('Valid flag placeholder or escaped label was lost.');
    }
    echo "Active Season flag guards OK\n";
}
