<?php

/**
 *	Collection class with dynamic members
 *	Similar functionality to an Array or Dictionary class
 *	Typically holding a collection/array of Entity members
 *	This class is not particularly useful compared to a standard array,
 *		but it's used to `extend` the functionality of standard arrays.
 *
 *	@link	https://laravel.com/docs/5.4/eloquent-collections#available-methods
 *
 *	@author	Trevor Herselman <therselman@gmail.com>
 */

namespace Twister\ORM;

class Collection implements \Iterator, \Countable, \ArrayAccess, \IteratorAggregate //, JsonSerializable	http://php.net/JsonSerializable
{
	protected $members	=	null;

	public function __construct(array $members = [])
	{
		$this->members	=&	$members;
	}


	/**
	 *	Countable interface
	 */
	public function find(...$params)
	{
		if (count($params) === 1)
			return count($this->members);
		
	}


	/**
	 *	Get the keys of the collection members.
	 *
	 *	@return static
	 */
	public function keys()
	{
		return new static(array_keys($this->members));	//	or	array_keys($this->members)
	}


	/**
	 *	Get the items in the collection that are not present in the given array.
	 *
	 *	@param  mixed  $items
	 *	@return static
	 */
	public function diff($items)
	{
		return new static(array_diff($this->members, $this->getArrayableItems($items)));
	}


	/**
	 *	Execute a callback over each item.
	 *
	 *	@param  callable  $callback
	 *	@return $this
	 */
	public function each(callable $callback)
	{
		foreach ($this->members as $key => $member)
		{
			if ($callback($key, $member) === false) {
				break;
			}
		}
		return $this;
	}


	/**
	 *	Run a map over each of the members.
	 *
	 *	@link	http://php.net/manual/en/function.array-map.php
	 *
	 *	@param  callable  $callback
	 *	@return static
	 */
	public function map(callable $callback)
	{
		$keys = array_keys($this->members);
		$members = array_map($callback, $this->members, $keys);
		return new static(array_combine($keys, $members));
	}


    /**
     *	Merge the collection with the given items.
     *
     *	@param  mixed  $items
     *	@return static
     */
    public function merge($items)
    {
        return new static(array_merge($this->items, $this->getArrayableItems($items)));
    }


	/**
	 *	Results array of items from Collection or Arrayable.
	 *
	 *	Used by PHP functions that only accept a standard array
	 *
	 *	@param  mixed  $items
	 *	@return array
	 */
	protected function getArrayableItems($items)
	{
		if ($items instanceof self) {
			return $items->all();
		} else if (is_array($items)) {
			return $items;
		} else if ($items instanceof Arrayable) {
			return $items->toArray();
		} else if ($items instanceof Jsonable) {
			return json_decode($items->toJson(), true);
		} else if (is_object($items) && method_exists($items, 'toArray')) {
			return $items->toArray();
		}
		return (array) $items;
	}


	/**
	 *	Get and remove the last member from the collection.
	 *
	 *	@link	http://php.net/manual/en/function.array-pop.php
	 *
	 *	@return mixed
	 */
	public function pop()
	{
		return array_pop($this->members);
	}


    /**
     *	Push an item onto the end of the collection.
     *
     *	@link	http://php.net/manual/en/function.array-push.php
     *
     *	@param  mixed  $value
     *	@return $this
     */
    public function push($value)
    {
		$this->members[] = $value;
        return $this;
    }


	/**
	 * Put an item in the collection by key.
	 *
	 * @param  mixed  $key
	 * @param  mixed  $value
	 * @return $this
	 */
	public function put($key, $value)
	{
		$this->members[$key] = $value;
		return $this;
	}


	/**
	 *	Get one or more random members from the collection.
	 *
	 *	@link	http://php.net/manual/en/function.array-rand.php
	 *
	 *	@param  int  $num
	 *	@return mixed
	 */
	public function random($num = 1)
	{
		if ($num == 1)
			return $this->members[array_rand($this->members)];

		$keys = array_rand($this->members, $num);
		return new static(array_intersect_key($this->members, array_flip($keys)));
	}


	/**
	 *	Get and remove the first item from the collection.
	 *
	 *	@link	http://php.net/manual/en/function.array-shift.php
	 *
	 *	@return mixed
	 */
	public function shift()
	{
		return array_shift($this->members);
	}


    /**
     *	Shuffle the members in the collection.
     *
     *	@return static
     */
    public function shuffle()
    {
        $members = $this->members;
        shuffle($members);
        return new static($members);
    }


