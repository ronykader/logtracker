<?php

namespace Obd\Logtracker\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class InstallLogtrackerTraitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logtracker:install-trait 
                            {--dir=app/Models : The directory to scan for models} 
                            {--dry-run : Show what would be changed without modifying files} 
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically add the Logtrackerable trait to all Eloquent models in the specified directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directory = base_path($this->option('dir'));

        if (!is_dir($directory)) {
            $this->error("Directory not found: {$directory}");
            return Command::FAILURE;
        }

        $files = $this->findModelFiles($directory);

        if (empty($files)) {
            $this->info("No PHP files found in {$directory}.");
            return Command::SUCCESS;
        }

        $this->info("Scanning " . count($files) . " files in " . $this->option('dir') . "...");
        
        $toUpdate = [];
        foreach ($files as $file) {
            if ($this->shouldUpdateModel($file)) {
                $toUpdate[] = $file;
            }
        }

        if (empty($toUpdate)) {
            $this->info("All models already have the Logtrackerable trait. No changes needed.");
            return Command::SUCCESS;
        }

        $this->comment("Found " . count($toUpdate) . " models to update:");
        foreach ($toUpdate as $file) {
            $this->line(" - " . str_replace(base_path(), '', $file));
        }

        if ($this->option('dry-run')) {
            $this->info("\n--- Dry Run: No files were modified ---");
            return Command::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('Do you want to proceed with the updates?', true)) {
            $this->warn("Operation cancelled.");
            return Command::SUCCESS;
        }

        $this->info("Updating models...");
        $updatedCount = 0;

        foreach ($toUpdate as $file) {
            if ($this->updateModelFile($file)) {
                $updatedCount++;
            }
        }

        $this->info("Successfully added Logtrackerable trait to {$updatedCount} models.");
        return Command::SUCCESS;
    }

    /**
     * Recursively find all PHP files in the directory.
     */
    private function findModelFiles($directory): array
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        $regex = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);
        
        $files = [];
        foreach ($regex as $match) {
            $files[] = $match[0];
        }
        return $files;
    }

    /**
     * Determine if the file is an Eloquent Model and missing the trait.
     */
    private function shouldUpdateModel(string $path): bool
    {
        $content = file_get_contents($path);

        // Must be a class that extends Model
        if (!preg_match('/class\s+\w+\s+extends\s+Model/i', $content)) {
            return false;
        }

        // Must not already use Logtrackerable
        if (preg_match('/use\s+Logtrackerable\b/i', $content)) {
            return false;
        }

        return true;
    }

    /**
     * Inject the trait into the model file.
     */
    private function updateModelFile(string $path): bool
    {
        $content = file_get_contents($path);

        // 1. Add namespace import if missing
        if (!preg_match('/use\s+Obd\\\\Logtracker\\\\Traits\\\\Logtrackerable\b/i', $content)) {
            $import = "\nuse Obd\\Logtracker\\Traits\\Logtrackerable;";
            
            // Insert after namespace or first use statement
            if (preg_match('/namespace\s+[^;]+;/', $content, $matches)) {
                $content = str_replace($matches[0], $matches[0] . $import, $content);
            }
        }

        // 2. Add trait use statement inside the class
        $traitLine = "\n    use Logtrackerable;";
        
        // Find the opening brace of the class
        if (preg_match('/(class\s+\w+\s+extends\s+Model[^{]*{)/i', $content, $matches)) {
            $content = str_replace($matches[0], $matches[0] . $traitLine, $content);
        }

        return file_put_contents($path, $content) !== false;
    }
}
