<?php

namespace Twister\ORM;

class EntityManager
{
	/**
	 * The database connection used by the EntityManager.
	 *
	 * @var \Doctrine\DBAL\Connection
	 */
	protected $conn			=	null;

	protected $repos		=	null;


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
		if ( ! isset($this->repos[$entityName])) {
			$repoName = $entityName . 'Repository';
			$this->repos[$entityName] = new $repoName($this);
		}
		return $this->repos[$entityName];
	}


	/**
	 *	{@inheritDoc}
	 */
	public function find($entityName, ...$params)
	{
		if ( ! isset($this->repos[$entityName])) {
			$repoName = $entityName . 'Repository';
			$this->repos[$entityName] = new $repoName($this);
		}
		return $this->repos[$entityName];
	}


    /**
     *	Adds support for magic method calls.
     *
     *	@param string $method
     *	@param array  $args
     *
     *	@return mixed The returned value from the resolved method.
     *
     *	@throws ORMException
     *	@throws \BadMethodCallException If the method called is invalid
     */
    public function __call($method, $args)
    {
        if (0 === strpos($method, 'get')) {
            return $this->;
        }
        if (0 === strpos($method, 'findBy')) {
            return $this->resolveMagicCall('findBy', substr($method, 6), $args);
        }
        if (0 === strpos($method, 'findOneBy')) {
            return $this->resolveMagicCall('findOneBy', substr($method, 9), $args);
        }
        if (0 === strpos($method, 'countBy')) {
            return $this->resolveMagicCall('count', substr($method, 7), $args);
        }
        throw new \BadMethodCallException(
            "Undefined method '$method'. The method name must start with ".
            "either findBy, findOneBy or countBy!"
        );
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
	public function transaction($func)		//	AKA `transactional` in Doctrine		AKA transact
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
