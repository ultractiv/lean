<?

namespace Lean\Model;

interface Interface {

  protected function __construct(array $attrs = null) {}

  public function __call($method, $args) {}

  protected function init() {}

  protected function setAttrs(array $attrs) {}

  private function protectAttrs() {}

  protected function protectMany(array $results) {}

  private static function buildQuery($table, array $attrs, $bindParams = false, $queryType = 'select') {}

  protected function count(array $attrs) {}
  
  protected function findOne(array $attrs) {}
  
  protected function findMany(array $attrs, $asObjects = false) {}
  
  protected function findAll(array $joins = null, $joined_fields = '') {}

  protected function beforeCreate(array &$attrs) {}

  protected function afterCreate() {}

  protected function beforeSave() {}

  protected function afterSave() {}

  public function save(array $attrs) {}
    
  private function _create(array $attrs) {}
  
  private function _update(array $attrs) {}
  
  public static function deleteWhere(array $attrs) {}

  protected function beforeDestroy() {}

  protected function afterDestroy() {}

  public function destroy() {}
  
  protected function delete() {}

  protected static function getClassName() {}

  protected function upload($source, $destination, $name = '') {}

  public function __get($name) {}

  public function attrs() {}

  public function toJSON() {}

  protected function clean(array $attrs) {}
  
}