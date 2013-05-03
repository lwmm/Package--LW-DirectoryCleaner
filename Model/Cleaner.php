<?php

namespace DirectoryCleaner\Model;

class Cleaner
{

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function execute()
    {
        if (!array_key_exists("directorycleaner", $this->config)) {
            throw new \DirectoryCleaner\Model\ConfigEntryIsMissing;
        }
        
        $cleanerConfig = $this->config["directorycleaner"];
        
        if ($cleanerConfig["archive"] == 1) {
            if(!is_dir($cleanerConfig["archive_path"])){
                throw new \DirectoryCleaner\Model\ArchivePathIsNotExisting;
            }
            if(!is_writable($cleanerConfig["archive_path"])){
                throw new \DirectoryCleaner\Model\ArchivePathIsNotWritable;
            }
        }
        
        $days = 30 * intval($cleanerConfig["month_of_saving"]);
        $expiringDate = date("Ymd", strtotime("-" . $days . " days"));

        foreach ($cleanerConfig["path"] as $path) {
            if (!is_dir($path)) {
                throw new \DirectoryCleaner\Model\AConfigPathIsNotAnDirectory;
            }
            else {
                $dir = \lw_directory::getInstance($path);
                $files = $dir->getDirectoryContents("file");

                foreach ($files as $file) {
                    $file->setDateFormat("Ymd");
                    if ($file->getDate() < $expiringDate) {
                        if ($cleanerConfig["debug"] == 1) {
                            if ($cleanerConfig["archive"] == 1) {
                                echo "ARCHIVE :" . $file->getName() . PHP_EOL;
                            }
                            else {
                                echo "DELETE : " . $file->getName() . PHP_EOL;
                            }
                        }
                        else {
                            if ($cleanerConfig["archive"] == 1) {
                                $archiveDir = \lw_directory::getInstance($cleanerConfig["archive_path"]);
                                $filename = $archiveDir->getNextFilename($file->getName());
                                $file->move($cleanerConfig["archive_path"], $filename);
                            }
                            else {
                                $file->delete();
                            }
                        }
                    }
                }
            }
        }

        
    }

}