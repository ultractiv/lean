<?

namespace Lean\Model;

interface ModelInterface {

  function __construct(array $attrs = null) ;

  function __call($method, $args) ;

  function init() ;

  function setAttrs(array $attrs) ;

  function protectAttrs() ;

  function protectMany(array $results) ;

  static function buildQuery($table, array $attrs, $bindParams = false, $queryType = 'select') ;

  function count(array $attrs) ;
  
  function findOne(array $attrs) ;
  
  function findMany(array $attrs, $asObjects = false) ;
  
  function findAll(array $joins = null, $joined_fields = '') ;

  function beforeCreate(array &$attrs) ;

  function afterCreate() ;

  function beforeSave() ;

  function afterSave() ;

  function save(array $attrs) ;
    
  function _create(array $attrs) ;
  
  function _update(array $attrs) ;
  
  static function deleteWhere(array $attrs) ;

  function beforeDestroy() ;

  function afterDestroy() ;

  function destroy() ;
  
  function delete() ;

  static function getClassName() ;

  function upload($source, $destination, $name = '') ;

  function __get($name) ;

  function attrs() ;

  function toJSON() ;

  function clean(array $attrs) ;
  
}