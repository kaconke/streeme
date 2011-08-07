<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EchonestProperties', 'doctrine');

/**
 * BaseEchonestProperties
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $song_id
 * @property string $name
 * @property string $value
 * 
 * @method integer            getId()      Returns the current record's "id" value
 * @method integer            getSongId()  Returns the current record's "song_id" value
 * @method string             getName()    Returns the current record's "name" value
 * @method string             getValue()   Returns the current record's "value" value
 * @method EchonestProperties setId()      Sets the current record's "id" value
 * @method EchonestProperties setSongId()  Sets the current record's "song_id" value
 * @method EchonestProperties setName()    Sets the current record's "name" value
 * @method EchonestProperties setValue()   Sets the current record's "value" value
 * 
 * @package    streeme
 * @subpackage model
 * @author     Richard Hoar
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseEchonestProperties extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('echonest_properties');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('song_id', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('name', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             ));
        $this->hasColumn('value', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             ));


        $this->index('song_index', array(
             'fields' => 
             array(
              0 => 'song_id',
             ),
             ));
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        
    }
}