<?php

/**
 * EchonestProperties filter form base class.
 *
 * @package    streeme
 * @subpackage filter
 * @author     Richard Hoar
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseEchonestPropertiesFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'en_item_id'            => new sfWidgetFormFilterInput(),
      'en_version'            => new sfWidgetFormFilterInput(),
      'en_date_added'         => new sfWidgetFormFilterInput(),
      'en_artist_id'          => new sfWidgetFormFilterInput(),
      'en_song_id'            => new sfWidgetFormFilterInput(),
      'en_foreign_id'         => new sfWidgetFormFilterInput(),
      'en_audio_md5'          => new sfWidgetFormFilterInput(),
      'en_location'           => new sfWidgetFormFilterInput(),
      'en_mode'               => new sfWidgetFormFilterInput(),
      'en_time_signature'     => new sfWidgetFormFilterInput(),
      'en_key'                => new sfWidgetFormFilterInput(),
      'en_duration'           => new sfWidgetFormFilterInput(),
      'en_loudness'           => new sfWidgetFormFilterInput(),
      'en_energy'             => new sfWidgetFormFilterInput(),
      'en_tempo'              => new sfWidgetFormFilterInput(),
      'en_danceability'       => new sfWidgetFormFilterInput(),
      'en_song_hotttnesss'    => new sfWidgetFormFilterInput(),
      'en_artist_hotttnesss'  => new sfWidgetFormFilterInput(),
      'en_artist_familiarity' => new sfWidgetFormFilterInput(),
      'en_latitude'           => new sfWidgetFormFilterInput(),
      'en_longitude'          => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'en_item_id'            => new sfValidatorPass(array('required' => false)),
      'en_version'            => new sfValidatorPass(array('required' => false)),
      'en_date_added'         => new sfValidatorPass(array('required' => false)),
      'en_artist_id'          => new sfValidatorPass(array('required' => false)),
      'en_song_id'            => new sfValidatorPass(array('required' => false)),
      'en_foreign_id'         => new sfValidatorPass(array('required' => false)),
      'en_audio_md5'          => new sfValidatorPass(array('required' => false)),
      'en_location'           => new sfValidatorPass(array('required' => false)),
      'en_mode'               => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'en_time_signature'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'en_key'                => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'en_duration'           => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'en_loudness'           => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'en_energy'             => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'en_tempo'              => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'en_danceability'       => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'en_song_hotttnesss'    => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'en_artist_hotttnesss'  => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'en_artist_familiarity' => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'en_latitude'           => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'en_longitude'          => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('echonest_properties_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'EchonestProperties';
  }

  public function getFields()
  {
    return array(
      'id'                    => 'Number',
      'en_item_id'            => 'Text',
      'en_version'            => 'Text',
      'en_date_added'         => 'Text',
      'en_artist_id'          => 'Text',
      'en_song_id'            => 'Text',
      'en_foreign_id'         => 'Text',
      'en_audio_md5'          => 'Text',
      'en_location'           => 'Text',
      'en_mode'               => 'Number',
      'en_time_signature'     => 'Number',
      'en_key'                => 'Number',
      'en_duration'           => 'Number',
      'en_loudness'           => 'Number',
      'en_energy'             => 'Number',
      'en_tempo'              => 'Number',
      'en_danceability'       => 'Number',
      'en_song_hotttnesss'    => 'Number',
      'en_artist_hotttnesss'  => 'Number',
      'en_artist_familiarity' => 'Number',
      'en_latitude'           => 'Number',
      'en_longitude'          => 'Number',
    );
  }
}
