<?php

namespace Twister\ORM;

/**
 *	`Maps` persistent storage to Entities
 *
 *	Actions and responsibilities include:
 *		Implements field Mutators and 
 *		Relationships & Joins
 *		List of Fields
 *		Table aliases
 *		Virtual / dynamic field names
 *
 */
abstract class EntityMap
{
	protected	$fields;
	protected	$from;
	protected	$joins;	//	optional
	protected	$properties;
	protected	$aliases;
	protected	$relationships;

//	abstract private __get__();

	function __construct(array $properties = null)
	{
		$this->properties	=&	$properties;
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

	public $virtualFields	=	[	'age'			=>	'TIMESTAMPDIFF(YEAR, u.dob, NOW()) as age',
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
