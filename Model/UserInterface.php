<?php

namespace Openpp\PushNotificationBundle\Model;

/**
 * UserInterface
 *
 * @author shiroko@webware.co.jp
 *
 */
interface UserInterface
{
    /**
     * Returns the application
     *
     * @return \Openpp\PushNotificationBundle\Model\ApplicationInterface
     */
    public function getApplication();

    /**
     * Sets the application
     *
     * @param \Openpp\PushNotificationBundle\Model\ApplicationInterface $application
     */
    public function setApplication(ApplicationInterface $application);

    /**
     * Returns the user id
     *
     * @return string
     */
    public function getUid();

    /**
     * Sets the user id
     *
     * @param string $uid
     */
    public function setUid($uid);

    /**
     * Returns the devices
     *
     * @return ArrayCollection
     */
    public function getDevices();

    /**
     * Adds the device
     *
     * @param \Openpp\PushNotificationBundle\Model\DeviceInterface $device
     */
    public function addDevice(DeviceInterface $device);

    /**
     * Removes the device
     *
     * @param \Openpp\PushNotificationBundle\Model\DeviceInterface $device
     */
    public function removeDevice(DeviceInterface $device);

    /**
     * Returns the badge
     *
     * @return integer
     */
    public function getBadge();

    /**
     * Sets the badge
     *
     * @param integer $badge
     */
    public function setBadge($badge);

    /**
     * Returns the tags
     *
     * @return ArrayCollection
     */
    public function getTags();

    /**
     * Adds a tag.
     *
     * @param TagInterface $tag
     */
    public function addTag(TagInterface $tag);

    /**
     * Removes a tag.
     *
     * @param TagInterface $tag
     */
    public function removeTag(TagInterface $tag);

    /**
     * Gets a device by its identifier.
     *
     * @param string $deviceIdentifier
     */
    public function getDeviceByIdentifier($deviceIdentifier);
}