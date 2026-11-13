<?php
/**
 * Verify the Joomla 5/6 administrator GitHub update dashboard entry.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);

/**
 * Assert a required feature contract without bootstrapping the Joomla CMS.
 *
 * @param bool $ok Whether the expected implementation is present.
 * @param string $message Actionable failure description.
 * @return void
 */
function assertGithubUpdateContract(bool $ok, string $message): void
{
    if (!$ok) {
        throw new RuntimeException($message);
    }
}

$view = file_get_contents($root . '/admin/src/View/Cpanel/HtmlView.php');
$layout = file_get_contents($root . '/admin/tmpl/cpanel/native.php');
$installerView = file_get_contents($root . '/admin/src/View/Githubinstall/HtmlView.php');
$installerLayout = file_get_contents($root . '/admin/tmpl/githubinstall/default.php');
$installerController = file_get_contents($root . '/admin/src/Controller/GithubinstallController.php');
$config = file_get_contents($root . '/admin/config.xml');

foreach (compact('view', 'layout', 'installerView', 'installerLayout', 'installerController', 'config') as $name => $source) {
    assertGithubUpdateContract(is_string($source), 'Cannot load GitHub update source: ' . $name);
}

assertGithubUpdateContract(
    str_contains($view, "authorise('core.admin', 'com_sportsmanagement')")
        && str_contains($view, '$this->canGithubUpdate ='),
    'GitHub dashboard entry must require component administrator rights.'
);
assertGithubUpdateContract(
    str_contains($layout, 'if ($this->canGithubUpdate)')
        && str_contains($layout, 'view=githubinstall')
        && !str_contains($layout, 'task=githubinstall.CopyGithubLink'),
    'Dashboard must link to confirmation, not perform an installation on GET.'
);
assertGithubUpdateContract(
    str_contains($installerView, "authorise('core.admin', 'com_sportsmanagement')")
        && str_contains($installerView, 'isLegacyUpdateSource'),
    'Confirmation page must check administrator permission and legacy master URL.'
);
assertGithubUpdateContract(
    str_contains($installerLayout, 'type="submit"')
        && str_contains($installerLayout, "HTMLHelper::_('form.token')")
        && str_contains($installerLayout, 'task=githubinstall.CopyGithubLink')
        && str_contains($installerLayout, '!$this->isLegacyUpdateSource'),
    'Installation must require an explicit token-protected confirmation and reject master.'
);
assertGithubUpdateContract(
    str_contains($installerController, '$this->checkToken()')
        && str_contains($installerController, "authorise('core.admin', 'com_sportsmanagement')"),
    'GitHub archive installation action must enforce CSRF and administrator ACL.'
);
assertGithubUpdateContract(
    str_contains($config, 'https://github.com/diddipoeler/sportsmanagement/archive/refs/heads/joomla5-6.zip')
        && !str_contains($config, 'default="https://github.com/diddipoeler/sportsmanagement/archive/master.zip"'),
    'Default GitHub update archive must track the Joomla 5/6 branch.'
);

foreach (['de-DE', 'en-GB'] as $language) {
    $strings = file_get_contents(
        $root . "/admin/language/$language/$language.com_sportsmanagement.ini"
    );
    assertGithubUpdateContract(
        is_string($strings)
            && str_contains($strings, 'COM_SPORTSMANAGEMENT_GITHUB_UPDATE_DASHBOARD_DESC=')
            && str_contains($strings, 'COM_SPORTSMANAGEMENT_GITHUB_UPDATE_LEGACY_SOURCE='),
        'Missing GitHub updater dashboard language keys: ' . $language
    );
}

echo "GitHub dashboard updater and protected confirmation contracts OK\n";
