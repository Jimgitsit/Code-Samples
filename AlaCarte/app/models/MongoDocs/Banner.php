<?php

namespace MongoDocs;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="banners")
 */
class Banner {
	/** @ODM\Id */
	private $id;
	
	/** @ODM\String */
	private $alacarteId;

	/** @ODM\String */
	private $path;

	/** @ODM\String */
	private $groupName;

	/** @ODM\String */
	private $imageUrl;

	/** @ODM\String */
	private $linkUrl;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set alacarteId
     *
     * @param string $alacarteId
     * @return self
     */
    public function setAlacarteId($alacarteId)
    {
        $this->alacarteId = $alacarteId;
        return $this;
    }

    /**
     * Get alacarteId
     *
     * @return string $alacarteId
     */
    public function getAlacarteId()
    {
        return $this->alacarteId;
    }

    /**
     * Set imagUrl
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
     * Get imagUrl
     *
     * @return string $imagUrl
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * Set linkUrl
     *
     * @param string $linkUrl
     * @return self
     */
    public function setLinkUrl($linkUrl)
    {
        $this->linkUrl = $linkUrl;
        return $this;
    }

    /**
     * Get linkUrl
     *
     * @return string $linkUrl
     */
    public function getLinkUrl()
    {
        return $this->linkUrl;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get path
     *
     * @return string $path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set subDomain
     *
     * @param string $groupName
     * 
	 * @return self
     */
    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;
        return $this;
    }

    /**
     * Get subDomain
     *
     * @return string $subDomain
     */
    public function getGroupName()
    {
        return $this->groupName;
    }
}