	/**
	 *	Slice the underlying collection array.
	 *
	 *	@param  int   $offset
	 *	@param  int   $length
	 *	@param  bool  $preserve_keys
	 *	@return static
	 */
	public function slice($offset, $length = null, $preserve_keys = false)
	{
		return new static(array_slice($this->members, $offset, $length, $preserve_keys));
	}


	/**
	 *	Take the first or last {$limit} members.
	 *
	 *	@param  int  $limit
	 *	@return static
	 */
	public function take($limit)
	{
		return $limit < 0 ? $this->slice($limit, abs($limit)) : $this->slice(0, $limit);
	}


	/**
	 *	Run a filter over each of the members.
	 *
	 *	@link	http://php.net/manual/en/function.array-filter.php
	 *
	 *	@param  callable|null  $callback
	 *	@return static
	 */
	public function filter(callable $callback = null)
	{
		return new static($callback ? array_filter($this->members, $callback) : array_filter($this->members));
	}


	/**
	 *	Reset the keys on the underlying array.
	 *
	 *	@link	http://php.net/manual/en/function.array-values.php
	 *
	 *	@return static
	 */
	public function values()
	{
		return new static(array_values($this->members));
	}


	/**
	 *	Prepend one or more members to the beginning of the collection
	 *
	 *	@link	http://php.net/manual/en/function.array-values.php
	 *
	 *	@return $this
	 */
	public function unshift(...$values)
	{
		array_unshift($this->members, $values);
		return $this;
	}


	/**
	 *	Flip the members of the collection.
	 *
	 *	@return static
	 */
	public function flip()
	{
		return new static(array_flip($this->members));
	}


	/**
	 *	Determine if the collection is empty or not.
	 *
	 *	@return bool
	 */
	public function isEmpty()
	{
		return empty($this->members);
	}


	/**
	 *	Determine if the collection is not empty.
	 *
	 *	@return bool
	 */
	public function isNotEmpty()
	{
		return ! empty($this->members);
	}


	/**
	 *	Get an iterator for the members.
	 *
	 *	@return \ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->members);
	}


	/**
	 *	Get member by id/index
	 *
	 *	Note: This is not very useful, because most members will be indexed by integer.
	 *
	 *	@param  string|int  $idx
	 *	@return mixed
	 */
	public function __get($idx)
	{
		return $this->members[$idx];
	}


	/**
	 *	Set member by id/index
	 *
	 *	@param  string|int  $idx
	 *	@param  mixed       $value
	 *	@return void
	 */
	public function __set($idx, $value)
	{
		$this->members[$idx] = $value;
	}


	function __isset($idx)
	{
		return isset($this->members[$idx]);
	}
	function __unset($idx)
	{
		unset($this->members[$idx]);
	}


    /**
     *	Get the collection of items as a plain array.
     *
     *	@return array
     */
	/**
	 *	Workaround for the `array access functions` eg. array_push($obj->toArray(), $value);
	 */
	public function toArray()
	{
		return $this->members;
	}


	/**
	 *	Get all of the items in the collection.
	 *
	 *	@alias toArray()
	 *
	 *	@return array
	 */
	public function all()
	{
		return $this->members;
	}


	/**
	 *	
	 */
	public function toJson()
	{
		return json_encode($this->members);
	}


    public function __toString()
    {
        return json_encode($this->members);
    }


	/**
	 *	Iterator interface
	 */
	public function rewind()
	{
		return reset($this->members);
	}
	public function current()
	{
		return current($this->members);
	}
	public function key()
	{
		return key($this->members);
	}
	public function next()
	{
		return next($this->members);
	}
	public function valid()
	{
		return key($this->members) !== null;	//	or	current($this->attributes) !== false
	}


	/**
	 *	Countable interface
	 */
	public function count()
	{
		return count($this->members);
	}
	/**
	 *	@alias count()
	 */
	public function length()
	{
		return count($this->members);
	}


	/**
	 *	ArrayAccess interface
	 */
	public function offsetGet($idx)			//	eg. var_dump($obj['two']);
	{
		return $this->members[$idx];
	}
	public function offsetSet($idx, $value)	//	eg. $obj['two'] = 'A value';
	{
		$this->members[$idx] = $value;
	}
	public function offsetExists($idx)		//	eg. isset($obj['two'])
	{
		return isset($this->members[$idx]);
	}
	public function offsetUnset($idx)		//	eg. unset($obj['two']);
	{
		unset($this->members[$idx]);
	}
}
