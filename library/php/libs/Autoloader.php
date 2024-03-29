<?php
namespace libs;

/**
 * Autoload.
 */
class Autoloader{


    /**
     * Load files by namespace.
     *
     * @param string $name
     * @return boolean
     */
    public static function loadByNamespace($className){
        $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $className);

        if (strstr($className, '\\')) {
            $class_file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . "$class_path.php";
        } else{
            $class_file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . "$class_path.php";
        }

        if (is_file($class_file)) {
            require_once($class_file);
            if (class_exists($className, false)) {
                return true;
            }
        }
        return false;
    }
//    public static function loadByNamespace($className){
//        $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $className);
//        if (strpos($className, 'models\\') === 0 || strpos($className, 'libs\\') === 0 || strpos($className, 'configs\\') === 0) {
//            $class_file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . "$class_path.php";
//        } else{
//            $class_file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . "$class_path.php";
//        }
//
//
//        if (is_file($class_file)) {
//            require_once($class_file);
//            if (class_exists($className, false)) {
//                return true;
//            }
//        }
//        return false;
//    }
}

spl_autoload_register('\libs\Autoloader::loadByNamespace');