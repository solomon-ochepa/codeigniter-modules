<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Delete a Module in Modular structure
 *
 * @package App\Commands
 * @author Solomon Ochepa <solomonochepa@gmail.com>
 */
class ModuleDelete extends BaseCommand
{
    /** @var String $group Group */
    protected $group       = 'Module';

    /** @var String $name Command's name */
    protected $name        = 'module:delete';

    /** @var String $description Command description */
    protected $description = 'Delete a module.';

    /** @var String $usage Command usage */
    protected $usage        = 'module:delete [module]';

    /**
     * @var String $example Command example */
    protected $example      = 'module:delete Example';

    /** @var Array $arguments the Command's Arguments */
    protected $arguments    = [
        'module'    => 'The module name.'
    ];

    /** @var Array $options the Command's Options */
    protected $options = [];

    /** Module Name */
    protected $module;

    /** @var String $module_path Modules absolute path. */
    protected $module_path;

    /** @var String $module_basename Modules relative path | basename. */
    protected $module_basename;

    /**
     * Run route:update CLI
     */
    public function run(array $params)
    {
        helper('inflector');

        // Module name
        while (!isset($params[0])) {
            CLI::error("NOTICE:\t\tThe Module name field is required.");

            CLI::write("USAGE:\t\t{$this->usage}", "green");
            CLI::write("EXAMPLE:\t{$this->example}\n", "green");

            $input = CLI::prompt('Module');
            if (CLI::strlen($input)) {
                $params[0] = $input;
            }
        }

        if (strlen(preg_replace('/[^A-Za-z0-9]+/', '', $params[0])) <> mb_strlen($params[0])) {
            CLI::error("Module name must be plain ascii characters A-z, and can contain numbers 0-9");
            return;
        }

        $this->module               = $this->str_title($params[0]);
        $this->module_basename      = basename(APPPATH) . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $this->module;
        $this->module_path          = APPPATH . '..' . DIRECTORY_SEPARATOR . $this->module_basename;

        // Confirm that module exists.
        if (!is_dir($this->module_path)) {
            CLI::error("Module [{$this->module}] not found.\n");
            return;
        }

        $this->module_path = realpath($this->module_path);

        try {
            $this->deleteModule();
        } catch (\Exception $e) {
            CLI::error($e);
        }
    }

    /**
     * Delete a Module
     */
    protected function deleteModule()
    {
        if (is_dir($this->module_path)) {
            delete_files($this->module_path, true, false, true);
            rmdir($this->module_path);

            CLI::write("Module [{$this->module}] deleted successfully.\n");
        } else {
            CLI::error("Module not found.");
        }
    }

    public function str_title(string $text)
    {
        $words = explode('_', $text);
        $titleCaseWords = array_map('ucfirst', $words);
        $titleCase = implode('', $titleCaseWords);

        // Remove any leading underscores
        $titleCase = ltrim($titleCase, '_');

        return $titleCase;
    }
}
