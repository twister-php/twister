<?php

namespace Twister;

//@param \Doctrine\DBAL\Connection     $conn

class EntityManager
{
	protected $config		=	null;

	/**
	 * The database connection used by the EntityManager.
	 *
	 * @var \Doctrine\DBAL\Connection
	 */
	protected $conn			=	null;

	/**
	 * Whether the EntityManager is closed or not.
	 *
	 * @var bool
	 */
	private $closed = false;

	/**
	 * Collection of query filters.
	 *
	 * @var \Doctrine\ORM\Query\FilterCollection
	 */
	private $filterCollection;

	public function __construct($conn, array $properties = null)
	{
		$this->properties	=&	$properties;
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


	/**
	 * Get entity field/property
	 *
	 * @param  string  $name
	 * @return mixed
	 */
	public function __get($name)
	{
		$value = $this->properties[$name];
		return is_callable($value) ? $value($this) : $value;
	}

	/**
	 * Set entity field/property
	 *
	 * @param  string  $name
	 * @param  mixed   $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->properties[$name] = $value;
	}


	public function __isset($name)
	{
		return isset($this->properties[$name]);
	}
	public function __unset($name)
	{
		unset($this->properties[$name]);
	}


	public function __call($method, ...$args)
	{
		array_unshift($args, $this);
		return call_user_func($this->properties[$method], ...$args);
	}
	public function __invoke()
	{
		return $this->properties['__invoke']($this);
	}
	public function setMethod($method, callable $callable)
	{
		$this->properties[$method] = $callable;
		return $this;
	}
}
