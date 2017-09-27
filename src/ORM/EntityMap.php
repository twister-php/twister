<?php

namespace Twister\ORM;

/**
 *	`Maps` persistent storage to Entities
 *
 *	`Since Eloquent models are query builders, you should review all of the methods available on the query builder. You may use any of these methods in your Eloquent queries.`
 *
 *	Actions and responsibilities include:
 *		Implements field Mutators and Accessors
 *		Relationships & Joins
 *		List of Fields
 *		Table aliases
 *		Virtual / dynamic field names
 *
 *
 *
 *	@link	http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/annotations-reference.html
 *
 *	@Column
 *		type:		Name of the Doctrine Type which is converted between PHP and Database representation.
 *		name:		By default the property name is used for the database column name also, however the ‘name’ attribute allows you to determine the column name.
 *		length:		Used by the “string” type to determine its maximum length in the database. Doctrine does not validate the length of a string values for you.
 *		precision:	The precision for a decimal (exact numeric) column (applies only for decimal column), which is the maximum number of digits that are stored for the values.
 *		scale:		The scale for a decimal (exact numeric) column (applies only for decimal column), which represents the number of digits to the right of the decimal point and must not be greater than precision.
 *		unique:		Boolean value to determine if the value of the column should be unique across all rows of the underlying entities table.
 *		nullable:	Determines if NULL values allowed for this column. If not specified, default value is false.
 *		options:	Array of additional options:
 *						default: The default value to set for the column if no value is supplied.
 *						unsigned: Boolean value to determine if the column should be capable of representing only non-negative integers (applies only for integer column and might not be supported by all vendors).
 *						fixed: Boolean value to determine if the specified length of a string column should be fixed or varying (applies only for string/binary column and might not be supported by all vendors).
 *						comment: The comment of the column in the schema (might not be supported by all vendors).
 *						collation: The collation of the column (only supported by Drizzle, Mysql, PostgreSQL>=9.1, Sqlite and SQLServer).
 *						check: Adds a check constraint type to the column (might not be supported by all vendors).
 *		columnDefinition:	DDL SQL snippet that starts after the column name and specifies the complete (non-portable!) column definition. This attribute allows to make use of advanced RMDBS features. However you should make careful use of this feature and the consequences. SchemaTool will not detect changes on the column correctly anymore if you use “columnDefinition”.
 *							Additionally you should remember that the “type” attribute still handles the conversion between PHP and Database values. If you use this attribute on a column that is used for joins between tables you should also take a look at @JoinColumn.
 *
 *
 *	derivedFrom: 	AKA dynamic field!
 *
 *
 *
 */
abstract class EntityMap
{
	protected	$fields;
	protected	$from;
	protected	$tables;
	protected	$joins;	//	optional
	protected	$properties;
	protected	$aliases;
	protected	$relationships;
	protected	$indexes;

	protected	$constraints;	//	AKA validations?

//	abstract private __get__();

	/**
	 * The current globally available instance (if any).
	 *
	 * @var static
	 */
	protected static $instance = null;

	protected function __construct()
	{

	}

	/**
	 * Get the globally available instance
	 *
	 * @return static
	 */
	public static function getInstance()
	{
		if ( ! isset(static::$instance))
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

}
/*
	'aliases'		=>	[	'w'		=>	'worlds',
							'u'		=>	'users',
							'c'		=>	'clubs'
						];

	'keys'	=	[	[['u.id', 'w.user_id'], 'one-to-many'],
					[['w.id', 'c.world_id'], 'one-to-many']
				];

	//	dynamic optional field names
	'joins'	=>	[	'name' => 'LEFT JOIN nation_names %1$s ON %1$s.nation_id = n.id AND %1$s.lang = "%2$s" AND "%3$s" BETWEEN %1$s.from_date AND %1$s.until_date',
					
				],

			static $joins = array(		'name'				=>	' LEFT JOIN nation_names %1$s ON %1$s.nation_id = n.id AND %1$s.lang = "%2$s" AND "%3$s" BETWEEN %1$s.from_date AND %1$s.until_date',
										'history'			=>	' LEFT JOIN nation_history nh ON nh.nation_id = n.id AND "%s" BETWEEN nh.from_date AND nh.until_date',
										'capital'			=>	' LEFT JOIN city_names %1$s ON %1$s.city_id = nh.capital_id AND %1$s.lang = "%2$s" AND "%3$s" BETWEEN %1$s.from_date AND %1$s.until_date',
										'territories'		=>	' LEFT JOIN territory_nations tn ON tn.territory_id = t.id',
										'territories-admin'	=>		' LEFT JOIN territory t ON t.id = tn.territory_id',
										'population'		=>	' LEFT JOIN nation_population np ON np.nation_id = n.id AND %s BETWEEN np.from_day AND np.until_day',	//	This uses a 'pre-calculated' day, ie. TO_DAYS(CURDATE) ... HOWEVER, We could also add anything we wanted to the string! ie. 'IFNULL(TO_DAYS("' . $date . '"), 0)' ... WARNING: TO_DAYS("0000-00-00") is converted to NULL!
									//	'profile'			=>	'', // common +
									//	'city'				=>	' LEFT JOIN nations n ON n.id = tn.nation_id',	//	shouldn't the city create the joins ???
									//	'wikipedia'			=>	' LEFT JOIN urls %1$s ON %1$s.object = "nation" AND %1$s.object_id = n.id AND %1$s.lang = "%2$s" AND %1$s.type = "wikipedia"',	//	DEPRECATED!
										'modified-by'		=>	' LEFT JOIN users muid ON muid.id = n.modified_user_id'
									);





	public $contexts	=	[	'profile'	=>	[],
								''
							];

	public $virtualFields	=	[	'age'			=>	'TIMESTAMPDIFF(YEAR, u.dob, NOW()) as age',				//	AKA derived fields
									'style_name'	=>	'STYLIZE_NAME(u.fname, u.alias, u.lname, 0, 0)'
								];

	public static function factory()
	{
		
	}



	Laravel
	-------
	$this->hasMany('App\User')->withTimestamps();


	Analogue
	--------
use Analogue\ORM\Relationships\BelongsTo;
use Analogue\ORM\Relationships\BelongsToMany;
use Analogue\ORM\Relationships\EmbedsMany;
use Analogue\ORM\Relationships\EmbedsOne;
use Analogue\ORM\Relationships\HasMany;
use Analogue\ORM\Relationships\HasManyThrough;
use Analogue\ORM\Relationships\HasOne;
use Analogue\ORM\Relationships\MorphMany;
use Analogue\ORM\Relationships\MorphOne;
use Analogue\ORM\Relationships\MorphTo;
use Analogue\ORM\Relationships\MorphToMany;

*/
