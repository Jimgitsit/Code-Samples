<?php

namespace MongoDocs;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="content")
 */
class Content extends BaseDocument
{
	/** @ODM\Id */
	private $id;

	/** 
	 * @ODM\Field(type="date")
	 * 
	 * The date this content was created.
	 * TODO: Rename this to creationDate 
	 */
	private $date;
	
	/** @ODM\Field(type="string") */
	private $status;

	/** @ODM\Field(type="boolean") */
	private $matchAllTriggers;

	/** 
	 * @ODM\Field(type="boolean") 
	 * 
	 * By default triggers are compared with = 
	 * If this property is true they will be compared with !=
	 */
	private $notEqualTo;
	
	/** @ODM\Field(type="boolean") */
	private $global;

	/** @var  @ODM\Field(type="string") */
	private $content;

	/** @ODM\Field(type="string") */
	private $url;

	/** @ODM\Field(type="string") */
	private $imageUrl;

	/** @ODM\Field(type="boolean") */
	private $hidden;

	/** @ODM\ReferenceMany(targetDocument="Trigger", cascade="all", simple=true) */
	private $triggers;

	/** @ODM\ReferenceOne(targetDocument="User", simple=true) */
	private $user;
	
	/**
	 * Content constructor.
	 */
	public function __construct()
	{
		// Defaults for new records
		$this->triggers = new \Doctrine\Common\Collections\ArrayCollection();
		$this->matchAllTriggers = true;
		$this->global = false;
		$this->notEqualTo = false;
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
	 * Set date
	 *
	 * @param \DateTime $date
	 *
	 * @return self
	 */
	public function setDate($date)
	{
		$this->date = $date;

		return $this;
	}

	/**
	 * Get date
	 *
	 * @return \DateTime $date
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * Set content
	 *
	 * @param string $content
	 *
	 * @return self
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	/**
	 * Get content
	 *
	 * @return string $content
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Set user
	 *
	 * @param \MongoDocs\User $user
	 *
	 * @return self
	 */
	public function setUser(\MongoDocs\User $user)
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * Get user
	 *
	 * @return \MongoDocs\User $user
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Set hidden
	 *
	 * @param boolean $hidden
	 *
	 * @return self
	 */
	public function setHidden($hidden)
	{
		$this->hidden = $hidden;

		return $this;
	}

	/**
	 * Get hidden
	 *
	 * @return boolean $hidden
	 */
	public function getHidden()
	{
		return $this->hidden;
	}

	/**
	 * Add trigger
	 *
	 * @param \MongoDocs\Trigger $trigger
	 */
	public function addTrigger(\MongoDocs\Trigger $trigger)
	{
		$this->triggers[] = $trigger;
	}

	/**
	 * Remove trigger
	 *
	 * @param \MongoDocs\Trigger $trigger
	 */
	public function removeTrigger(\MongoDocs\Trigger $trigger)
	{
		$this->triggers->removeElement($trigger);
	}

	/**
	 * Get triggers
	 *
	 * @return \Doctrine\Common\Collections\Collection $triggers
	 */
	public function getTriggers()
	{
		return $this->triggers;
	}

	/**
	 * Set url
	 *
	 * @param string $url
	 * @return self
	 */
	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
	}

	/**
	 * Get url
	 *
	 * @return string $url
	 */
	public function getUrl()
	{
		return $this->url;
	}

    /**
     * Set imageUrl
     *
     * @param string $imageUrl
     * @return self
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    /**
     * Get imageUrl
     *
     * @return string $imageUrl
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set matchAllTriggers
     *
     * @param boolean $matchAllTriggers
     * @return self
     */
    public function setMatchAllTriggers($matchAllTriggers)
    {
        $this->matchAllTriggers = $matchAllTriggers;
        return $this;
    }

    /**
     * Get matchAllTriggers
     *
     * @return boolean $matchAllTriggers
     */
    public function getMatchAllTriggers()
    {
        return $this->matchAllTriggers === null? true: $this->matchAllTriggers;
    }

    /**
     * Set global
     *
     * @param boolean $global
     * @return self
     */
    public function setGlobal($global)
    {
        $this->global = $global;
        return $this;
    }

    /**
     * Get global
     *
     * @return boolean $global
     */
    public function getGlobal()
    {
        return $this->global;
    }

    /**
     * Set notEqualTo
     *
     * @param boolean $notEqualTo
     * @return self
     */
    public function setNotEqualTo($notEqualTo)
    {
        $this->notEqualTo = $notEqualTo;
        return $this;
    }

    /**
     * Get notEqualTo
     *
     * @return boolean $notEqualTo
     */
    public function getNotEqualTo()
    {
        return $this->notEqualTo;
    }
}
