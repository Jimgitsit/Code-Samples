<?php

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity
 * @Table(name="imports")
 */
class ImportEntity extends EntityBase {
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	private $id;

	/**
	 * @Column(type="datetime", name="date_run")
	 */
	private $dateRun;

	/**
	 * @Column(type="text", nullable=true)
	 */
	private $backupFile;
	
	/**
	 * @Column(type="boolean")
	 */
	private $errorsOccured = false;
	
	/**
	 * @Column(type="boolean")
	 */
	private $warningsOccurred = false;

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
     * Set dateRun
     *
     * @param \DateTime $dateRun
     *
     * @return ImportEntity
     */
    public function setDateRun($dateRun)
    {
        $this->dateRun = $dateRun;

        return $this;
    }

    /**
     * Get dateRun
     *
     * @return \DateTime
     */
    public function getDateRun()
    {
        return $this->dateRun;
    }

    /**
     * Set backupFile
     *
     * @param string $backupFile
     *
     * @return ImportEntity
     */
    public function setBackupFile($backupFile)
    {
        $this->backupFile = $backupFile;

        return $this;
    }

    /**
     * Get backupFile
     *
     * @return string
     */
    public function getBackupFile()
    {
        return $this->backupFile;
    }

    /**
     * Set errorsOccured
     *
     * @param bool $errorsOccured
     *
     * @return ImportEntity
     */
    public function setErrorsOccured($errorsOccured)
    {
        $this->errorsOccured = $errorsOccured;

        return $this;
    }

    /**
     * Get errorsOccured
     *
     * @return bool
     */
    public function getErrorsOccured()
    {
        return $this->errorsOccured;
    }

    /**
     * Set warningsOccured
     *
     * @param bool $warningsOccurred
     *
     * @return ImportEntity
     */
    public function setWarningsOccurred($warningsOccurred)
    {
        $this->warningsOccurred = $warningsOccurred;

        return $this;
    }

    /**
     * Get warningsOccured
     *
     * @return bool
     */
    public function getWarningsOccurred()
    {
        return $this->warningsOccurred;
    }
}
