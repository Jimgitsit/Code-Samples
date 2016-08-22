<?php

namespace MongoDocs;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ODM\Document(collection="groups")
 */
class Group extends BaseDocument
{
	/** @ODM\Id */
	private $id;

	/** @ODM\Field(type="string") */
	private $name;

	/** @ODM\Field(type="string") */
	private $machineName;
	
	/** 
	 * @var array 
	 * @ODM\Field(type="collection")
	 */
	private $domains = array();

	/**
	 * @var ArrayCollection
	 * @ODM\ReferenceMany(targetDocument="User", cascade="all", simple=true)
	 */
	private $users;
	
	/**
	 * Group constructor.
	 */
	public function __construct()
	{
		$this->users = new ArrayCollection();
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
	 *
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
	 * Add user
	 *
	 * @param \MongoDocs\User $user
	 */
	public function addUser(User $user)
	{
		$this->users[] = $user;
	}

	/**
	 * Remove user
	 *
	 * @param \MongoDocs\User $user
	 */
	public function removeUser(User $user)
	{
		$this->users->removeElement($user);
	}

	/**
	 * Get users
	 *
	 * @return ArrayCollection $users
	 */
	public function getUsers()
	{
		return $this->users;
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
     * Set domains
     *
     * @param ODM\collection $domains
     * @return self
     */
    public function setDomains($domains)
    {
        $this->domains = $domains;
        return $this;
    }

    /**
     * Get domains
     *
     * @return ODM\Collection $domains
     */
    public function getDomains()
    {
        return $this->domains;
    }

	/**
	 * Adds a domain
	 * 
	 * @param $domain
	 *
	 * @return $this
	 */
	public function addDomain($domain) {
		$this->domains[] = $domain;
		return $this;
	}

	/**
	 * Removes a domain
	 * 
	 * @param $domain
	 *
	 * @return $this
	 */
	public function removeDomain($domain) {
		$domains = array();
		foreach ($this->domains as $d) {
			if ($d != $domain) {
				$domains[] = $domain;
			}
		}
		$this->domains = $domains;
		return $this;
	}
}
