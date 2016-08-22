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
 *     name="provider_appointment"
 * )
 */
class ProviderAppointment extends EntityBase implements \JsonSerializable
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
	 * @ImportMap Appointment_Status
	 */
	protected $status;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Appointment_Organization
	 * @ImportIgnoreValues The Ambulatory Surgery Center at FMC@, Managed Care
	 */
	protected $organization;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Appointment_Staff_Type
	 */
	protected $staffType;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Appointment_Staff_Category
	 */
	protected $staffCategory;
	
	/**
	 * @Column(type="date")
	 * @ImportMap Appointment_Cat_Staff_Start_Date
	 */
	protected $catStaffStartDate;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Appointment_Department
	 */
	protected $department;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Appointment_Primary_Mailing_Office_Name
	 */
	protected $primaryMailingOfficeName;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Appointment_Primary_OfficeID
	 */
	protected $primaryOfficeID;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Appointment_Mailing_OfficeID
	 */
	protected $mailingOfficeID;
	
	/**
	 * @Column(type="date")
	 * @ImportMap Appointment_Initial_Appointment
	 */
	protected $initialAppointment;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Appointment_IsEmployed
	 */
	protected $isEmployed;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Appointment_Exclude_Directory
	 */
	protected $excludeDirectory;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Appointment_LawsonID
	 */
	protected $lawsonID;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Appointment_OnStaff
	 */
	protected $onStaff;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Appointment_Archived
	 */
	protected $archived;
	
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
     * @return ProviderAppointment
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
     * @return ProviderAppointment
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
     * Set status
     *
     * @param string $status
     *
     * @return ProviderAppointment
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set organization
     *
     * @param string $organization
     *
     * @return ProviderAppointment
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set staffType
     *
     * @param string $staffType
     *
     * @return ProviderAppointment
     */
    public function setStaffType($staffType)
    {
        $this->staffType = $staffType;

        return $this;
    }

    /**
     * Get staffType
     *
     * @return string
     */
    public function getStaffType()
    {
        return $this->staffType;
    }

    /**
     * Set staffCategory
     *
     * @param string $staffCategory
     *
     * @return ProviderAppointment
     */
    public function setStaffCategory($staffCategory)
    {
        $this->staffCategory = $staffCategory;

        return $this;
    }

    /**
     * Get staffCategory
     *
     * @return string
     */
    public function getStaffCategory()
    {
        return $this->staffCategory;
    }

    /**
     * Set catStaffStartDate
     *
     * @param string $catStaffStartDate
     *
     * @return ProviderAppointment
     */
    public function setCatStaffStartDate($catStaffStartDate)
    {
        $this->catStaffStartDate = $catStaffStartDate;

        return $this;
    }

    /**
     * Get catStaffStartDate
     *
     * @return string
     */
    public function getCatStaffStartDate()
    {
        return $this->catStaffStartDate;
    }

    /**
     * Set department
     *
     * @param string $department
     *
     * @return ProviderAppointment
     */
    public function setDepartment($department)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department
     *
     * @return string
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set primaryMailingOfficeName
     *
     * @param string $primaryMailingOfficeName
     *
     * @return ProviderAppointment
     */
    public function setPrimaryMailingOfficeName($primaryMailingOfficeName)
    {
        $this->primaryMailingOfficeName = $primaryMailingOfficeName;

        return $this;
    }

    /**
     * Get primaryMailingOfficeName
     *
     * @return string
     */
    public function getPrimaryMailingOfficeName()
    {
        return $this->primaryMailingOfficeName;
    }

    /**
     * Set primaryOfficeID
     *
     * @param string $primaryOfficeID
     *
     * @return ProviderAppointment
     */
    public function setPrimaryOfficeID($primaryOfficeID)
    {
        $this->primaryOfficeID = $primaryOfficeID;

        return $this;
    }

    /**
     * Get primaryOfficeID
     *
     * @return string
     */
    public function getPrimaryOfficeID()
    {
        return $this->primaryOfficeID;
    }

    /**
     * Set mailingOfficeID
     *
     * @param string $mailingOfficeID
     *
     * @return ProviderAppointment
     */
    public function setMailingOfficeID($mailingOfficeID)
    {
        $this->mailingOfficeID = $mailingOfficeID;

        return $this;
    }

    /**
     * Get mailingOfficeID
     *
     * @return string
     */
    public function getMailingOfficeID()
    {
        return $this->mailingOfficeID;
    }

    /**
     * Set initialAppointment
     *
     * @param \DateTime $initialAppointment
     *
     * @return ProviderAppointment
     */
    public function setInitialAppointment($initialAppointment)
    {
        $this->initialAppointment = $initialAppointment;

        return $this;
    }

    /**
     * Get initialAppointment
     *
     * @return \DateTime
     */
    public function getInitialAppointment()
    {
        return $this->initialAppointment;
    }

    /**
     * Set isEmployed
     *
     * @param string $isEmployed
     *
     * @return ProviderAppointment
     */
    public function setIsEmployed($isEmployed)
    {
        $this->isEmployed = $isEmployed;

        return $this;
    }

    /**
     * Get isEmployed
     *
     * @return string
     */
    public function getIsEmployed()
    {
        return $this->isEmployed;
    }

    /**
     * Set excludeDirectory
     *
     * @param string $excludeDirectory
     *
     * @return ProviderAppointment
     */
    public function setExcludeDirectory($excludeDirectory)
    {
        $this->excludeDirectory = $excludeDirectory;

        return $this;
    }

    /**
     * Get excludeDirectory
     *
     * @return string
     */
    public function getExcludeDirectory()
    {
        return $this->excludeDirectory;
    }

    /**
     * Set lawsonID
     *
     * @param string $lawsonID
     *
     * @return ProviderAppointment
     */
    public function setLawsonID($lawsonID)
    {
        $this->lawsonID = $lawsonID;

        return $this;
    }

    /**
     * Get lawsonID
     *
     * @return string
     */
    public function getLawsonID()
    {
        return $this->lawsonID;
    }

    /**
     * Set onStaff
     *
     * @param string $onStaff
     *
     * @return ProviderAppointment
     */
    public function setOnStaff($onStaff)
    {
        $this->onStaff = $onStaff;

        return $this;
    }

    /**
     * Get onStaff
     *
     * @return string
     */
    public function getOnStaff()
    {
        return $this->onStaff;
    }

    /**
     * Set archived
     *
     * @param string $archived
     *
     * @return ProviderAppointment
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;

        return $this;
    }

    /**
     * Get archived
     *
     * @return string
     */
    public function getArchived()
    {
        return $this->archived;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return ProviderAppointment
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
     * @return ProviderAppointment
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
