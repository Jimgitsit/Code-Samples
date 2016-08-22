<?php

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity
 * @Table(
 *     name="provider_offices"
 * )
 */
class ProviderOffices extends EntityBase implements \JsonSerializable
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
	 * @Column(type="string", length=255)
	 * @ImportMap Office_ID
	 * @ImportRequired
	 * @ImportExists Office
	 */
	protected $officeID;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Office_Phone
	 * @ImportPhone
	 */
	protected $phone;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Office_Fax
	 * @ImportPhone
	 */
	protected $fax;
	
	/**
	 * @Column(type="date",nullable=true)
	 */
	protected $created = null;
	
	/**
	 * @Column(type="date",nullable=true)
	 */
	protected $lastUpdated = null;

    /**
     * Get id
     *
     * @return int
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
     * @return ProviderOffices
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
     * Set officeID
     *
     * @param string $officeID
     *
     * @return ProviderOffices
     */
    public function setOfficeID($officeID)
    {
        $this->officeID = $officeID;

        return $this;
    }

    /**
     * Get officeID
     *
     * @return string
     */
    public function getOfficeID()
    {
        return $this->officeID;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return ProviderOffices
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set fax
     *
     * @param string $fax
     *
     * @return ProviderOffices
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return ProviderOffices
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
     * @return ProviderOffices
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
