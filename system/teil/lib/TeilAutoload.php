<?php namespace Teil\Lib;


/**
* Load all the teil files
*/
class TeilAutoload 
{
    // Result files
    private $all_files = array();

    // These files should be loadded first
    private $priority_files = array();

    // These files should be excluded at all
    private $files_to_exclude = array();

    // Result loader files look like this
    private $result_files = array();

    // Maximum number of files to be loaded
    private $totalLoadFileLimit = 100;


    public function __construct($priority_files = array(), $files_to_exclude = array())
    {
        $this->priority_files = $priority_files;
        $this->files_to_exclude = $files_to_exclude;
    }


    /**
     * Get all the file in directory (/teil/)
     *
     * @return void
     */
    private function recurse($main, $count = 0)
    {
        $dirHandle = opendir($main);

        while($file = readdir($dirHandle))
        {
            if(is_dir($main . $file . "/") AND $file != '.' AND $file != '..')
            {
                $count = $this->recurse($main . $file . "/", $count);
            }
            elseif (preg_match("/\.php$/i", $file))
            {
                $count++;
                $this->all_files[] = $main . $file;
            }
        }

        if ($count > $this->totalLoadFileLimit)
        {
            throw new \Exception("Too, many files to include");
        }
    }


    /**
     * Exlude files
     *
     * @return void
     */
    private function excludeFiles()
    {
        foreach ($this->files_to_exclude as $file_exclude)
        {
            foreach ($this->all_files as $key => $file_path)
            {
                // Get filename from path (/teil/core/App.php ---> App.php)
                $filename_regexp = preg_match("/\/([^\/]+?\.php)$/i", $file_path, $matches);
                $filename = empty($matches[1]) ? NULL : $matches[1];

                if ($filename == $file_exclude)
                {
                    unset($this->all_files[$key]);
                    break;
                }
            }
        }
    }


    /**
     * Place priority files first
     *
     * @return void
     */
    private function prioritiorizeFiles()
    {
        foreach ($this->priority_files as $priority_key => $file_proprity_name)
        {
            foreach ($this->all_files as $key => $file_path)
            {
                // Get filename from path (/teil/core/App.php ---> App.php)
                $filename_regexp = preg_match("/\/([^\/]+?\.php)$/i", $file_path, $matches);
                $filename = empty($matches[1]) ? NULL : $matches[1];

                if ( ! $filename)
                {
                    continue;
                }

                if ($filename == $file_proprity_name)
                {
                    $this->result_files[] = $file_path;
                    unset($this->all_files[$key]);

                    break;
                }
            }
        }
    }


    /**
     * Check if directory exitst
     *
     * @return void
     */
    private function directoryExists($directory)
    {
        if ( ! is_dir($directory))
        {
            throw new \Exception("No such directory"); die();
        }
    }


    /**
     * Get the result paths
     *
     * @return array
     */
    public function getLoaderPaths($base_directory)
    {
        $this->directoryExists($base_directory);

        $this->recurse($base_directory);

        $this->excludeFiles();
        $this->prioritiorizeFiles();

        $this->result_files = array_merge($this->result_files, $this->all_files);

        return $this->result_files;
    }

}