<?

namespace Lean\Model;

use \Lean\Cache;
use \Lean\DB;
use \Lean\Logger;
use \Lean\Notifier;
use \Lean\Validator as ValidatorBase;

class Base {

  use Validator;

  protected $primary_key = 'id'; // primary key field
  protected $table; // table name of model

  protected $relationships = array(); // an array of model relationships

  /*
  protected $relationships = array(
    'belongs_to_one'  => 'user', --> $paper->author
    'belongs_to_many' => 'journals', --> $paper->journals
    'has_many' => 'assessments assignments', --> $paper->assessments | $paper->assignments
    'has_one'  => 'paper' --> $
  );
  */

  protected $no_backend = false;
  protected $no_cache = false;
  protected $no_validate = false;

  protected $db; // instance of Db
  protected $cache; // instance of Cache
  protected $notifier; // instance of Notifier

  protected $order = 'created_at desc';

  protected $attrs = array ();
  protected $_attrs = array (); // collects protected_attributes
  protected $protected_attrs = ""; // comma-separated list of attrs to hide from public
  protected $_temp = array (); // collects attributes created internally before saving model e.g. file upload metadata


  protected $_files = 'files_to_upload'; // holds reference to files to upload

  protected $errors = '';

  protected function __construct($attrs = null) {

    if (!$this->no_backend) {
      if (! $this->table)
        $this->table = strtolower ( get_class($this) )."s";
      $this->db = DB::instance ();
    }

    if (!$this->no_cache) {
      $this->cache = Cache::instance ();
    }

    $this->notifier = Notifier::instance ();
    $this->validator = ValidatorBase::instance ();

    if ($attrs && is_numeric ( $attrs ))
      // assume $attrs is id
      $this->findOne( array( $this->primary_key => $attrs ) );

    else if ($attrs && is_array ( $attrs )) {

      if (!array_key_exists($this->primary_key, $attrs)) {
        $this->validate($attrs);
      }

      // set the attrs
      $this->attrs = $attrs;
      $this->protectAttrs();

    }
  }

  public function __call($method, $args){
    if (method_exists($this, $method)) $this->$method($args);
    else throw new Exception(__CLASS__." has no '{$method}' method");
  }

  protected function init(){}

  private function protectAttrs() {
    $protected = explode ( ' ', $this->protected_attrs );

    foreach ( $protected as $attr ) {
      if (isset ( $this->attrs [$attr] )) {
        $this->_attrs [$attr] = $this->attrs [$attr];
        unset ( $this->attrs [$attr] );
      }
    }
  }

  protected function protectMany($results){

    if (!$this->protected_attrs || $this->protected_attrs == '')
      return $results;

    $attrs = explode(' ', $this->protected_attrs);
    $return = array();
    foreach ($results as $result) {
      foreach ($attrs as $attr)
        unset($result[$attr]);
      array_push($return, $result);
    }
    return $return;

  }

  private static function buildQuery($table, array $attrs, $bindParams = false, $queryType = 'select'){

    if ($queryType == 'select')
      $query = "select * from {$table} where ";

    else if ($queryType == 'delete')
      $query = "delete from {$table} where ";

    $_attrs = $attrs;
    $lastEntry = array_pop($attrs);

    switch ($bindParams) {

      case true:

        foreach ( $attrs as $key => $value ) {
          $query .= " $key='$value', ";
        }

        if (count($_attrs) > 1)
          $query = substr_replace($query, ' and ', -2);

        $query .= array_search($lastEntry, $_attrs) . "='" . $lastEntry ."' ";

        break;

      case false:

      default:

        $query .= join ( "=?, ", array_keys ( $attrs ) );

        if (count($_attrs) > 1)
          $query = substr_replace($query, ' and ', -2);

        $query .= array_search($lastEntry, $_attrs) . "=? ";

        break;

    }
    Logger::logQuery($query, $_attrs);
    return $query;
  }

  /*private function buildQuery(array $attrs, $bind = false, $queryType = 'select') {
    $query = "{$queryType} * from {$this->table} where ";
    $_attrs = $attrs;
    $lastEntry = array_pop($attrs);
    switch ($bind) {
      case true:
        foreach ( $attrs as $key => $value ) {
          $query .= " $key='$value', ";
        }
        if (count($_attrs) > 1) $query = substr_replace($query, ' and ', -2);
        $query .= array_search($lastEntry, $_attrs) . "='" . $lastEntry ."' ";
        break;
      case false:
      default:
        $query .= join ( "=?, ", array_keys ( $attrs ) );
        if (count($_attrs) > 1)
          $query = substr_replace($query, ' and ', -2);
        $query .= array_search($lastEntry, $_attrs) . "=? ";
        break;
    }
    Logger::logQuery($query, $_attrs);
    return $query;
  }*/

  protected function count(array $attrs) {
    try {
      $select = self::buildQuery($this->table, $attrs, true);
      $io = $this->db->query ( $select );
      return $io->rowCount ();
    } catch ( \PDOException $e ) {
      Logger::log($e);
    }
  }

  // get/set cache
  protected function findOne(array $attrs) {
    try {
      $select = self::buildQuery($this->table, $attrs) . "limit 1";
      $io = $this->db->prepare ( $select );
      if ($io->execute ( array_values ( $attrs ) ))
        while ( $row = $io->fetch ( \PDO::FETCH_ASSOC ) ) {
          $this->attrs = $row;
          // $this->id = $row [$this->primary_key];
          $this->protectAttrs ();
          $this->init();
          return $this;
        }
    } catch ( \PDOException $e ) {
      Logger::log($e);
    }
  }

