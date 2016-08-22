<?php

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

require_once('EntityBase.php');

/**
 * @Entity
 * @Table(
 *     name="search_queries"
 * )
 */
class SearchQuery extends EntityBase implements \JsonSerializable
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @Column(type="datetime")
	 */
	protected $date;
	
	/**
	 * @Column(type="json_array")
	 */
	protected $requestData;
	
	/**
	 * @Column(type="text")
	 */
	protected $scope;
	
	/**
	 * @Column(type="text")
	 */
	protected $phrase;
	
	/**
	 * @Column(type="integer")
	 */
	protected $resultCountTotal;
	
	/**
	 * @Column(type="integer", nullable=true)
	 */
	protected $resultCountProviders;
	
	/**
	 * @Column(type="integer", nullable=true)
	 */
	protected $resultCountEmployees;
	
	/**
	 * @Column(type="integer", nullable=true)
	 */
	protected $resultCountBusinesses;
	
	public function setTotal($type, $count) {
		$this->$type = $count;
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
     * Set scope
     *
     * @param string $scope
     *
     * @return SearchQuery
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set phrase
     *
     * @param string $phrase
     *
     * @return SearchQuery
     */
    public function setPhrase($phrase)
    {
        $this->phrase = $phrase;

        return $this;
    }

    /**
     * Get phrase
     *
     * @return string
     */
    public function getPhrase()
    {
        return $this->phrase;
    }

    /**
     * Set resultCountTotal
     *
     * @param int $resultCountTotal
     *
     * @return SearchQuery
     */
    public function setResultCountTotal($resultCountTotal)
    {
        $this->resultCountTotal = $resultCountTotal;

        return $this;
    }

    /**
     * Get resultCountTotal
     *
     * @return int
     */
    public function getResultCountTotal()
    {
        return $this->resultCountTotal;
    }

    /**
     * Set resultCountProviders
     *
     * @param int $resultCountProviders
     *
     * @return SearchQuery
     */
    public function setResultCountProviders($resultCountProviders)
    {
        $this->resultCountProviders = $resultCountProviders;

        return $this;
    }

    /**
     * Get resultCountProviders
     *
     * @return int
     */
    public function getResultCountProviders()
    {
        return $this->resultCountProviders;
    }

    /**
     * Set resultCountEmployees
     *
     * @param int $resultCountEmployees
     *
     * @return SearchQuery
     */
    public function setResultCountEmployees($resultCountEmployees)
    {
        $this->resultCountEmployees = $resultCountEmployees;

        return $this;
    }

    /**
     * Get resultCountEmployees
     *
     * @return int
     */
    public function getResultCountEmployees()
    {
        return $this->resultCountEmployees;
    }

    /**
     * Set resultCountBusinesses
     *
     * @param int $resultCountBusinesses
     *
     * @return SearchQuery
     */
    public function setResultCountBusinesses($resultCountBusinesses)
    {
        $this->resultCountBusinesses = $resultCountBusinesses;

        return $this;
    }

    /**
     * Get resultCountBusinesses
     *
     * @return int
     */
    public function getResultCountBusinesses()
    {
        return $this->resultCountBusinesses;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return SearchQuery
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set requestData
     *
     * @param array $requestData
     *
     * @return SearchQuery
     */
    public function setRequestData($requestData)
    {
        $this->requestData = $requestData;

        return $this;
    }

    /**
     * Get requestData
     *
     * @return array
     */
    public function getRequestData()
    {
        return $this->requestData;
    }
}
