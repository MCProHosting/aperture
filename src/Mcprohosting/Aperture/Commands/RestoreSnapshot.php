<?php namespace Mcprohosting\Aperture\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Mcprohosting\Aperture\Snapshot;
use Illuminate\Config\Repository;

class RestoreSnapshot extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'snapshot:restore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restores a table snapshot.';

    /**
     * @var \Mcprohosting\Aperture\Snapshot
     */
    protected $snapshot;

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @param \Mcprohosting\Aperture\Snapshot $snapshot
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(Snapshot $snapshot, Repository $config)
    {
        parent::__construct();

        $this->snapshot = $snapshot;
        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $database = $this->option('database') ?: $this->config->get('database.default');

        if (!is_dir(storage_path() . '/aperture')) {
            mkdir(storage_path() . '/aperture');
        }

        $selection = array();

        foreach (scandir(storage_path() . '/aperture', SCANDIR_SORT_DESCENDING) as $file) {
            if (strpos($file, $this->argument('table') . '_' . $database)) {

                echo '[' . count($selection) . '] Snapshot from ';
                $parts = explode('_', $file);
                echo date('H:i, M nS', $parts[0]) . "\n";

                $selection[] = $file;
            }
        }

        $this->ask('Which snapshot do you want to restore from? ');

        if ($this->confirm('This will clear any existing data in ' . $this->argument('table') . '. Continue? [yes|no]', false)) {
            $file = fopen(storage_path() . '/aperture/' . $selection[0], 'r');

            $this->snapshot->handle = $file;
            $this->snapshot->restore($database, $this->argument('table'), $this->option('chunk'));

            fclose($file);
            $this->info('Snapshot restored!');
        } else {
            $this->error('Restoration aborted');
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('table', InputArgument::REQUIRED, 'Table to snapshot.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('database', null, InputOption::VALUE_OPTIONAL, 'Database the table lives on.', null),
            array('chunk', null, InputOption::VALUE_OPTIONAL, 'How many rows to process at once.', 500),
        );
    }

}
