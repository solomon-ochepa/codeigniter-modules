<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Create a Module in Modular structure
 *
 * @package App\Commands
 * @author Solomon Ochepa <solomonochepa@gmail.com>
 */
class ModuleMake extends BaseCommand
{
    /**
     * Group
     *
     * @var string
     */
    protected $group       = 'Module';

    /**
     * Command's name
     *
     * @var string
     */
    protected $name        = 'module:make';

    /**
     * Command description
     *
     * @var string
     */
    protected $description = 'Generates a new Module.';

    /**
     * Command usage
     *
     * @var string
     */
    protected $usage        = 'module:make [name] [options]';

    /**
     * Command example
     *
     * @var string
     */
    protected $example      = 'module:make Example';

    /**
     * the Command's Arguments
     *
     * @var array
     */
    protected $arguments    = ['name' => 'Module name to be created'];

    /**
     * the Command's Options
     *
     * @var array
     */
    protected $options      = [
        '-path' => 'Set module folder other than app/Modules',
        '-c' => 'Create only - [r]oute, [c]ontroller, [l]ibrary, [m]odel, [v]iew, [o]ther dirs'
    ];

    /**
     * Module Name to be Created
     */
    protected $module;
    protected $module_plural;
    protected $module_lower;
    protected $module_lower_plural;
    protected $module_upper;
    protected $module_upper_plural;

    /**
     * Module folder (default /Modules)
     */
    protected $modules_rootdir;

    /** @var String $modules_basename Modules root dir. */
    protected $modules_basename;

    /**
     * View folder (default /View)
     */
    protected $view_folder;

    /**
     * Run route:update CLI
     */
    public function run(array $params)
    {
        helper('inflector');

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

        // Get custom Modules path.
        $custom_basename = preg_replace('/[^A-Za-z0-9]+/', '', CLI::getOption('f'));

        // Set Modules relative path / basename
        $this->modules_basename = $custom_basename ? ucfirst($custom_basename) : basename(APPPATH) . DIRECTORY_SEPARATOR . 'Modules';

        // Set Modules root dir (absolute path).
        $this->modules_rootdir = APPPATH . '..' . DIRECTORY_SEPARATOR . $this->modules_basename;

        // Create root dir if not exists.
        if (!is_dir($this->modules_rootdir)) {
            mkdir($this->modules_rootdir);
        }

        $this->modules_rootdir = realpath($this->modules_rootdir);

        $this->module               = ucfirst($params[0]);
        $this->module_plural        = plural($this->module);
        $this->module_lower         = strtolower($params[0]);
        $this->module_lower_plural  = plural($this->module_lower);
        $this->module_upper         = strtoupper($params[0]);
        $this->module_upper_plural  = plural($this->module_upper);

        CLI::write('Creating module: ' . $this->module . "\n");

        // Create the Module path.
        if (!is_dir($module_path = $this->modules_rootdir . DIRECTORY_SEPARATOR . $this->module)) {
            mkdir($module_path, 0777, true);
        }

        try {
            $this->createEntity();
            $this->createModel();
            $this->createConfig();
            $this->createView();

            if (CLI::getOption('c') == '' || strstr(CLI::getOption('c'), 'm')) {
                $this->createMigration();
            }

            if (CLI::getOption('c') == '' || strstr(CLI::getOption('c'), 's')) {
                $this->createSeeder();
            }

            if (CLI::getOption('c') == '' || strstr(CLI::getOption('c'), 'c')) {
                $this->createController();
            }

            if (CLI::getOption('c') == '' || strstr(CLI::getOption('c'), 'o')) {
                $this->createLibrary();
                $this->createOtherDirs();
            }

            $this->updateAutoload();

            CLI::write("Module [{$this->module}] created!\n");
        } catch (\Exception $e) {
            CLI::error($e);
        }
    }

    protected function createMigration()
    {
        command("module:migration Create{$this->module_plural}Table {$this->module}");
    }

    protected function createSeeder()
    {
        command("module:seeder {$this->module} {$this->module}");
    }

