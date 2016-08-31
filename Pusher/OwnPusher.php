<?php

namespace Openpp\PushNotificationBundle\Pusher;

use Sly\NotificationPusher\PushManager;
use Sly\NotificationPusher\Model\Message;
use Sly\NotificationPusher\Collection\DeviceCollection;
use Sly\NotificationPusher\Model\Push;
use Sly\NotificationPusher\Adapter\Gcm;
use Sly\NotificationPusher\Adapter\Apns;
use Openpp\PushNotificationBundle\Model\DeviceInterface;
use Openpp\PushNotificationBundle\Model\ApplicationInterface;
use Openpp\PushNotificationBundle\Model\Device;
use Openpp\PushNotificationBundle\Collections\DeviceCollection as Devices;
use Openpp\WebPushAdapter\Adapter\Web;

class OwnPusher extends AbstractPusher
{
    /**
     * @var string
     */
    protected $publicKey;

    /**
     * @var string
     */
    protected $privateKey;

    /**
     * @var integer
     */
    protected $ttl;

    /**
     * Sets the a signing key pair for Web Push server self-identification
     * generated by using the elliptic curve digital signature (ECDSA) over the P-256 curve.
     *
     * @param string $publicKey
     * @param string $privateKey
     */
    public function setKeyPair($publicKey, $privateKey)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    /**
     * Sets the default TTL for Web Push
     *
     * @param integer $ttl
     */
    public function setTTL($ttl)
    {
        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function push($application, $tagExpression, $message, array $options = array())
    {
        $application = $this->getApplication($application);

        $devices = $this->deviceManager->findDevicesByTagExpression($application, $tagExpression);

        if ($devices) {
            $this->pushToDevice($application, $devices, $message, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function pushToDevice($application, $devices, $message, array $options = array())
    {
        $application = $this->getApplication($application);

        if (is_integer($devices[0])) {
            $devices = $this->deviceManager->findDevicesBy(array('id' => $devices));
        }
        $devices = new Devices($devices);

        $pushManager = new PushManager(PushManager::ENVIRONMENT_PROD);
        $messageObj  = new Message($message, $options);
        $timestamp   = new \DateTime();

        foreach (array_values(Device::getTypeChoices()) as $type) {
            $targetDevices = $devices->getByType($type);
            if (!$targetDevices->count()) {
                continue;
            }
            if ($type == DeviceInterface::TYPE_WEB) {
                // sort by endpoint.
                $collection = new Devices($targetDevices->toArray());
                $targetDevices = $collection->sortByField('token');
            }
            $deviceCollection = new DeviceCollection($targetDevices->toArray());

            $push = new Push($this->getAdapter($application, $type), $deviceCollection, $messageObj);
            $pushManager->add($push);
            $pushManager->push();
        }

        $this->dispatchPushResult($application, $message, $timestamp, $devices);
    }

    /**
     * Get the adapter.
     *
     * @param ApplicationInterface $application
     * @param integer $deviceType
     *
     * @return \Sly\NotificationPusher\Adapter\AdapterInterface
     */
    protected function getAdapter(ApplicationInterface $application, $deviceType)
    {
        switch ($deviceType) {
            case DeviceInterface::TYPE_ANDROID:
                $adapter = new Gcm(array(
                    'apiKey' => $application->getGcmApiKey()
                ));
                break;

            case DeviceInterface::TYPE_IOS:
                $adapter = new Apns(array(
                    'certificate' => $application->getApnsCertificate()
                ));
                break;

            case DeviceInterface::TYPE_WEB:
                if (!$this->publicKey || !$this->privateKey) {
                    throw new \RuntimeException('Need to configure a key pair for Web Push.');
                }
                $parameters = array(
                    'publicKey'  => $this->publicKey,
                    'privateKey' => $this->privateKey,
                );
                if ($this->ttl) {
                    $parameters['ttl'] = $this->ttl;
                }
                $adapter = new Web($parameters);
                break;

            default:
                throw new \RuntimeException('Unsupported device type: ' . $deviceType);
        }

        return $adapter;
    }
}