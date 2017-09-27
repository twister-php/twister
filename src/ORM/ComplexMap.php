<?php

namespace Twister\ORM;

/**
 *	Used to map a single entity to multiple-tables with complex joins or dynamic fields ... why?
 *
 *
 */
abstract class ComplexMap extends EntityMap
{

	protected	$joins;	//	optional
	protected	$fields;

}
