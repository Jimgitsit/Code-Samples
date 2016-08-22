<?php

namespace MongoDocs;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="nodes")
 */
class Node extends BaseDocument
{
	const TYPE_TEXT = 'text';
	const TYPE_LINK = 'link';
	const TYPE_BLOCK = 'block';
	const TYPE_ORBIT_BANNER = 'orbit-banner';

	/**
	 * @var array Content types
	 */
	public static $CONTENT_TYPES = array(
		self::TYPE_TEXT,
		self::TYPE_BLOCK,
		self::TYPE_LINK,
		self::TYPE_ORBIT_BANNER
	);
	
	/** @ODM\Id */
	private $id;
	
	/** @ODM\Field(type="string") @ODM\UniqueIndex */
	private $alacarteId;

	/** @ODM\Field(type="string") */
	private $path;

	/** @ODM\Field(type="string") */
	private $groupName; // TODO: convert this to a reference to MongoDocs\Group

	/** @ODM\Field(type="string") */
	private $contentType;

	/** @ODM\ReferenceMany(targetDocument="Content", cascade="all", simple=true) */
	private $content;
	
	public function __construct()
	{
		$this->content = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * Returns the first Content record that matches the any of the given triggers.
	 * By default only returns active content but this can be overridden with the status
	 * parameter.
	 * 
	 * @param $reqTriggers
	 * @param $status
	 * @param $ignoreNotEqualTo Boolean If true then Content::notEqualTo will be ignored.
	 *
	 * @return array|null
	 */
	public function matchContent($reqTriggers, $status = 'live', $ignoreNotEqualTo) {
		$match = false;
		$curContent = array();
		foreach ($this->content as $content) {
			if ((is_array($status) && in_array($content->getStatus(), $status)) || $content->getStatus() == $status) {
				foreach ($content->getTriggers() as $trigger) {
					$notEqualTo = $content->getNotEqualTo();
					if ($ignoreNotEqualTo) {
						$notEqualTo = false;
					}
					
					$triggerMatch = $this->matchTrigger($reqTriggers, $trigger, $notEqualTo);
					
					// $triggerMatch == true and $notEqualTo == false -> match
					// $triggerMatch == false and $notEqualTo == true -> match
					// $triggerMatch == true and $notEqualTo == true -> no match
					// $triggerMatch == false and $notEqualTo == false -> no match
					
					if (($triggerMatch === true && $notEqualTo === false) || 
						($triggerMatch === false && $notEqualTo === true) || 
						($triggerMatch === null && $notEqualTo === true)) 
					{
						// This is a match
						$match = true;
						
						// Set up the return content
						$curContent['id'] = $content->getId();
						$curContent['hidden'] = $content->getHidden();
						
						if ($this->getContentType() == self::TYPE_TEXT) {
							$curContent['text'] = $content->getContent();
						}
						else if ($this->getContentType() == self::TYPE_LINK) {
							$curContent['link']['url'] = $content->getUrl();
							$curContent['link']['text'] = $content->getContent();
						}
						else if ($this->getContentType() == self::TYPE_BLOCK) {
							// Nothing else for now
						}
						else if ($this->getContentType() == self::TYPE_ORBIT_BANNER) {
							$curContent['image_url'] = $content->getImageUrl();
							$curContent['link_url'] = $content->getUrl();
						}
					}
					else {
						// This trigger does not match
						$match = false;
						$curContent = array();
					}
					
					// "and" comparison
					if ($match == false && $content->getMatchAllTriggers() == true) {
						// This content doesn't match all triggers
						break;
					}
					// "or" comparison
					else if ($match == true && $content->getMatchAllTriggers() == false) {
						// Found a match, no need to continue
						break;
					}
				}
			}
			
			if ($match == true) {
				// Found matching content so return it
				// (the first matching content is returned)
				break;
			}
		}
		
		return $match == true && count($curContent) > 0 ? $curContent : null;
	}

	/**
	 * @param array $reqTriggers
	 * @param \MongoDocs\Trigger $trigger
	 * @param Boolean $notEqualTo
	 *
	 * @return bool|null Returns whether or not if the trigger matches the requests trigger. 
	 *                   If the trigger was not provided in the requested triggers then null is returned.
	 */
	private function matchTrigger($reqTriggers, $trigger, $notEqualTo) {
		// If no triggers were passed we match everything
		if (count($reqTriggers) == 0) {
			return true;
		}
		
		foreach ($reqTriggers as $reqTrigger) {
			if ($trigger->getType() == $reqTrigger['type']) {
				return strtolower($trigger->getValue()) == strtolower($reqTrigger['value']);
			}
		}
		
		// If we got here then the defined trigger was not provided by the client
		return null;
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
     * Add content
     *
     * @param \MongoDocs\Content $content
     */
    public function addContent(\MongoDocs\Content $content)
    {
        $this->content[] = $content;
    }

    /**
     * Remove content
     *
     * @param \MongoDocs\Content $content
     */
    public function removeContent(\MongoDocs\Content $content)
    {
        $this->content->removeElement($content);
    }

    /**
     * Get content
     *
     * @return \Doctrine\Common\Collections\Collection $content
     */
    public function getContent()
    {
	    return $this->content;
    }

    /**
     * Set contentType
     *
     * @param string $contentType
     * @return self
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Get contentType
     *
     * @return string $contentType
     */
    public function getContentType()
    {
        return $this->contentType;
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
     * Set domain
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
     * Get domain
     *
     * @return string $domain
     */
    public function getGroupName()
    {
        return $this->groupName;
    }
}
