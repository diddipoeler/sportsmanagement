<?php
/**
 * Shared Joomla 5/6 project heading.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ExtraFieldsReadHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ProjectNavigationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$overall = (array) ($this->overallconfig ?? []);
$config = (array) ($this->config ?? []);
$project = $this->project ?? null;
$input = $this->input;
$document = $this->getDocument();
$componentParams = ComponentHelper::getParams('com_sportsmanagement');
$pictureServer = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
    ? (string) COM_SPORTSMANAGEMENT_PICTURE_SERVER
    : Uri::root();
$pictureUrl = static function (string $picture) use ($pictureServer): string {
    if ($picture === '') {
        return '';
    }

    return preg_match('#^https?://#i', $picture)
        ? $picture
        : rtrim($pictureServer, '/') . '/' . ltrim($picture, '/');
};
$placeholder = static function (string $key) use ($componentParams): string {
    return trim((string) $componentParams->get($key, ''));
};
$printButton = static function () use ($input, $overall): string {
    if (empty($overall['show_print_button']) || $input->getInt('print', 0) === 1) {
        return '';
    }

    $printUri = clone Uri::getInstance();
    $printUri->setVar('print', 1);
    $printUri->setVar('tmpl', 'component');
    $title = Text::_('JGLOBAL_PRINT');
    $label = !empty($overall['show_icons'])
        ? HTMLHelper::image('media/com_sportsmanagement/jl_images/printButton.png', $title, ['title' => $title])
        : $title;

    return HTMLHelper::link(
        $printUri->toString(),
        $label,
        [
            'target' => '_blank',
            'rel' => 'noopener',
            'title' => $title,
        ]
    );
};

if (!$overall) {
    return;
}

$columnCount = 2;
foreach (['show_project_sporttype_picture', 'show_project_kunena_link', 'show_project_picture', 'show_project_staffel_id'] as $option) {
    if (!empty($overall[$option])) {
        $columnCount++;
    }
}

if (!empty($overall['show_project_heading']) && $project) :
    ?>
    <div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?>"
         id="projectheading"
         itemscope
         itemtype="https://schema.org/SportsOrganization">
        <table class="table">
            <?php
            if (!empty($overall['show_project_extrafield'])) {
                $model = $this->getModel();
                if ($model instanceof SportsManagementProjectModel) {
                    $viewName = $input->getCmd('view', (string) ($this->view ?? $this->getName()));
                    $extraFields = ExtraFieldsReadHelper::load(
                        $model->getDatabase(),
                        (int) ($project->league_id ?? 0),
                        $viewName
                    );
                    $linkTitle = (string) ($project->league_name ?? '');

                    foreach ($extraFields as $field) {
                        $value = trim((string) ($field->fvalue ?? ''));
                        if ($value === '') {
                            continue;
                        }
                        ?>
                        <tr>
                            <td><?php echo Text::_((string) ($field->name ?? '')); ?></td>
                            <td>
                                <?php
                                echo (string) ($field->field_type ?? '') === 'link'
                                    ? HTMLHelper::link($value, $linkTitle, ['target' => '_blank', 'rel' => 'noopener'])
                                    : Text::_($value);
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
            }

            if (!empty($overall['show_project_country'])) :
                $country = (string) ($project->country ?? '');
                ?>
                <tr class="contentheading">
                    <td colspan="<?php echo $columnCount; ?>">
                        <?php
                        echo CountryPresentationHelper::flag($country)
                            . ' ' . htmlspecialchars(CountryPresentationHelper::name($country), ENT_QUOTES, 'UTF-8');
                        ?>
                    </td>
                </tr>
            <?php endif; ?>

            <tr class="contentheading">
                <?php if (!empty($overall['show_project_sporttype_picture'])) : ?>
                    <td>
                        <?php
                        $sportPicture = trim((string) ($project->sport_type_picture ?? ''));
                        if ($sportPicture === '') {
                            $sportPicture = $placeholder('ph_logo_big');
                        }
                        echo ModalImageHelper::render(
                            'sporttype-picture',
                            $pictureUrl($sportPicture),
                            Text::_((string) ($project->sport_type_name ?? '')),
                            (int) ($overall['picture_width'] ?? 20),
                            '',
                            (int) $this->modalwidth,
                            (int) $this->modalheight,
                            (int) ($overall['use_jquery_modal'] ?? 0),
                            'itemprop',
                            'image'
                        );
                        ?>
                    </td>
                <?php endif; ?>

                <?php if (!empty($overall['show_project_picture'])) : ?>
                    <td>
                        <?php
                        $projectPicture = trim((string) ($project->picture ?? ''));
                        $copyright = (string) ($project->cr_picture ?? '');
                        if ($projectPicture === '' || $projectPicture === 'images/com_sportsmanagement/database/placeholders/placeholder_150.png') {
                            $projectPicture = trim((string) ($project->leaguepicture ?? ''));
                            $copyright = (string) ($project->cr_leaguepicture ?? '');
                        }
                        if ($projectPicture === '') {
                            $projectPicture = $placeholder('ph_logo_big');
                        }

                        echo ModalImageHelper::render(
                            'project-picture',
                            $pictureUrl($projectPicture),
                            (string) ($project->name ?? ''),
                            (int) ($overall['picture_width'] ?? 20),
                            '',
                            (int) $this->modalwidth,
                            (int) $this->modalheight,
                            (int) ($overall['use_jquery_modal'] ?? 0),
                            'itemprop',
                            'image'
                        );

                        if ($copyright !== '') {
                            echo Text::sprintf(
                                'COM_SPORTSMANAGEMENT_COPYRIGHT_INFO',
                                '<i>' . htmlspecialchars($copyright, ENT_QUOTES, 'UTF-8') . '</i>'
                            );
                        }
                        ?>
                    </td>
                <?php endif; ?>

                <?php if (!empty($overall['show_project_text'])) : ?>
                    <td class="contentheading">
                        <span itemprop="name"><?php echo htmlspecialchars((string) ($project->name ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if (!empty($this->division)) : ?>
                            - <?php echo htmlspecialchars((string) ($this->division->name ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>

                <?php if (!empty($overall['show_project_staffel_id'])) : ?>
                    <td>
                        <?php
                        echo Text::sprintf(
                            'COM_SPORTSMANAGEMENT_PROJECT_INFO_STAFFEL_ID',
                            '<i>' . htmlspecialchars((string) ($project->staffel_id ?? ''), ENT_QUOTES, 'UTF-8') . '</i>'
                        );
                        ?>
                    </td>
                <?php endif; ?>

                <td class="buttonheading" align="right">
                    <?php echo $printButton(); ?>&nbsp;
                </td>

                <td class="buttonheading" align="right">
                    <?php
                    if (!empty($overall['show_project_kunena_link']) && !empty($project->sb_catid)) {
                        $link = SiteRouteHelper::query([
                            'option' => 'com_kunena',
                            'view' => 'topic',
                            'catid' => (int) $project->sb_catid,
                        ]);
                        $title = (string) ($project->name ?? '') . ' Forum';
                        $description = HTMLHelper::image(
                            'media/com_sportsmanagement/jl_images/kunena.logo.png',
                            $title,
                            ['title' => $title, 'width' => '100']
                        );
                        echo HTMLHelper::link($link, $description);
                    }
                    ?>&nbsp;
                </td>
            </tr>
        </table>
    </div>
    <?php
elseif (!empty($overall['show_print_button'])) :
    ?>
    <div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?>">
        <table class="table">
            <tr class="contentheading">
                <td class="buttonheading" align="right"><?php echo $printButton(); ?>&nbsp;</td>
            </tr>
        </table>
    </div>
    <?php
endif;

if (($this->view ?? '') === 'ranking' && !empty($project)) {
    $showNavigation = array_key_exists('show_project_navigation', $config)
        ? (bool) $config['show_project_navigation']
        : true;

    if ($showNavigation) {
        $model = $this->getModel();
        if ($model instanceof SportsManagementProjectModel) {
            $previousProject = ProjectNavigationHelper::previous($model->getDatabase(), $project);
            $nextProject = ProjectNavigationHelper::next($model->getDatabase(), $project);
            $databaseSelector = $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
            ?>
            <div class="row">
                <div class="col-sm-6 text-left">
                    <?php if ($previousProject) :
                        $previousLink = SiteRouteHelper::view('ranking', [
                            'cfg_which_database' => $databaseSelector,
                            's' => (int) ($previousProject->season_id ?? 0),
                            'p' => (string) ($previousProject->slug ?? $previousProject->id ?? ''),
                            'type' => 0,
                            'r' => 0,
                            'from' => 0,
                            'to' => 0,
                            'division' => 0,
                        ]);
                        ?>
                        <a href="<?php echo htmlspecialchars($previousLink, ENT_QUOTES, 'UTF-8'); ?>"
                           class="btn btn-primary btn-sm active"
                           role="button">&laquo; <?php echo htmlspecialchars((string) $previousProject->name, ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php endif; ?>
                </div>
                <div class="col-sm-6 text-right">
                    <?php if ($nextProject) :
                        $nextLink = SiteRouteHelper::view('ranking', [
                            'cfg_which_database' => $databaseSelector,
                            's' => (int) ($nextProject->season_id ?? 0),
                            'p' => (string) ($nextProject->slug ?? $nextProject->id ?? ''),
                            'type' => 0,
                            'r' => 0,
                            'from' => 0,
                            'to' => 0,
                            'division' => 0,
                        ]);
                        ?>
                        <a href="<?php echo htmlspecialchars($nextLink, ENT_QUOTES, 'UTF-8'); ?>"
                           class="btn btn-primary btn-sm active"
                           role="button"><?php echo htmlspecialchars((string) $nextProject->name, ENT_QUOTES, 'UTF-8'); ?> &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    }
}
