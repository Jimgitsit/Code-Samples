<?php

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;

require_once('EntityBase.php');

/**
 * @Entity
 * @Table(
 *     name="providers"
 * )
 */
class Provider extends EntityBase implements \JsonSerializable
{
	/**
	 * @Id
	 * @Column(type="string", length=255)
	 * @ImportMap Provider_NPI
	 */
	protected $providerNPI;
	
	/**
	 * @Column(type="string", length=255, nullable=true)
	 * @ImportMap EMPLOYEE
	 * @ImportRequired
	 */
	protected $employeeID;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Provider_Last_Name
	 * @ImportRequired
	 */
	protected $lastName;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Provider_First_Name
	 */
	protected $firstName;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Provider_Middle_Name
	 */
	protected $middleName;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Provider_Suffix
	 */
	protected $suffix;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Provider_Specialty1
	 */
	protected $specialty1;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Provider_Specialty2
	 */
	protected $specialty2;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Provider_Specialty3
	 */
	protected $specialty3;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Provider_Cell_Phone_Number
	 * @ImportPhone
	 */
	protected $cellPhoneNumber;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Provider_Primary_Email
	 */
	protected $primaryEmail;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Provider_Sex
	 */
	protected $sex;
	
	/**
	 * @Column(type="date",nullable=true)
	 */
	protected $created = null;
	
	/**
	 * @Column(type="date",nullable=true)
	 */
	protected $lastUpdated = null;

    /**
     * Set providerNPI
     *
     * @param string $providerNPI
     *
     * @return Provider
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
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Provider
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Provider
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set middleName
     *
     * @param string $middleName
     *
     * @return Provider
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * Get middleName
     *
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * Set suffix
     *
     * @param string $suffix
     *
     * @return Provider
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Get suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Set specialty1
     *
     * @param string $specialty1
     *
     * @return Provider
     */
    public function setSpecialty1($specialty1)
    {
        $this->specialty1 = $specialty1;

        return $this;
    }

    /**
     * Get specialty1
     *
     * @return string
     */
    public function getSpecialty1()
    {
        return $this->specialty1;
    }

    /**
     * Set specialty2
     *
     * @param string $specialty2
     *
     * @return Provider
     */
    public function setSpecialty2($specialty2)
    {
        $this->specialty2 = $specialty2;

        return $this;
    }

    /**
     * Get specialty2
     *
     * @return string
     */
    public function getSpecialty2()
    {
        return $this->specialty2;
    }

    /**
     * Set specialty3
     *
     * @param string $specialty3
     *
     * @return Provider
     */
    public function setSpecialty3($specialty3)
    {
        $this->specialty3 = $specialty3;

        return $this;
    }

    /**
     * Get specialty3
     *
     * @return string
     */
    public function getSpecialty3()
    {
        return $this->specialty3;
    }

    /**
     * Set cellPhoneNumber
     *
     * @param string $cellPhoneNumber
     *
     * @return Provider
     */
    public function setCellPhoneNumber($cellPhoneNumber)
    {
        $this->cellPhoneNumber = $cellPhoneNumber;

        return $this;
    }

    /**
     * Get cellPhoneNumber
     *
     * @return string
     */
    public function getCellPhoneNumber()
    {
        return $this->cellPhoneNumber;
    }

    /**
     * Set primaryEmail
     *
     * @param string $primaryEmail
     *
     * @return Provider
     */
    public function setPrimaryEmail($primaryEmail)
    {
        $this->primaryEmail = $primaryEmail;

        return $this;
    }

    /**
     * Get primaryEmail
     *
     * @return string
     */
    public function getPrimaryEmail()
    {
        return $this->primaryEmail;
    }

    /**
     * Set sex
     *
     * @param string $sex
     *
     * @return Provider
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * Get sex
     *
     * @return string
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set employeeID
     *
     * @param string $employeeID
     *
     * @return Provider
     */
    public function setEmployeeID($employeeID)
    {
        $this->employeeID = $employeeID;

        return $this;
    }

    /**
     * Get employeeID
     *
     * @return string
     */
    public function getEmployeeID()
    {
        return $this->employeeID;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Provider
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
     * @return Provider
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
