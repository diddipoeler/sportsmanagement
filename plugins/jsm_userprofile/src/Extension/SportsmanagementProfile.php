<?php
namespace Diddipoeler\Plugin\User\SportsmanagementProfile\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Event\Model\PrepareDataEvent;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Event\User\AfterDeleteEvent;
use Joomla\CMS\Event\User\AfterSaveEvent;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;

/** Joomla 5/6 SportsManagement user profile fields plugin. */
final class SportsmanagementProfile extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepareData' => 'onContentPrepareData',
            'onContentPrepareForm' => 'onContentPrepareForm',
            'onUserAfterSave' => 'onUserAfterSave',
            'onUserAfterDelete' => 'onUserAfterDelete',
        ];
    }

    public function onContentPrepareData(PrepareDataEvent $event): void
    {
        if (!in_array($event->getContext(), ['com_users.user', 'com_users.profile', 'com_admin.profile'], true)) {
            return;
        }

        $data = $event->getData();

        if (!is_object($data)) {
            return;
        }

        $userId = (int) ($data->id ?? 0);
        $data->jsmprofile = [];

        if ($userId <= 0) {
            return;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('profile_key'),
                $db->quoteName('profile_value'),
            ])
            ->from($db->quoteName('#__user_profiles'))
            ->where($db->quoteName('user_id') . ' = :userId')
            ->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('jsmprofile.%'))
            ->order($db->quoteName('ordering'))
            ->bind(':userId', $userId, ParameterType::INTEGER);

        $db->setQuery($query);

        foreach ($db->loadRowList() ?: [] as $row) {
            $key = str_replace('jsmprofile.', '', (string) $row[0]);
            $data->jsmprofile[$key] = $row[1];
        }
    }

    public function onContentPrepareForm(PrepareFormEvent $event): void
    {
        $form = $event->getForm();

        if (!in_array($form->getName(), ['com_users.user', 'com_admin.profile'], true)) {
            return;
        }

        $this->loadLanguage();
        FormHelper::addFormPath(dirname(__DIR__, 2) . '/profiles');
        $form->loadFile('profile', false);
    }

    public function onUserAfterSave(AfterSaveEvent $event): void
    {
        $data = $event->getUser();
        $userId = ArrayHelper::getValue($data, 'id', 0, 'int');

        if ($userId <= 0 || !$event->getSavingResult() || empty($data['jsmprofile']) || !is_array($data['jsmprofile'])) {
            return;
        }

        $db = $this->getDatabase();
        $delete = $db->getQuery(true)
            ->delete($db->quoteName('#__user_profiles'))
            ->where($db->quoteName('user_id') . ' = :userId')
            ->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('jsmprofile.%'))
            ->bind(':userId', $userId, ParameterType::INTEGER);
        $db->setQuery($delete)->execute();

        $insert = $db->getQuery(true)
            ->insert($db->quoteName('#__user_profiles'))
            ->columns([
                $db->quoteName('user_id'),
                $db->quoteName('profile_key'),
                $db->quoteName('profile_value'),
                $db->quoteName('ordering'),
            ]);

        $ordering = 1;

        foreach ($data['jsmprofile'] as $key => $value) {
            $storedValue = is_scalar($value) || $value === null
                ? (string) $value
                : (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $insert->values(implode(',', [
                (string) $userId,
                $db->quote('jsmprofile.' . (string) $key),
                $db->quote($storedValue),
                (string) $ordering++,
            ]));
        }

        $db->setQuery($insert)->execute();
    }

    public function onUserAfterDelete(AfterDeleteEvent $event): void
    {
        if (!$event->getDeletingResult()) {
            return;
        }

        $user = $event->getUser();
        $userId = ArrayHelper::getValue($user, 'id', 0, 'int');

        if ($userId <= 0) {
            return;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__user_profiles'))
            ->where($db->quoteName('user_id') . ' = :userId')
            ->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('jsmprofile.%'))
            ->bind(':userId', $userId, ParameterType::INTEGER);

        $db->setQuery($query)->execute();
    }
}
