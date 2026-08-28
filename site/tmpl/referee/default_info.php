<?php
/**
 * Native Joomla 5/6 referee personal information.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonAgeHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if (!$this->referee) {
    return;
}

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$formattedName = PersonNameFormatter::format(
    null,
    (string) ($this->referee->firstname ?? ''),
    (string) ($this->referee->nickname ?? ''),
    (string) ($this->referee->lastname ?? ''),
    (int) ($this->config['name_format'] ?? 0)
);
$pictureBase = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
    ? (string) COM_SPORTSMANAGEMENT_PICTURE_SERVER
    : Uri::root();
$resolvePicture = static function (string $picture) use ($pictureBase): string {
    $picture = trim($picture);

    if ($picture === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $picture)) {
        return $picture;
    }

    $path = JPATH_SITE . '/' . ltrim(str_replace('\\', '/', $picture), '/');
    if (!is_file($path)) {
        return '';
    }

    return rtrim($pictureBase, '/') . '/' . ltrim($picture, '/');
};
?>
<h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PERSONAL_DATA'); ?></h2>

<div class="<?php echo $escape($this->divclassrow); ?> table-responsive" id="referee_info">
    <div class="col-md-6">
        <?php if (!empty($this->config['show_photo'])) : ?>
            <?php
            $picture = trim((string) ($this->referee->picture ?? ''));
            $placeholder = PersonImageHelper::placeholder();

            if ($picture === '' || $picture === $placeholder) {
                $picture = trim((string) ($this->person->picture ?? ''));
            }

            $pictureUrl = $resolvePicture($picture);
            if ($pictureUrl === '') {
                $pictureUrl = preg_match('#^https?://#i', $placeholder)
                    ? $placeholder
                    : ($placeholder !== ''
                        ? rtrim($pictureBase, '/') . '/' . ltrim($placeholder, '/')
                        : '');
            }

            if ($pictureUrl !== '') {
                $imageTitle = Text::sprintf('COM_SPORTSMANAGEMENT_PERSON_PICTURE', $formattedName);
                echo ModalImageHelper::render(
                    'referee' . (int) ($this->referee->id ?? 0),
                    $pictureUrl,
                    $imageTitle,
                    (int) ($this->config['picture_width'] ?? 50),
                    '',
                    $this->modalwidth,
                    $this->modalheight,
                    (int) ($this->overallconfig['use_jquery_modal'] ?? 0),
                    'itemprop',
                    'image'
                );
            }
            ?>
        <?php endif; ?>
    </div>

    <div class="col-md-6">
        <?php if (!empty($this->person->country) && (int) ($this->config['show_nationality'] ?? 0) === 1) : ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NATIONALITY'); ?></strong>
                <?php
                $country = (string) $this->person->country;
                echo CountryPresentationHelper::flag($country)
                    . ' ' . $escape(CountryPresentationHelper::name($country));
                ?>
            </address>
        <?php endif; ?>

        <?php
        $outputName = $escape(trim(
            (string) ($this->referee->firstname ?? '') . ' ' . (string) ($this->referee->lastname ?? '')
        ));
        $userId = (int) ($this->referee->user_id ?? 0);

        if ($userId > 0) {
            switch ((int) ($this->config['show_user_profile'] ?? 0)) {
                case 1:
                    $outputName = HTMLHelper::link(
                        SiteRouteHelper::query([
                            'option' => 'com_contact',
                            'view' => 'contact',
                            'id' => $userId,
                        ]),
                        $outputName
                    );
                    break;

                case 2:
                    $outputName = HTMLHelper::link(
                        SiteRouteHelper::query([
                            'option' => 'com_cbe',
                            'view' => 'userProfile',
                            'user' => $userId,
                            'jlp' => (int) ($this->project->id ?? 0),
                            'jlpid' => (int) ($this->referee->id ?? 0),
                        ]),
                        $outputName
                    );
                    break;
            }
        }
        ?>
        <address>
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NAME'); ?></strong>
            <?php echo $outputName; ?>
        </address>

        <?php if (!empty($this->referee->nickname)) : ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NICKNAME'); ?></strong>
                <?php echo $escape($this->referee->nickname); ?>
            </address>
        <?php endif; ?>

        <?php
        $birthdayMode = (int) ($this->config['show_birthday'] ?? 0);
        $birthday = (string) ($this->referee->birthday ?? '0000-00-00');
        $deathday = (string) ($this->referee->deathday ?? '0000-00-00');
        if ($birthdayMode > 0 && $birthdayMode < 5 && $birthday !== '0000-00-00') :
            $label = match ($birthdayMode) {
                1 => 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY_AGE',
                2 => 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY',
                3 => 'COM_SPORTSMANAGEMENT_PERSON_AGE',
                4 => 'COM_SPORTSMANAGEMENT_PERSON_YEAR_OF_BIRTH',
            };
            $birthdayValue = match ($birthdayMode) {
                1 => HTMLHelper::date($birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE'))
                    . '&nbsp;(' . $escape(PersonAgeHelper::calculate($birthday, $deathday)) . ')',
                2 => HTMLHelper::date($birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE')),
                3 => $escape(PersonAgeHelper::calculate($birthday, $deathday)),
                4 => HTMLHelper::date($birthday, Text::_('%Y')),
            };
            ?>
            <address>
                <strong><?php echo Text::_($label); ?></strong>
                <?php echo $birthdayValue; ?>
            </address>
        <?php endif; ?>

        <?php if (!empty($this->referee->address) && (int) ($this->config['show_person_address'] ?? 0) === 1) : ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_ADDRESS'); ?></strong>
                <?php
                echo CountryPresentationHelper::address(
                    '',
                    (string) ($this->referee->address ?? ''),
                    (string) ($this->referee->state ?? ''),
                    (string) ($this->referee->zipcode ?? ''),
                    (string) ($this->referee->location ?? ''),
                    (string) ($this->referee->address_country ?? ''),
                    'COM_SPORTSMANAGEMENT_PERSON_ADDRESS_FORM'
                );
                ?>
            </address>
        <?php endif; ?>

        <?php if (!empty($this->referee->phone) && (int) ($this->config['show_person_phone'] ?? 0) === 1) : ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PHONE'); ?></strong>
                <?php echo $escape($this->referee->phone); ?>
            </address>
        <?php endif; ?>

        <?php if (!empty($this->referee->mobile) && (int) ($this->config['show_person_mobile'] ?? 0) === 1) : ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_MOBILE'); ?></strong>
                <?php echo $escape($this->referee->mobile); ?>
            </address>
        <?php endif; ?>

        <?php if (!empty($this->referee->email) && (int) ($this->config['show_person_email'] ?? 0) === 1) : ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_EMAIL'); ?></strong>
                <?php
                $email = (string) $this->referee->email;
                $identity = $this->app->getIdentity();
                if ((int) $identity->id > 0 || empty($this->overallconfig['nospam_email'])) {
                    echo '<a href="mailto:' . $escape($email) . '">' . $escape($email) . '</a>';
                } else {
                    echo HTMLHelper::_('email.cloak', $email);
                }
                ?>
            </address>
        <?php endif; ?>

        <?php if (!empty($this->referee->website) && (int) ($this->config['show_person_website'] ?? 0) === 1) : ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_WEBSITE'); ?></strong>
                <?php
                echo HTMLHelper::link(
                    (string) $this->referee->website,
                    $escape($this->referee->website),
                    ['target' => '_blank', 'rel' => 'noopener noreferrer']
                );
                ?>
            </address>
        <?php endif; ?>

        <?php if ((float) ($this->referee->height ?? 0) > 0 && (int) ($this->config['show_person_height'] ?? 0) === 1) : ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_HEIGHT'); ?></strong>
                <?php
                echo $escape(str_replace(
                    '%HEIGHT%',
                    (string) $this->referee->height,
                    Text::_('COM_SPORTSMANAGEMENT_PERSON_HEIGHT_FORM')
                ));
                ?>
            </address>
        <?php endif; ?>

        <?php if ((float) ($this->referee->weight ?? 0) > 0 && (int) ($this->config['show_person_weight'] ?? 0) === 1) : ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_WEIGHT'); ?></strong>
                <?php
                echo $escape(str_replace(
                    '%WEIGHT%',
                    (string) $this->referee->weight,
                    Text::_('COM_SPORTSMANAGEMENT_PERSON_WEIGHT_FORM')
                ));
                ?>
            </address>
        <?php endif; ?>

        <?php if (!empty($this->referee->position_name)) : ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_POSITION'); ?></strong>
                <?php echo Text::_((string) $this->referee->position_name); ?>
            </address>
        <?php endif; ?>

        <?php if (!empty($this->referee->knvbnr) && (int) ($this->config['show_person_regnr'] ?? 0) === 1) : ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_REGISTRATIONNR'); ?></strong>
                <?php echo $escape($this->referee->knvbnr); ?>
            </address>
        <?php endif; ?>
    </div>
</div>
<br>
