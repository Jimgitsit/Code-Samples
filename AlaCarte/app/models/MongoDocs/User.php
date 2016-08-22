<?php

namespace MongoDocs;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="users")
 */
class User extends BaseDocument {
	const STATUS_ENABLED = 'enabled';
	const STATUS_DISABLED = 'disabled';
	public static $statusTypes = array(
		self::STATUS_ENABLED,
		self::STATUS_DISABLED
	);
	
	const USER_LEVEL_CONTENT_EDITOR = 0;
	const USER_LEVEL_GROUP_ADMIN = 1;
	const USER_LEVEL_GLOBAL_ADMIN = 2;
	public static $userLevels = array(
		self::USER_LEVEL_CONTENT_EDITOR,
		self::USER_LEVEL_GROUP_ADMIN,
		self::USER_LEVEL_GLOBAL_ADMIN
	);
	
	/** @ODM\Id */
	private $id;

	/** @ODM\Field(type="string") */
	private $name;

	/** @ODM\Field(type="string") */
	private $email;

	/** @ODM\Field(type="string") */
	private $password;
	
	/** @ODM\Field(type="string") */
	private $tempPassword;
	
	/** @ODM\Field(type="string") */
	private $apiClientId;

	/** @ODM\Field(type="string") */
	private $apiSecret;

	/** @ODM\Field(type="string") */
	private $salt;
	
	/** @var  @ODM\Field(type="int") */
	private $level;

	/** @ODM\ReferenceOne(targetDocument="Group", simple=true) */
	private $group;

	/** @ODM\Field(type="string") */
	private $status;

	/** @ODM\Field(type="boolean") */
	private $isGlobalAdmin;

	/** @ODM\Field(type="boolean") */
	private $isGroupAdmin;

	/** @ODM\Field(type="string") */
	private $cookieId;
	
