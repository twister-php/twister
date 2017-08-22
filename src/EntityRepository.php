<?php

namespace Twister;

/**
 *	An EntityRepository serves as a repository for entities with generic as well as
 *	business specific methods for retrieving entities.
 *
 *	This class is designed for inheritance and users can subclass this class to
 *	write their own repositories with business-specific methods to locate entities.
 *
 *	@author  Trevor Herselman <therselman@gmail.com>
 */
class EntityRepository
{
	/**
	 * The database connection used by the EntityManager.
	 *
	 * @var \Doctrine\DBAL\Connection
	 */
	protected $conn			=	null;


	public function __construct(Container $c)
	{
		$this->conn	=	$c->db;
	}


	/**
	 *	{@inheritDoc}
	 */
	public function getConnection()
	{
		return $this->conn;
	}


	/**
	 *	{@inheritDoc}
	 */
	public function getRepository($entityName)
	{
		static $repos = null;
		if ( ! isset($repos[$entityName])) {
			$repoName = $entityName . 'Repository';
echo 'loading: ' . $repoName;
			$repos[$entityName] = new $repoName($this);
		}
		return $repos[$entityName];
	}


	/**
	 *	{@inheritDoc}
	 */
	public function beginTransaction()
	{
		$this->conn->beginTransaction();
	}


	/**
	 *	{@inheritDoc}
	 */
	public function transaction($func)		//	AKA `transactional` in Doctrine
	{
		if (!is_callable($func)) {
			throw new \InvalidArgumentException('Expected argument of type "callable", got "' . gettype($func) . '"');
		}
		$this->conn->beginTransaction();
		try {
			$return = call_user_func($func, $this);
			$this->flush();
			$this->conn->commit();
			return $return ?: true;
		} catch (Exception $e) {
			$this->close();
			$this->conn->rollBack();
			throw $e;
		}
	}


	/**
	 *	{@inheritDoc}
	 */
	public function commit()
	{
		$this->conn->commit();
	}


	/**
	 *	{@inheritDoc}
	 */
	public function rollback()
	{
		$this->conn->rollBack();
	}


}
