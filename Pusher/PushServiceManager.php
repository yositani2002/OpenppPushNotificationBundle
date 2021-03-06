<?php

namespace Openpp\PushNotificationBundle\Pusher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Openpp\PushNotificationBundle\Model\TagManagerInterface;
use Openpp\PushNotificationBundle\TagExpression\TagExpression;
use Openpp\PushNotificationBundle\Consumer\PushNotificationConsumer;
use Openpp\PushNotificationBundle\Event\PrePushEvent;


class PushServiceManager implements PushServiceManagerInterface
{
    protected $dispatcher;
    protected $backend;
    protected $tagManager;
    protected $pusher;

    /**
     * Constructor
     *
     * @param EventDispatcherInterface $dispatcher
     * @param BackendInterface         $backend
     * @param TagManagerInterface      $tagManager
     * @param PusherInterface          $pusher
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        BackendInterface         $backend,
        TagManagerInterface      $tagManager,
        PusherInterface          $pusher = null
    ) {
        $this->dispatcher = $dispatcher;
        $this->backend    = $backend;
        $this->tagManager = $tagManager;
        $this->pusher     = $pusher;
    }

    /**
     * {@inheritdoc}
     */
    public function push($applicationName, $tagExpression, $message, array $options = array())
    {
        if ($tagExpression != '') {
            $te = new TagExpression($tagExpression);
            $te->validate();
        }

        list(
            $applicationName,
            $tagExpression,
            $message,
            $options,
            $devices
        ) = $this->dispatchPrePushEvent($applicationName, $tagExpression, $message, $options);

        $this->backend->createAndPublish(PushNotificationConsumer::TYPE_NAME, array(
            'application'   => $applicationName,
            'tagExpression' => $tagExpression,
            'message'       => $message,
            'options'       => $options,
            'operation'     => self::OPERATION_PUSH,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function pushExecute($applicationName, $tagExpression, $message, array $options = array())
    {
        $this->getPusher()->push($applicationName, $tagExpression, $message, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function pushToDevices($applicationName, $devices, $message, array $options = array())
    {
        list(
            $applicationName,
            $tagExpression,
            $message,
            $options,
            $devices
        ) = $this->dispatchPrePushEvent($applicationName, null, $message, $options, $devices);

        $this->backend->createAndPublish(PushNotificationConsumer::TYPE_NAME, array(
            'application' => $applicationName,
            'devices'     => $devices,
            'message'     => $message,
            'options'     => $options,
            'operation'   => self::OPERATION_PUSH_TO_DEVICES,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function pushToDevicesExecute($applicationName, $devices, $message, array $options = array())
    {
        $this->getPusher()->pushToDevice($applicationName, $devices, $message, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function addTagToUser($applicationName, $uid, $tag)
    {
        if (!is_array($tag)) {
            $tag = array($tag);
        }

        foreach ($tag as $idx => $one) {

            TagExpression::validateSingleTag($one);

            if ($this->tagManager->isReservedTag($one)) {
                unset($tag[$idx]);
            }
        }
        if (!$tag) {
            return;
        }

        $this->backend->createAndPublish(PushNotificationConsumer::TYPE_NAME, array(
            'application' => $applicationName,
            'uid'         => $uid,
            'tag'         => $tag,
            'operation'   => self::OPERATION_ADDTAGTOUSER,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function addTagToUserExecute($applicationName, $uid, $tag)
    {
        $this->getPusher()->addTagToUser($applicationName, $uid, $tag);
    }

    /**
     * {@inheritdoc}
     */
    public function removeTagFromUser($applicationName, $uid, $tag)
    {
        if (!is_array($tag)) {
            $tag = array($tag);
        }

        foreach ($tag as $idx => $one) {

            TagExpression::validateSingleTag($one);

            if ($this->tagManager->isReservedTag($one)) {
                unset($tag[$idx]);
            }
        }
        if (!$tag) {
            return;
        }

        $this->backend->createAndPublish(PushNotificationConsumer::TYPE_NAME, array(
            'application' => $applicationName,
            'uid'         => $uid,
            'tag'         => $tag,
            'operation'   => self::OPERATION_REMOVETAGFROMUSER,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function removeTagFromUserExecute($applicationName, $uid, $tag)
    {
        $this->getPusher()->removeTagFromUser($applicationName, $uid, $tag);
    }

    /**
     * {@inheritDoc}
     */
    public function createRegistration($applicationName, $deviceIdentifier, array $tags)
    {
        $this->backend->createAndPublish(PushNotificationConsumer::TYPE_NAME, array(
            'application'      => $applicationName,
            'deviceIdentifier' => $deviceIdentifier,
            'tags'             => $tags,
            'operation'        => self::OPERATION_CREATE_REGISTRATION,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function createRegistrationExecute($applicationName, $deviceIdentifier, array $tags)
    {
        $this->getPusher()->createRegistration($applicationName, $deviceIdentifier, $tags);
    }

    /**
     * {@inheritDoc}
     */
    public function updateRegistration($applicationName, $deviceIdentifier, array $tags)
    {
        $this->backend->createAndPublish(PushNotificationConsumer::TYPE_NAME, array(
            'application'      => $applicationName,
            'deviceIdentifier' => $deviceIdentifier,
            'tags'             => $tags,
            'operation'   => self::OPERATION_UPDATE_REGISTRATION,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function updateRegistrationExecute($applicationName, $deviceIdentifier, array $tags)
    {
        $this->getPusher()->updateRegistration($applicationName, $deviceIdentifier, $tags);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteRegistration($applicationName, $type, $registrationId, $eTag)
    {
        $this->backend->createAndPublish(PushNotificationConsumer::TYPE_NAME, array(
            'application'    => $applicationName,
            'type'           => $type,
            'registrationId' => $registrationId,
            'eTag'           => $eTag,
            'operation'      => self::OPERATION_DELETE_REGISTRATION,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function deleteRegistrationExecute($applicationName, $type, $registrationId, $eTag)
    {
        $this->getPusher()->deleteRegistration($applicationName, $type, $registrationId, $eTag);
    }

    /**
     * {@inheritDoc}
     */
    public function getPusher()
    {
        return $this->pusher;
    }

    /**
     * @param string $applicationName
     * @param string $tagExpression
     * @param string $message
     * @param array $options
     * @param array $devices
     *
     * @return array
     */
    protected function dispatchPrePushEvent($applicationName, $tagExpression, $message, array $options = array(), $devices = array())
    {
        $event = new PrePushEvent($applicationName, $tagExpression, $message, $options, $devices);
        $event = $this->dispatcher->dispatch(PrePushEvent::EVENT_NAME, $event);

        return array(
            $event->getApplicationName(),
            $event->getTagExpression(),
            $event->getMessage(),
            $event->getOptions(),
            $event->getDevices(),
        );
    }
}