	/**
	 * User constructor.
	 */
	public function __construct() {
		$this->setStatus('enabled');
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
			'email' => $this->getEmail(),
			'apiClientId' => $this->getApiClientId(),
			'apiSecret' => $this->getApiSecret(),
			'status' => $this->getStatus(),
			'isGroupAdmin' => $this->getIsGroupAdmin(),
			'isGlobalAdmin' => $this->getIsGlobalAdmin(),
			'level' => array(
				'value' => $this->getLevel(),
				'name' => self::userLevelName($this->getLevel())
			),
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
	 * Saves this user to the session
	 */
	public function saveToSession() {
		$user = json_decode(json_encode($this), true);
		unset($user['apiClientId']);
		unset($user['apiClientSecret']);
		$_SESSION['user'] = $user;
	}
	
	/**
	 * Removes the user from the session.
	 */
	public function removeFromSession() {
		unset($_SESSION['user']);
	}
	
	/**
	 * Helper function that returns the display name for a user level.
	 * 
	 * @param $userLevel int One of the USER_LEVEL_* constants defined in this class.
	 *
	 * @return string
	 */
	public static function userLevelName($userLevel) {
		if ($userLevel !== null) {
			switch ($userLevel) {
				case 0: {
					return 'Content Editor';
				}
				case 1: {
					return 'Group Admin';
				}
				case 2: {
					return 'Global Admin';
				}
			}
		}
		
		return '';
	}
	
	/**
	 * Temporary function to update user records in the db to support 
	 * the new 'level' property. 
	 * 
	 * Called from wherever a user is accessed.
	 * 
	 * This can be removed once all users have been updated in the db.
	 * 
	 * @param $dm
	 * @param $user
	 *
	 * @return mixed
	 */
	public static function fixProps($dm, $user, $saveToSession = false) {
		if ($user->getLevel() == null) {
			if ($user->getIsGroupAdmin()) {
				$user->setLevel(self::USER_LEVEL_GROUP_ADMIN);
			}
			else if ($user->getIsGlobalAdmin()) {
				$user->setLevel(self::USER_LEVEL_GLOBAL_ADMIN);
				if ($user->getGroup() !== null) {
					$user->getGroup()->removeUser($user);
				}
				$user->setGroup(null);
			}
			else {
				$user->setLevel(self::USER_LEVEL_CONTENT_EDITOR);
			}
			
			$user->setIsGroupAdmin(null);
			$user->setIsGlobalAdmin(null);
			
			$dm->persist($user);
			$dm->flush();
			
			if ($saveToSession && isset($_SESSION['user'])) {
				$user->saveToSession();
			}
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
     * Set email
     *
     * @param string $email
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set isGlobalAdmin
     *
     * @param boolean $isGlobalAdmin
     * @return self
     */
    public function setIsGlobalAdmin($isGlobalAdmin)
    {
        $this->isGlobalAdmin = $isGlobalAdmin;
        return $this;
    }

    /**
     * Get isGlobalAdmin
     *
     * @return boolean $isGlobalAdmin
     */
    public function getIsGlobalAdmin()
    {
        return $this->isGlobalAdmin;
    }

    /**
     * Set isGroupAdmin
     *
     * @param boolean $isGroupAdmin
     * @return self
     */
    public function setIsGroupAdmin($isGroupAdmin)
    {
        $this->isGroupAdmin = $isGroupAdmin;
        return $this;
    }

    /**
     * Get isGroupAdmin
     *
     * @return boolean $isGroupAdmin
     */
    public function getIsGroupAdmin()
    {
        return $this->isGroupAdmin;
    }

    /**
     * Set group
     *
     * @param \MongoDocs\Group $group
     * @return self
     */
    public function setGroup($group)
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
     * Set password
     * 
     * Passwords are hashed using password_hash. Use password_verify to check.
     *
     * @param string $password
     * @return self
     */
    public function setPassword($password)
    {
	    if ($password != null) {
		    // Generate a random salt
		    $salt = bin2hex(openssl_random_pseudo_bytes(22, $strong));
		
		    $this->setSalt($salt);
		    $this->password = hash('sha256', $salt . $password);
	    }
	    else {
		    $this->password = null;
	    }
	    
        return $this;
    }

	/**
	 * Tests if the given password is correct for this user.
	 * 
	 * @param $password
	 *
	 * @return bool
	 */
	public function verifyPassword($password) {
		$hash = hash('sha256', $this->getSalt() . $password);
		
		if ($this->getPassword() === $hash) {
			return true;
		}
		
		return false;
	}

    /**
     * Get password
     * 
     * Use verifyPassword to test a password.
     *
     * @return string $password
     */
    public function getPassword()
    {
        return $this->password;
    }
	
	/**
	 * Set tempPassword
	 * Uses the same salt field as the normal password.
	 *
	 * @param string $tempPassword
	 * @return self
	 */
	public function setTempPassword($tempPassword)
	{
		if ($tempPassword != null) {
			// Generate a random salt
			$salt = bin2hex(openssl_random_pseudo_bytes(22, $strong));
			
			$this->setSalt($salt);
			$this->tempPassword = hash('sha256', $salt . $tempPassword);
		}
		else {
			$this->tempPassword = null;
		}
		
		return $this;
	}
	
	/**
	 * Tests if the given temporary password is correct for this user.
	 * Uses the same salt field as the normal password.
	 *
	 * @param $tempPassword
	 *
	 * @return bool
	 */
	public function verifyTempPassword($tempPassword) {
		$hash = hash('sha256', $this->getSalt() . $tempPassword);
		
		if ($this->getTempPassword() === $hash) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get tempPassword
	 *
	 * @return string $tempPassword
	 */
	public function getTempPassword()
	{
		return $this->tempPassword;
	}

    /**
     * Set salt
     *
     * @param string $salt
     * @return self
     */
    private function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * Get salt
     *
     * @return string $salt
     */
    private function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set apiClientId
     *
     * @param string $apiClientId
     * @return self
     */
    public function setApiClientId($apiClientId)
    {
        $this->apiClientId = $apiClientId;
        return $this;
    }

    /**
     * Get apiClientId
     *
     * @return string $apiClientId
     */
    public function getApiClientId()
    {
        return $this->apiClientId;
    }

    /**
     * Set apiSecret
     *
     * @param string $apiSecret
     * @return self
     */
    public function setApiSecret($apiSecret)
    {
        $this->apiSecret = $apiSecret;
        return $this;
    }

    /**
     * Get apiSecret
     *
     * @return string $apiSecret
     */
    public function getApiSecret()
    {
        return $this->apiSecret;
    }

    /**
     * Set cookieId
     *
     * @param string $cookieId
     * @return self
     */
    public function setCookieId()
    {
	    $this->cookieId = hash('sha256', $this->salt . $this->id);
	    
        return $this;
    }

	/**
	 * Removes the cookie id.
	 * 
	 * @return $this
	 */
	public function removeCookieId() {
		$this->cookieId = '';
		
		return $this;
	}

    /**
     * Get cookieId
     *
     * @return string $cookieId
     */
    public function getCookieId()
    {
        return $this->cookieId;
    }

    /**
     * Set level
     *
     * @param int $level
     * @return self
     */
    public function setLevel($level)
    {
        $this->level = $level;
        return $this;
    }

    /**
     * Get level
     *
     * @return int $level
     */
    public function getLevel()
    {
        return $this->level;
    }
}
