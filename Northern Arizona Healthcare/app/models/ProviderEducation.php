<?php

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity
 * @Table(
 *     name="provider_education"
 * )
 */
class ProviderEducation extends EntityBase implements \JsonSerializable
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
	 * @ImportMap Education_Type
	 */
	protected $type;
	
	/**
	 * @Column(type="date")
	 * @ImportMap Education_Date
	 */
	protected $date;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Education_Location
	 */
	protected $location;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Education_Specialty
	 */
	protected $specialty;
	
	/**
	 * @Column(type="date")
	 * @ImportMap Education_StartDate
	 */
	protected $startDate;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Education_City
	 */
	protected $city;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Education_State
	 */
	protected $state;
	
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
     * @return ProviderEducation
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
     * @return ProviderEducation
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
     * Set type
     *
     * @param string $type
     *
     * @return ProviderEducation
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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return ProviderEducation
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set location
     *
     * @param string $location
     *
     * @return ProviderEducation
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set specialty
     *
     * @param string $specialty
     *
     * @return ProviderEducation
     */
    public function setSpecialty($specialty)
    {
        $this->specialty = $specialty;

        return $this;
    }

    /**
     * Get specialty
     *
     * @return string
     */
    public function getSpecialty()
    {
        return $this->specialty;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return ProviderEducation
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return ProviderEducation
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return ProviderEducation
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return ProviderEducation
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
     * @return ProviderEducation
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
