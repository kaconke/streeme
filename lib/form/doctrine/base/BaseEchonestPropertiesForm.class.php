<?php

/**
 * EchonestProperties form base class.
 *
 * @method EchonestProperties getObject() Returns the current form's model object
 *
 * @package    streeme
 * @subpackage form
 * @author     Richard Hoar
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseEchonestPropertiesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                 => new sfWidgetFormInputHidden(),
      'song_id'            => new sfWidgetFormInputText(),
      'en_version'         => new sfWidgetFormInputText(),
      'en_date_added'      => new sfWidgetFormInputText(),
      'en_item_id'         => new sfWidgetFormInputText(),
      'en_artist_id'       => new sfWidgetFormInputText(),
      'en_song_id'         => new sfWidgetFormInputText(),
      'en_foreign_id'      => new sfWidgetFormInputText(),
      'en_audio_md5'       => new sfWidgetFormInputText(),
      'en_mode'            => new sfWidgetFormInputText(),
      'en_time_signature'  => new sfWidgetFormInputText(),
      'en_key'             => new sfWidgetFormInputText(),
      'en_duration'        => new sfWidgetFormInputText(),
      'en_loudness'        => new sfWidgetFormInputText(),
      'en_energy'          => new sfWidgetFormInputText(),
      'en_tempo'           => new sfWidgetFormInputText(),
      'en_danceability'    => new sfWidgetFormInputText(),
      'en_song_hotttnesss' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                 => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'song_id'            => new sfValidatorInteger(array('required' => false)),
      'en_version'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'en_date_added'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'en_item_id'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'en_artist_id'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'en_song_id'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'en_foreign_id'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'en_audio_md5'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'en_mode'            => new sfValidatorInteger(array('required' => false)),
      'en_time_signature'  => new sfValidatorInteger(array('required' => false)),
      'en_key'             => new sfValidatorInteger(array('required' => false)),
      'en_duration'        => new sfValidatorNumber(array('required' => false)),
      'en_loudness'        => new sfValidatorNumber(array('required' => false)),
      'en_energy'          => new sfValidatorNumber(array('required' => false)),
      'en_tempo'           => new sfValidatorNumber(array('required' => false)),
      'en_danceability'    => new sfValidatorNumber(array('required' => false)),
      'en_song_hotttnesss' => new sfValidatorNumber(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('echonest_properties[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'EchonestProperties';
  }

}