  // get/set cache
  protected function findMany(array $attrs, $asObjects = false) {
    try {
      $select = self::buildQuery($this->table, $attrs);
      if ($this->order != '') $select .= "order by {$this->table}.{$this->order}";
      $io = $this->db->prepare ( $select );

      if ($io->execute ( array_values ( $attrs ) )) {

        $results = $io->fetchAll ( \PDO::FETCH_ASSOC );

        if (!$asObjects) return $this->protectMany( $results );

        // return each row as instantiate of the class

        $return = array();
        $class = get_class($this);

        foreach ($results as $attrs) array_push($return, $class::instantiate($attrs));

        return $return;

      }
    } catch ( \PDOException $e ) {
      Logger::log($e);
    }
  }

  // get/set cache
  protected function findAll(array $joins = null, $joined_fields = "") {
    try {
      $select = "select {$this->table}.* {$joined_fields} from {$this->table}";
      if ($joins != null)
        foreach ($joins as $table => $foreign_key)
          $select .= " left join {$table} on {$this->table}.{$foreign_key} = {$table}.id";
      if ($this->order != '') $select .= " order by {$this->table}.{$this->order}";

      //Logger::logQuery($select);
      $io = $this->db->prepare ($select);
      if ($io->execute ())
        return $this->protectMany($io->fetchAll ( \PDO::FETCH_ASSOC ));
    } catch ( \PDOException $e ) {
      Logger::log($e);
    }
  }

  public function save(array $attrs) {
    if (! $this->id)
      return $this->_create ( array_merge ( $attrs, $this->_temp ) );

    // revalidate before updating model
    $this->validate($attrs);
    // save only when model is valid
    if ($this->isValid()) return $this->_update ( array_merge ( $attrs, $this->_temp ) );
  }

  /*public static function update(array $searchCriteria, array $updateFields) {
      // not yet implemented
      try {
        $update = "update {$this->table} set " . join ( "=?,", array_keys ( $attrs ) ) . "=? where {$this->primary_key}=?";
      }
      catch (Exception $e){

      }
  }*/

  // set cached
  private function _create(array $attrs) {
    try {

      $attrs = $this->clean($attrs);

      $this->db->beginTransaction ();

      $insert = "insert into {$this->table} (" . join ( ",", array_keys ( $attrs ) ) . ") ";
      $values = " values ('" . join ( "','", array_values ( $attrs ) ) . "')";
      $query = "$insert $values";
      if ($this->db->exec ( $query )) {
        $id = $this->db->lastInsertId ();
        $this->db->commit ();
        $this->attrs = $attrs;
        $this->attrs [$this->primary_key] = $id;
        $this->protectAttrs ();
        $this->init();
        return true;
      }
    } catch ( \PDOException $e ) {
      $this->db->rollBack ();
      Logger::log($e);
    }
  }

  // update cached value
  private function _update(array $attrs) {
    try {
      $attrs = $this->clean($attrs);
      $update = "update {$this->table} set " . join ( "=?,", array_keys ( $attrs ) ) . "=? where {$this->primary_key}=?";
      $io = $this->db->prepare ( $update );
      if ($io->execute ( array_merge ( array_values ( $attrs ), array ( $this->id ) ) )) {
        $this->attrs = array_merge ( $this->attrs, $attrs );
        $this->protectAttrs ();
        return true;
      }
    } catch ( \PDOException $e ) {
      Logger::log($e);
    }
  }

  // delete entries from the database at a time
  public static function deleteWhere(array $attrs) {
    $db = DB::instance();
    $table = strtolower ( static::getClassName() )."s";
    try {
      $query = static::buildQuery($table, $attrs, true, 'delete');
      $db->exec($query);
      return true;
    } catch ( \PDOException $e ) {
      Logger::log($e);
    }
  }

  // delete cached value
  protected function delete() {
    $query = "delete from {$this->table} where {$this->primary_key}={$this->id}";
    $this->db->exec($query);
    empty($this->attrs);
    empty($this->_attrs);
    return true;
  }

  protected static function getClassName(){
    return \__CLASS__ ;
  }

  public function destroy() {
    $this->delete();
  }

  protected function upload($source, $destination, $name = '') {

    $ext = strtolower ( strrchr ( $source ['name'], '.' ) );

    if (! $name) $name = uniqid ( $this->table . '-' );

    if (move_uploaded_file ( $source ['tmp_name'], $destination . $name . $ext )) {
      $this->_temp ['filesize'] = ceil ( $source ['size'] / 1000 ) . "kb";
      $this->_temp ['filetype'] = $ext;
      $this->_temp ['filepath'] = $name . $ext;
      $this->_temp ['filename'] = $name;
      return true;
    } else {
      unlink ( $source ['tmp_name'] );
      $this->setValidationError("Could not upload file");
      return false;
    }

  }

  public function __get($name) {
    if (array_key_exists ( $name, $this->attrs ))
      return $this->attrs [$name];
    return null;
  }

  public function attrs() {
    return $this->attrs;
  }

  public function toJSON(){
    return json_encode($this->attrs);
  }

  protected function clean(array $attrs) {
    $_attrs = array();
    foreach($attrs as $key => $value)
      if (!empty($value)) {
        if ( ini_get('magic_quotes_gpc') ) $value = \stripslashes($value);
        $value = htmlspecialchars(strip_tags($value));
        $_attrs[$key] = htmlentities($value, \ENT_QUOTES);
      }
    return $_attrs;
  }

}
