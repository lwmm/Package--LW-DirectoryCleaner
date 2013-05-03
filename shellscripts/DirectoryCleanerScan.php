<?php

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);

$config = parse_ini_file("/var/www/c38/lw_configs/conf.inc.php", true);

class lwBaseAutoloader
{

    public function __construct($config)
    {
        $this->config = $config;
        spl_autoload_register(array($this, 'loader'));
    }

    private function loader($className)
    {
        if (strtolower(substr($className, 0, 6)) == "build_") {
            if (is_file($this->config['path']['builder'] . $className . ".class.php")) {
                include_once($this->config['path']['builder'] . $className . ".class.php");
            }
        }
        else {
            if (strstr($className, 'DirectoryCleaner')) {
                $path = $this->config["path"]["package"] . "LwDirectoryCleaner";
                $filename = str_replace('DirectoryCleaner', $path, $className);
                $filename = str_replace('\\', '/', $filename) . '.php';
                if (is_file($filename)) {
                    include_once($filename);
                }
            }
        }
    }

}

$autoloader = new lwBaseAutoloader($config);
$builder = new build_autoloadregistry($config);

$directoryCleaner = new \DirectoryCleaner\Model\Cleaner($config);

try {
    $directoryCleaner->execute();
} catch (\DirectoryCleaner\Model\AConfigPathIsNotAnDirectory $exc) {
    die("Ein der in der Config eingetragenen Pfade ist kein Verzeichnis.");
} catch (\DirectoryCleaner\Model\ArchivePathIsNotExisting $exc) {
    die("Der Archivpfad ist kein Verzeichnis.");
} catch (\DirectoryCleaner\Model\ArchivePathIsNotWritable $exc) {
    die("Das Archiv-Verzeichnis ist nicht beschreibar.");
} catch (\DirectoryCleaner\Model\ConfigEntryIsMissing $exc) {
    die("DirectoryCleaner Config-Eingtrag.");
}