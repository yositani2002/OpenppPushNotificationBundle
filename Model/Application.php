<?php

namespace Openpp\PushNotificationBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Represents a Application model
 *
 * @author shiroko@webware.co.jp
 *
 */
class Application implements ApplicationInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $connectionString;

    /**
     * @var string
     */
    protected $hubName;

    /* TODO: impelement it.
    protected $apnsCertificate;

    protected $apnsCertificateKey;

    protected $gcmApiKey;
    */
    /**
     * @var array
     */
    protected $users;

    /**
     * @var \Datetime
     */
    protected $createdAt;

    /**
     * @var \Datetime
     */
    protected $updatedAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users   = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionString()
    {
        return $this->connectionString;
    }

    /**
     * {@inheritdoc}
     */
    public function setConnectionString($connectionString)
    {
        $this->connectionString = $connectionString;
    }

    /**
     * {@inheritdoc}
     */
    public function getHubName()
    {
        return $this->hubName;
    }

    /**
     * {@inheritdoc}
     */
    public function setHubName($hubName)
    {
        $this->hubName = $hubName;
    }

    /* TODO: implement it.
    public function getApnsCertificate()
    {
        return $this->apnsCertificate;
    }

    public function setApnsCertificate($apnsCertificate)
    {
        $this->apnsCertificate = $apnsCertificate;
    }

    public function getApnsCertificateKey()
    {
        return $this->apnsCertificateKey;
    }

    public function setApnsCertificateKey($apnsCertificateKey)
    {
        $this->apnsCertificateKey = $apnsCertificateKey;
    }

    public function getGcmApiKey()
    {
        return $this->gcmApiKey;
    }

    public function setGcmApiKey($gcmApiKey)
    {
        $this->gcmApiKey = $gcmApiKey;
    }
    */

    /**
     * {@inheritdoc}
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * {@inheritdoc}
     */
    public function addUser(UserInterface $user)
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeUser(UserInterface $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Returns the creation date.
     *
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the creation date.
     *
     * @param \DateTime|null $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Returns the last update date.
     *
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Sets the last update date.
     *
     * @param \DateTime|null $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Returns a string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}