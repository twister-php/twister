<?php

namespace Twister;

trait ContainerAwareTrait
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Sets the container.
	 *
	 * @param ContainerInterface|null $container A ContainerInterface instance or null
	 */
	public function setContainer(ContainerInterface $container = null)
	{
		$this->container = $container;
		return $this;
	}
}
