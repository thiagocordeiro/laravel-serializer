<?php

namespace LaravelSerializer\Framework\Commands;

use Illuminate\Console\Command;

class SerializerClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'serializer:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush the serializer cache';

    public function handle(): void
    {
        $cache = sprintf("%s/serializer", config('serializer.cache'));

        $this->deleteDirectory($cache);

        $this->components->info('Serializer cache cleared successfully.');
    }

    private function deleteDirectory($directory): bool
    {
        if (!file_exists($directory)) {
            return true;
        }

        if (!is_dir($directory)) {
            return unlink($directory);
        }

        foreach (scandir($directory) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($directory . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($directory);
    }
}
