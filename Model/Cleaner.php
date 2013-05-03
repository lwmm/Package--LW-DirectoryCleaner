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
            foreach ($cleanerConfig["archive_path"] as $archivePath) {
                if (!is_dir($archivePath)) {
                    throw new \DirectoryCleaner\Model\ArchivePathIsNotExisting;
                }
                if (!is_writable($archivePath)) {
                    throw new \DirectoryCleaner\Model\ArchivePathIsNotWritable;
                }
            }

            if (!array_key_exists("default_archive_path", $cleanerConfig)) {
                throw new \DirectoryCleaner\Model\NoDefaultArchivePath;
            }

            if (!is_dir($cleanerConfig["default_archive_path"])) {
                throw new \DirectoryCleaner\Model\DefaultArchivePathNotExisting;
            }
            if (!is_writable($cleanerConfig["default_archive_path"])) {
                throw new \DirectoryCleaner\Model\DefaultArchivePathNotWriteable;
            }
        }

        $days = 30 * intval($cleanerConfig["month_of_saving"]);

        $expiringDate = date("Ymd", strtotime("-" . $days . " days"));

        foreach ($cleanerConfig["path"] as $nr => $path) {
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
                                if (array_key_exists($nr, $cleanerConfig["archive_path"])) {
                                    $archiveDir = \lw_directory::getInstance($cleanerConfig["archive_path"][$nr]);
                                    $filename = $archiveDir->getNextFilename($file->getName());
                                    $file->move($cleanerConfig["archive_path"][$nr], $filename);
                                }
                                else {
                                    $archiveDir = \lw_directory::getInstance($cleanerConfig["default_archive_path"]);
                                    $filename = $archiveDir->getNextFilename($file->getName());
                                    $file->move($cleanerConfig["default_archive_path"], $filename);
                                }
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