    /**
     * Create Config File
     */
    protected function createConfig()
    {
        command("module:route {$this->module}");
    }

    /**
     * Create controller file
     */
    protected function createController()
    {
        command("module:controller {$this->module}Controller {$this->module}");
    }

    /**
     * Create models file
     */
    protected function createEntity()
    {
        command("module:entity {$this->module} {$this->module}");
    }

    /**
     * Create models file
     */
    protected function createModel()
    {
        command("module:model {$this->module} {$this->module}");
    }

    /**
     * Create library file
     */
    protected function createLibrary()
    {
        command("module:library {$this->module}Library {$this->module}");
    }

    /**
     * Create View
     */
    protected function createView()
    {
        $viewPath = $this->createDir('Views');

        if (!file_exists($viewPath . DIRECTORY_SEPARATOR .  'index.php')) {
            $template =
                "<div class=\"container\">
    <div class=\"row\">
        <div class=\"col col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12\">
            Module: <strong>{$this->module}</strong>
            <hr />
            <ul>
                <?php foreach (\${$this->module_lower_plural} ?? [] as \$key => \${$this->module_lower}) : ?>
                    <li><?= \${$this->module_lower}->id ?>: <?= \${$this->module_lower}->title ?? '{title}' ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>";

            file_put_contents($viewPath . DIRECTORY_SEPARATOR .  'index.php', $template);
        } else {
            CLI::error("Index view allready exists!");
        }
    }

    /**
     * function createOtherDirs
     *
     * Create other dirs
     */
    protected function createOtherDirs()
    {
        $this->createDir('Filters', true);
        $this->createDir('Language', true);
        $this->createDir('Validation', true);
    }

    /**
     * function createDir
     *
     * Create directory and set, if required, gitkeep to keep this in git.
     *
     * @param type $folder
     * @param type $gitkeep
     * @return string
     */

    protected function createDir($folder, $gitkeep = false)
    {
        $dir = $this->modules_rootdir . DIRECTORY_SEPARATOR . $this->module . DIRECTORY_SEPARATOR .  $folder;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
            if ($gitkeep) {
                file_put_contents($dir .  '/.gitkeep', '');
            }
        }

        return $dir;
    }

    /**
     * function updateAutoload
     *
     * Add a psr4 configuration to Config/Autoload.php file
     *
     * @return boolean
     */

    protected function updateAutoload()
    {
        $Autoload = new \Config\Autoload();
        $psr4 = $Autoload->psr4;
        if (isset($psr4[$this->module])) {
            return false;
        }
        $file = fopen(APPPATH . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Autoload.php', 'r');
        if (!$file) {
            CLI::error("Config/Autoload.php nor readable!");
            return false;
        }

        $newcontent = '';
        $posfound = false;
        $posline = 0;

        if (CLI::getOption('f') == '') {
            $psr4Add = "\t\t'{$this->module}' => APPPATH . 'Modules/{$this->module}',";
        } else {
            $psr4Add = "\t\t'{$this->module}' => ROOTPATH . '{$this->modules_basename}/{$this->module}',";
        }

        while (($buffer = fgets($file, 4096)) !== false) {
            if ($posfound && strpos($buffer, ']')) {
                //Last line of $psr4
                $newcontent .= $psr4Add . "\n";
                $posfound = false;
            }
            if ($posfound && $posline > 3 && substr(trim($buffer), -1) != ',') {
                $buffer = str_replace("\n", ",\n", $buffer);
            }
            if (strpos($buffer, 'public $psr4 = [')) {
                $posfound = true;
                $posline = 1;
                //First line off $psr4
            }
            if ($posfound) {
                $posline++;
            }
            $newcontent .= $buffer;
        }

        $file = fopen(APPPATH . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Autoload.php', 'w');
        if (!$file) {
            CLI::error("Config/Autoload.php nor writable!");
            return false;
        }
        fwrite($file, $newcontent);
        fclose($file);

        return true;
    }
}
