<?php

namespace MongoDocs;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ODM\Document(collection="trigger_defs")
 */
class TriggerDef extends BaseDocument
{
	/** @ODM\Id */
	private $id;

	/** @ODM\Field(type="string") */
	private $name;

	/** @ODM\Field(type="string") */
	private $machineName;

	/** @ODM\ReferenceOne(targetDocument="Group", simple=true) */
	private $group;

	/** @ODM\Field(type="string") */
	private $type;

	/** @var  @ODM\Field(type="hash") */
	private $options;
    
    /**
     * TriggerDef constructor.
     */
	public function __construct() {
		$this->options = new ArrayCollection();
	}
    
    /**
     * Overridden from parent to handle the group.
     *
     * @return array
     */
    public function jsonSerialize() {
        $user = array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'machineName' => $this->getMachineName(),
            'type' => $this->getType(),
            'options' => $this->getOptions(),
            'group' => null
        );
        
        if ($this->getGroup() !== null) {
            $user['group'] = array(
                'id' => $this->group->getId(),
                'name' => $this->group->getName(),
                'machineName' => $this->group->getMachineName(),
                'domains' => $this->group->getDomains()
            );
        }
        
        return $user;
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set machineName
     *
     * @param string $machineName
     * @return self
     */
    public function setMachineName($machineName)
    {
        $this->machineName = $machineName;
        return $this;
    }

    /**
     * Get machineName
     *
     * @return string $machineName
     */
    public function getMachineName()
    {
        return $this->machineName;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set options
     *
     * @param ArrayCollection $options
     * @return self
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get options
     *
     * @return ArrayCollection $options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set group
     *
     * @param \MongoDocs\Group $group
     * @return self
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Get group
     *
     * @return \MongoDocs\Group $group
     */
    public function getGroup()
    {
        return $this->group;
    }
}
