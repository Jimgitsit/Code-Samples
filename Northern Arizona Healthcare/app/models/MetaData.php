<?php

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity
 * @Table(
 *     name="meta_data"
 * )
 */
class MetaData extends EntityBase implements \JsonSerializable
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @Column(type="text")
	 * @ImportMap user_type
	 * @ImportRequired
	 */
	protected $type;
	
	/**
	 * @Column(type="text")
	 * @ImportMap user_type_id
	 * @ImportRequired
	 */
	protected $typeID;
	
	/**
	 * @Column(type="text")
	 * @ImportMap source
	 */
	protected $source;
	
	/**
	 * @Column(type="text")
	 * @ImportMap label
	 */
	protected $label = '';
	
	/**
	 * @Column(type="json_array")
	 * @ImportMap value
	 */
	protected $value;
	
	/**
	 * @Column(type="text")
	 * @ImportMap audience
	 */
	protected $audience;
	
	/**
	 * @Column(type="text")
	 * @ImportMap communication_type
	 * @ImportRequired
	 */
	protected $valueType;
	
	/**
	 * @Column(type="text")
	 * @ImportMap communication_type_label
	 */
	protected $valueSubType = '';
	
	/**
	 * @Column(type="integer")
	 * @ImportMap value_order
	 */
	protected $valueOrder = 0;
	
	/**
	 * @Column(type="boolean")
	 * @ImportMap is_Active
	 */
	protected $isActive = true;
	
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
     * Set id
     *
     * @param int $id
     *
     * @return MetaData
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Set type
     *
     * @param string $type
     *
     * @return MetaData
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
     * Set typeID
     *
     * @param string $typeID
     *
     * @return MetaData
     */
    public function setTypeID($typeID)
    {
        $this->typeID = $typeID;

        return $this;
    }

    /**
     * Get typeID
     *
     * @return string
     */
    public function getTypeID()
    {
        return $this->typeID;
    }

    /**
     * Set source
     *
     * @param string $source
     *
     * @return MetaData
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
     * Set label
     *
     * @param string $label
     *
     * @return MetaData
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return MetaData
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set audience
     *
     * @param string $audience
     *
     * @return MetaData
     */
    public function setAudience($audience)
    {
        $this->audience = $audience;

        return $this;
    }

    /**
     * Get audience
     *
     * @return string
     */
    public function getAudience()
    {
        return $this->audience;
    }

    /**
     * Set valueType
     *
     * @param string $valueType
     *
     * @return MetaData
     */
    public function setValueType($valueType)
    {
        $this->valueType = $valueType;

        return $this;
    }

    /**
     * Get valueType
     *
     * @return string
     */
    public function getValueType()
    {
        return $this->valueType;
    }

    /**
     * Set valueSubType
     *
     * @param string $valueSubType
     *
     * @return MetaData
     */
    public function setValueSubType($valueSubType)
    {
        $this->valueSubType = $valueSubType;

        return $this;
    }

    /**
     * Get valueSubType
     *
     * @return string
     */
    public function getValueSubType()
    {
        return $this->valueSubType;
    }

    /**
     * Set valueOrder
     *
     * @param int $valueOrder
     *
     * @return MetaData
     */
    public function setValueOrder($valueOrder)
    {
        $this->valueOrder = $valueOrder;

        return $this;
    }

    /**
     * Get valueOrder
     *
     * @return int
     */
    public function getValueOrder()
    {
        return $this->valueOrder;
    }

    /**
     * Set isActive
     *
     * @param bool $isActive
     *
     * @return MetaData
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return MetaData
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
     * @return MetaData
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
