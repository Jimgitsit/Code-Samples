<?php

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;

/**
 * @Entity
 * @Table(
 *     name="businesses"
 * )
 */

class Business extends EntityBase implements \JsonSerializable {
	
	/**
	 * @Id
	 * @Column(type="integer")
	 * @ImportMap biz_ID
	 */
	protected $businessID;
	
	/**
	 * @Column(type="integer")
	 * @ImportMap Biz_ID_parent
	 */
	protected $parentBusinessID = 0;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Cost_Center
	 */
	protected $costCenter;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Process_Level
	 */
	protected $processLevel;
	
	/**
	 * @Column(type="text")
	 * @ImportMap Property_Reports_to
	 */
	protected $propertyReportsTo = '';
	
	/**
	 * @Column(type="text")
	 * @ImportMap Property_Location
	 */
	protected $propertyLocation = '';
	
	/**
	 * @Column(type="text")
	 * @ImportMap biz_type
	 */
	protected $type;
	
	/**
	 * @Column(type="text")
	 * @ImportMap biz_name
	 */
	protected $name;
	
	/**
	 * @Column(type="text")
	 */
	protected $displayName;
	
	/**
	 * @Column(type="text")
	 * @ImportMap DirectorID
	 */
	protected $directorID;
	
	/**
	 * @Column(type="text")
	 * @ImportMap VPID
	 */
	protected $vpID;
	
	/**
	 * @Column(type="text")
	 * @ImportMap property
	 */
	protected $property = '';
	
	/**
	 * @Column(type="text")
	 * @ImportMap directions
	 */
	protected $directions = '';
	
	/**
	 * @Column(type="text")
	 * @ImportMap intranet_url
	 */
	protected $intranetURL = '';
	
	/**
	 * @Column(type="text")
	 * @ImportMap web_url
	 */
	protected $webURL = '';
	
	/**
	 * @Column(type="text")
	 * @ImportMap hours
	 */
	protected $hours = '';
	
	/**
	 * @Column(type="text")
	 * @ImportMap promo_line
	 */
	protected $promoLine = '';
	
	/**
	 * @Column(type="date")
	 * @ImportMap created
	 */
	protected $created;
	
	/**
	 * @Column(type="date")
	 * @ImportMap last_updated
	 */
	protected $lastUpdated;
	
	/**
	 * @Column(type="boolean")
	 * @ImportMap isBlind
	 */
	protected $isBlind;
	
	/**
	 * @Column(type="boolean")
	 * @ImportMap isActive
	 */
	protected $isActive;
	
	/**
	 * @Column(type="boolean")
	 * @ImportMap isNew
	 */
	protected $isNew = false;
	
	/**
	 * @Column(type="text")
	 * @ImportMap public_website
	 */
	protected $publicWebsite = 'No';
	
	/**
	 * @Column(type="text")
	 * @ImportMap employee_Portal
	 */
	protected $employeePortal = 'No';
	
	/**
	 * @Column(type="text")
	 * @ImportMap midasID
	 */
	protected $midasID = '';
	
	/**
	 * @Column(type="text")
	 * @ImportMap source
	 */
	protected $source = '';
	
	protected static $maxBusinessID;
	
	/**
	 * Returns a new business id which will be the max businessID + 1
	 * 
	 * @param $em \Doctrine\ORM\EntityManager
	 *
	 * @return mixed
	 */
	public static function getNewBusinessID($em) {
		if (self::$maxBusinessID == null) {
			self::$maxBusinessID = $em->createQueryBuilder()->select("MAX(b.businessID)")
				->from('Business', 'b')
				->setMaxResults(1)
				->getQuery()
				->getSingleScalarResult();
		}
		
		self::$maxBusinessID++;
		return self::$maxBusinessID;
	}

    /**
     * Set businessID
     *
     * @param int $businessID
     *
     * @return Business
     */
    public function setBusinessID($businessID)
    {
        $this->businessID = $businessID;

        return $this;
    }

    /**
     * Get businessID
     *
     * @return int
     */
    public function getBusinessID()
    {
        return $this->businessID;
    }

    /**
     * Set parentBusinessID
     *
     * @param int $parentBusinessID
     *
     * @return Business
     */
    public function setParentBusinessID($parentBusinessID)
    {
        $this->parentBusinessID = $parentBusinessID;

        return $this;
    }

    /**
     * Get parentBusinessID
     *
     * @return int
     */
    public function getParentBusinessID()
    {
        return $this->parentBusinessID;
    }

    /**
     * Set costCenter
     *
     * @param string $costCenter
     *
     * @return Business
     */
    public function setCostCenter($costCenter)
    {
        $this->costCenter = $costCenter;

        return $this;
    }

    /**
     * Get costCenter
     *
     * @return string
     */
    public function getCostCenter()
    {
        return $this->costCenter;
    }

    /**
     * Set processLevel
     *
     * @param string $processLevel
     *
     * @return Business
     */
    public function setProcessLevel($processLevel)
    {
        $this->processLevel = $processLevel;

        return $this;
    }

    /**
     * Get processLevel
     *
     * @return string
     */
    public function getProcessLevel()
    {
        return $this->processLevel;
    }

    /**
     * Set propertyReportsTo
     *
     * @param string $propertyReportsTo
     *
     * @return Business
     */
    public function setPropertyReportsTo($propertyReportsTo)
    {
        $this->propertyReportsTo = $propertyReportsTo;

        return $this;
    }

