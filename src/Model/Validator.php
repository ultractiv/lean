<?
/**
 * Created by PhpStorm.
 * User: agbetunsin
 * Date: 4/6/14
 * Time: 10:33 AM
 */

namespace Lean\Model;

trait Validator {

  protected $validator; // instance of Validator

  protected $validations = array(); // array of model level validations

  protected $invalid = false; // bool flag to hold the validation state of the model

  protected $attrsToValidate = array(); // collects attrs to be validated before saving to model

  public function isValid(){
    return !$this->invalid;
  }

  public function getValidationError(){
    return $this->validator->getError();
  }

  protected function setValidationError($err){
    $this->invalid = true;
    $this->validator->setError($err);
    return $this; // for chaining;
  }

  protected function validate(array $attrs){

    $this->attrsToValidate = $attrs;

    if ($this->id)
      $this->validateOnUpdate();

    else $this->validateOnCreate();

  }

  protected function validateOnCreate(){

    if (!array_key_exists('create', $this->validations)) return;

    $validations = $this->validations['create'];

    if (!is_array($validations)) return;

    if (array_key_exists('presence', $validations)) {
      if (!$this->validator->validatePresence($validations['presence'], $this->attrsToValidate))
        return $this->invalid = true;
    }

    if (array_key_exists('email', $validations)) {
      if (!$this->validator->validateEmail($this->attrsToValidate[ $validations['email'] ]))
        return $this->invalid = true;
    }

    if (array_key_exists('password', $validations)) {
      if (!$this->validator->validatePassword($this->attrsToValidate[ $validations['password'] ]))
        return $this->invalid = true;
    }

    if (array_key_exists('unique', $validations)) {
      $field = $validations['unique'];
      $value = $this->attrsToValidate[ $field ];
      if ($this->count( array( $field => $value))){
        $this->validator->setError("{$value} already exists");
        return $this->invalid = true;
      }
    }

    if (array_key_exists('unique_together', $validations)) {
      $fields = explode(' ', $validations['unique_together']);
      // build array of attributes to search by
      $attrs = array();
      foreach ($fields as $field) $attrs[$field] = $this->attrsToValidate[$field];
      if ( $this->count( $attrs ) ){
        $this->validator->setError("Entry already exists");
        return $this->invalid = true;
      }
    }

    if (array_key_exists('file', $validations) && isset($this->attrsToValidate[ $this->_files ])) {
      foreach ($validations['file'] as $field => $validation) {

        $file = $this->attrsToValidate[ $this->_files ][$field];

        if (array_key_exists('presence', $validation ) && $validation['presence'] == true) {
          if (!$this->validator->validateFilePresence($file))
            return $this->invalid = true;
        }

        if (array_key_exists('types', $validation )) {
          if (!$this->validator->validateFileType($file, $validation['types']))
            return $this->invalid = true;
        }

        if (array_key_exists('size', $validation )) {
          if (!$this->validator->validateFileSize($file, $validation['size']))
            return $this->invalid = true;
        }

      }

      unset( $this->attrsToValidate[ $this->_files ] );
    }

    $this->attrs = $this->attrsToValidate;

  }

  protected function  validateOnUpdate(){
    if (!array_key_exists('update', $this->validations)) return;

    $validations = $this->validations['update'];

    if (!is_array($validations)) return;

    if (array_key_exists('not_null', $validations)) {
      $fields = explode($validations['not_null'], ' ');
      foreach($fields as $field)
        if (array_key_exists($field, $this->attrsToValidate))
          if (!$this->validator->validateEmpty($this->attrsToValidate[ $field ], $field))
            return $this->invalid = true;
    }

    if (array_key_exists('phone', $validations)) {
      $fields = explode($validations['phone'], ' ');
      foreach($fields as $field)
        if (array_key_exists($field, $this->attrsToValidate))
          if (!$this->validator->validatePhone($this->attrsToValidate[ $field ]))
            return $this->invalid = true;
    }

  }


} 