

17 July 2017:	@ 3:30am - I implemented the \ArrayAccess syntactic suggar to SQL, it's taking readability to the next level!

16 July 2017:	On this day, I was on the MyBatis website reading documentation on their `Dynamic SQL` and `SQL Builder Class`;
					http://www.mybatis.org/mybatis-3/statement-builders.html
					http://www.mybatis.org/mybatis-3/dynamic-sql.html
					So I have begun the first steps in implementing the SQL class.
					The really funny thing about all these Query builders is that they normally require MORE code than straight SQL, I hate them all!
					@ 2:30pm I had the idea to incorporate my `builder` technique as well!
					The MyBatis `Dynamic SQL` builder is somewhat similar to my `builder` technique, except it's in XML! Yuk!

					Reading the Doctrine Data Mapping page (I hate Doctrine even more now), I have so much disrespect for it!
						Why put `check constraints` in the Entity class?	http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#property-mapping

					@ 4:15pm - BRAINSTORM! I had the idea to do `= new SQL().('My Additional Text here').().().('howdee doodee folks %s', $test).('WHERE id = :id', ['id' => 5]).('AND name = ?', $name)
						OMG! I don't have to use sprintf() ... OMG ... I can add text DIRECTLY to the SQL('SELECT * FROM bla bla WHERE id = ', $id) ...
