<?php

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity
 * @Table(
 *     name="provider_licenses"
 * )
 */
class ProviderLicenses extends EntityBase implements \JsonSerializable
{
	/**
	 * Record ID.
	 *
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @Column(type="string", length=255)
	 * @ImportMap Provider_NPI
	 * @ImportRequired
	 * @ImportExists Provider
	 */
	protected $providerNPI;
	
	/**
	 * @Column(type="text")
	 * @ImportMap License_Licensee
	 */
	protected $licensee;
	
	/**
	 * @Column(type="text")
	 * @ImportMap License_License_Number
	 */
	protected $licenseNumber;
	
	/**
	 * @Column(type="text")
	 * @ImportMap License_Name
	 */
	protected $name;
	
	/**
	 * @Column(type="text")
	 * @ImportMap License_Type
	 * @ImportRequired
	 */
	protected $type;
	
	/**
	 * @Column(type="text")
	 * @ImportMap License_State
	 */
	protected $state;
	
	/**
	 * @Column(type="date")
	 * @ImportMap License_Expired
	 */
	protected $expired;
	
	/**
	 * @Column(type="date",nullable=true)
	 */
	protected $created = null;
	
	/**
	 * @Column(type="date",nullable=true)
	 */
	protected $lastUpdated = null;

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return ProviderLicenses
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set providerNPI
     *
     * @param string $providerNPI
     *
     * @return ProviderLicenses
     */
    public function setProviderNPI($providerNPI)
    {
        $this->providerNPI = $providerNPI;

        return $this;
    }

    /**
     * Get providerNPI
     *
     * @return string
     */
    public function getProviderNPI()
    {
        return $this->providerNPI;
    }

    /**
     * Set licensee
     *
     * @param string $licensee
     *
     * @return ProviderLicenses
     */
    public function setLicensee($licensee)
    {
        $this->licensee = $licensee;

        return $this;
    }

    /**
     * Get licensee
     *
     * @return string
     */
    public function getLicensee()
    {
        return $this->licensee;
    }

    /**
     * Set licenseNumber
     *
     * @param string $licenseNumber
     *
     * @return ProviderLicenses
     */
    public function setLicenseNumber($licenseNumber)
    {
        $this->licenseNumber = $licenseNumber;

        return $this;
    }

    /**
     * Get licenseNumber
     *
     * @return string
     */
    public function getLicenseNumber()
    {
        return $this->licenseNumber;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ProviderLicenses
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return ProviderLicenses
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return ProviderLicenses
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set expired
     *
     * @param \DateTime $expired
     *
     * @return ProviderLicenses
     */
    public function setExpired($expired)
    {
        $this->expired = $expired;

        return $this;
    }

    /**
     * Get expired
     *
     * @return \DateTime
     */
    public function getExpired()
    {
        return $this->expired;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return ProviderLicenses
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set lastUpdated
     *
     * @param \DateTime $lastUpdated
     *
     * @return ProviderLicenses
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    /**
     * Get lastUpdated
     *
     * @return \DateTime
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }
}
