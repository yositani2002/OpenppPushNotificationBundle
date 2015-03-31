<?php

namespace Openpp\PushNotificationBundle\Pusher;

use Openpp\PushNotificationBundle\Model\ApplicationManagerInterface;
use Openpp\PushNotificationBundle\Model\TagManagerInterface;
use Openpp\PushNotificationBundle\Model\UserManagerInterface;
use Openpp\PushNotificationBundle\Model\DeviceManagerInterface;
use Openpp\PushNotificationBundle\Model\ApplicationInterface;
use Openpp\PushNotificationBundle\Model\DeviceInterface;
use Openpp\NotificationHubsRest\NotificationHub\NotificationHub;
use Openpp\NotificationHubsRest\Notification\NotificationFactory;
use Openpp\NotificationHubsRest\Registration\RegistrationFactory;
use Openpp\PushNotificationBundle\Exception\DeviceNotFoundException;
use Openpp\PushNotificationBundle\Exception\ApplicationNotFoundException;

/**
 * 
 * @author shiroko@webware.co.jp
 *
 */
class AzurePusher extends AbstractPusher
{
    protected $hubs;
    protected $notificationFactory;
    protected $registrationFactory;

    /**
     * Constructor
     *
     * @param ApplicationManagerInterface $applicationManager
     * @param TagManagerInterface $tagManager
     * @param UserManagerInterface $userManager
     * @param NotificationFactory $notificationFactory
     * @param RegistrationFactory $registrationFactory
     */
    public function __construct(ApplicationManagerInterface $applicationManager, TagManagerInterface $tagManager, UserManagerInterface $userManager, DeviceManagerInterface $deviceManager, NotificationFactory $notificationFactory, RegistrationFactory $registrationFactory)
    {
        $this->notificationFactory = $notificationFactory;
        $this->registrationFactory = $registrationFactory;

        parent::__construct($applicationManager, $tagManager, $userManager, $deviceManager);
    }

    /**
     * {@inheritdoc}
     */
    public function push($applicationName, $target, $message, array $options = array())
    {
        $application = $this->applicationManager->findApplicationByName($applicationName);
        if (!$application) {
            throw new ApplicationNotFoundException($applicationName . ' is not found.');
        }

        $notifications = $this->createNotifications($application, $target, $message, $options);

        foreach ($notifications as $notification) {
            $this->getHub($application)->sendNotification($notification);
        }
    }

    /**
     * Gets a Notification Hub for the application.
     *
     * @param ApplicationInterface $application
     *
     * @return NotificationHub
     */
    protected function getHub(ApplicationInterface $application)
    {
        if (!isset($this->hubs[$application->getHubName()])) {
            $this->hubs[$application->getHubName()] = new NotificationHub($application->getConnectionString(), $application->getHubName());
        }

        return $this->hubs[$application->getHubName()];
    }

    /**
     * Creates Notifications.
     *
     * @param ApplicationInterface $application
     * @param string $target
     * @param string $message
     * @param array $options
     *
     * @return Notification
     */
    protected function createNotifications(ApplicationInterface $application, $target, $message, array $options)
    {
        $notifications = array();

        if (TagManagerInterface::BROADCAST_TAG === $target) {
            $target = '';
        }

        if ($this->hasAndroidTarget($application, $target)) {
            $notifications[] = $this->notificationFactory->createNotification('gcm', $message, $options, $target);
        }

        if ($this->hasIOSTarget($application, $target)) {
            $notifications[] = $this->notificationFactory->createNotification('apple', $message, $options, $target);
        }

        return $notifications;
    }

    /**
     * {@inheritdoc}
     */
    public function createRegistration($applicationName, DeviceInterface $device, array $tags)
    {
        $application = $this->applicationManager->findApplicationByName($applicationName);
        if (!$application) {
            throw new ApplicationNotFoundException($applicationName . ' is not found.');
        }

        $type = $device->getType() === DeviceInterface::TYPE_IOS ? "apple" : "gcm";

        $registration = $this->registrationFactory->createRegistration($type);
        $registration->setToken($device->getToken());
        if (!empty($tags)) {
            $registration->setTags($tags);
        }

        $result = $this->getHub($application)->createRegistration($registration);

        $device->setRegistrationId($result['RegistrationId']);
        $device->setETag($result['ETag']);
        $this->deviceManager->updateDevice($device);
    }

    /**
     * {@inheritdoc}
     */
    public function updateRegistration($applicationName, $deviceIdentifier, array $tags)
    {
        $application = $this->applicationManager->findApplicationByName($applicationName);
        if (!$application) {
            throw new ApplicationNotFoundException('Application ' . $applicationName . ' is not found.');
        }

        $device = $this->deviceManager->findDeviceByIdentifier($application, $deviceIdentifier);
        if (!$device) {
            throw new DeviceNotFoundException($applicationName . "'s device " . $deviceIdentifier . 'is not found.');
        }

        $type = $device->getType() === DeviceInterface::TYPE_IOS ? "apple" : "gcm";

        $registration = $this->registrationFactory->createRegistration($type);
        $registration->setToken($device->getToken())
                     ->setRegistrationId($device->getRegistrationId())
                     ->setETag($device->getETag());

        if (!empty($tags)) {
            $registration->setTags($tags);
        }

        $result = $this->getHub($application)->updateRegistration($registration);

        $device->setRegistrationId($result['RegistrationId']);
        $device->setETag($result['ETag']);
        $this->deviceManager->updateDevice($device);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRegistration($applicationName, $type, $registrationId, $eTag)
    {
        $application = $this->applicationManager->findApplicationByName($applicationName);
        if (!$application) {
            throw new ApplicationNotFoundException($applicationName . ' is not found.');
        }

        $deviceType = $type === DeviceInterface::TYPE_IOS ? "apple" : "gcm";

        $registration = $this->registrationFactory->createRegistration($deviceType);
        $registration->setRegistrationId($registrationId)
                     ->setETag($eTag);

        $this->getHub($application)->deleteRegistration($registration);
    }
}