    /**
     * Get propertyReportsTo
     *
     * @return string
     */
    public function getPropertyReportsTo()
    {
        return $this->propertyReportsTo;
    }

    /**
     * Set propertyLocation
     *
     * @param string $propertyLocation
     *
     * @return Business
     */
    public function setPropertyLocation($propertyLocation)
    {
        $this->propertyLocation = $propertyLocation;

        return $this;
    }

    /**
     * Get propertyLocation
     *
     * @return string
     */
    public function getPropertyLocation()
    {
        return $this->propertyLocation;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Business
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
     * Set name
     *
     * @param string $name
     *
     * @return Business
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
     * Set directorID
     *
     * @param string $directorID
     *
     * @return Business
     */
    public function setDirectorID($directorID)
    {
        $this->directorID = $directorID;

        return $this;
    }

    /**
     * Get directorID
     *
     * @return string
     */
    public function getDirectorID()
    {
        return $this->directorID;
    }

    /**
     * Set vpID
     *
     * @param string $vpID
     *
     * @return Business
     */
    public function setVpID($vpID)
    {
        $this->vpID = $vpID;

        return $this;
    }

    /**
     * Get vpID
     *
     * @return string
     */
    public function getVpID()
    {
        return $this->vpID;
    }

    /**
     * Set property
     *
     * @param string $property
     *
     * @return Business
     */
    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * Get property
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Set directions
     *
     * @param string $directions
     *
     * @return Business
     */
    public function setDirections($directions)
    {
        $this->directions = $directions;

        return $this;
    }

    /**
     * Get directions
     *
     * @return string
     */
    public function getDirections()
    {
        return $this->directions;
    }

    /**
     * Set intranetURL
     *
     * @param string $intranetURL
     *
     * @return Business
     */
    public function setIntranetURL($intranetURL)
    {
        $this->intranetURL = $intranetURL;

        return $this;
    }

    /**
     * Get intranetURL
     *
     * @return string
     */
    public function getIntranetURL()
    {
        return $this->intranetURL;
    }

    /**
     * Set webURL
     *
     * @param string $webURL
     *
     * @return Business
     */
    public function setWebURL($webURL)
    {
        $this->webURL = $webURL;

        return $this;
    }

    /**
     * Get webURL
     *
     * @return string
     */
    public function getWebURL()
    {
        return $this->webURL;
    }

    /**
     * Set hours
     *
     * @param string $hours
     *
     * @return Business
     */
    public function setHours($hours)
    {
        $this->hours = $hours;

        return $this;
    }

    /**
     * Get hours
     *
     * @return string
     */
    public function getHours()
    {
        return $this->hours;
    }

    /**
     * Set promoLine
     *
     * @param string $promoLine
     *
     * @return Business
     */
    public function setPromoLine($promoLine)
    {
        $this->promoLine = $promoLine;

        return $this;
    }

    /**
     * Get promoLine
     *
     * @return string
     */
    public function getPromoLine()
    {
        return $this->promoLine;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Business
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
     * @return Business
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

    /**
     * Set isBlind
     *
     * @param bool $isBlind
     *
     * @return Business
     */
    public function setIsBlind($isBlind)
    {
        $this->isBlind = $isBlind;

        return $this;
    }

    /**
     * Get isBlind
     *
     * @return bool
     */
    public function getIsBlind()
    {
        return $this->isBlind;
    }

    /**
     * Set isActive
     *
     * @param bool $isActive
     *
     * @return Business
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set publicWebsite
     *
     * @param string $publicWebsite
     *
     * @return Business
     */
    public function setPublicWebsite($publicWebsite)
    {
	    if ($publicWebsite === true) {
		    $publicWebsite = 'Yes';
	    }
	
	    if ($publicWebsite === false) {
		    $publicWebsite = 'No';
	    }
	    
        $this->publicWebsite = $publicWebsite;

        return $this;
    }

    /**
     * Get publicWebsite
     *
     * @return string
     */
    public function getPublicWebsite()
    {
        return $this->publicWebsite;
    }

    /**
     * Set employeePortal
     *
     * @param string $employeePortal
     *
     * @return Business
     */
    public function setEmployeePortal($employeePortal)
    {
	    if ($employeePortal === true) {
		    $employeePortal = 'Yes';
	    }
	    
	    if ($employeePortal === false) {
		    $employeePortal = 'No';
	    }
	    
        $this->employeePortal = $employeePortal;

        return $this;
    }

    /**
     * Get employeePortal
     *
     * @return string
     */
    public function getEmployeePortal()
    {
        return $this->employeePortal;
    }

    /**
     * Set midasID
     *
     * @param string $midasID
     *
     * @return Business
     */
    public function setMidasID($midasID)
    {
        $this->midasID = $midasID;

        return $this;
    }

    /**
     * Get midasID
     *
     * @return string
     */
    public function getMidasID()
    {
        return $this->midasID;
    }

    /**
     * Set source
     *
     * @param string $source
     *
     * @return Business
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set isNew
     *
     * @param bool $isNew
     *
     * @return Business
     */
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;

        return $this;
    }

    /**
     * Get isNew
     *
     * @return bool
     */
    public function getIsNew()
    {
        return $this->isNew;
    }

    /**
     * Set displayName
     *
     * @param string $displayName
     *
     * @return Business
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Get displayName
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }
}
