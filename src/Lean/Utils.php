<?

namespace Lean;

class Utils {

  public static function content_type($filename) {
    if (is_callable('finfo')) {
      $finfo = new \finfo;
      return $finfo->file($filename, \FILEINFO_MIME_TYPE);
    }
    else if (is_callable('mime_content_type')) {
      return mime_content_type($filename);
    }
    else {
      $types = array(
        '.doc'  => 'application/doc',
        '.docx' => 'application/docx',
        '.pdf'  => 'application/pdf'
      );
      return $types[ strtolower ( strrchr ( $filename, '.' ) ) ];
    }
  }

}