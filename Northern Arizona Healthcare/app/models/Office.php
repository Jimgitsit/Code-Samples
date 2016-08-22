<?php

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;

/**
 * @Entity
 * @Table(
 *     name="offices"
 * )
 */
class Office extends EntityBase implements \JsonSerializable
{
	/**
	 * @Id
	 * @Column(type="string", length=255)
	 * @ImportMap Office_OfficeID
	 */
	protected $officeID;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Office_Name
	 * @ImportRequired
	 */
	protected $name;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Office_Address_1
	 */
	protected $address1;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Office_Address_2
	 */
	protected $address2;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Office_City
	 */
	protected $city;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Office_State
	 */
	protected $state;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Office_Zip
	 */
	protected $zip;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Office_URL
	 */
	protected $url;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Office_Email
	 */
	protected $email;
	
	/**
	 * @Column(type="date",nullable=true)
	 */
	protected $created = null;
	
	/**
	 * @Column(type="date",nullable=true)
	 */
	protected $lastUpdated = null;

    /**
     * Set officeID
     *
     * @param string $officeID
     *
     * @return Office
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
     * Set name
     *
     * @param string $name
     *
     * @return Office
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
     * Set address1
     *
     * @param string $address1
     *
     * @return Office
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * Get address1
     *
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Set address2
     *
     * @param string $address2
     *
     * @return Office
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get address2
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Office
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
     * @return Office
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
     * Set zip
     *
     * @param string $zip
     *
     * @return Office
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Office
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Office
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Office
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
     * @return Office
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
