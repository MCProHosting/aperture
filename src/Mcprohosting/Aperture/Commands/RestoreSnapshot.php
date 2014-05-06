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
                $selection[] = $file;
            }
        }

        if (count($selection) === 0) {
            return $this->error('No snapshots found.');
        } elseif (count($selection) === 1) {
            $choice = 0;
        } else {
            foreach ($selection as $key => $file) {
                echo '[' . $key . '] Snapshot from ';
                $parts = explode('_', $file);
                echo date('H:i, M jS', $parts[0]) . "\n";
            }

            $choice = (int) $this->ask('Which snapshot do you want to restore from? ');
        }

        if ($this->snapshot->hasRows($database, $this->argument('table'), $this->option('chunk'))
            && !$this->confirm('This will clear any existing data in ' . $this->argument('table') . '. Continue? [y|N]', false)
        ) {
            return $this->error('Restoration aborted');
        }

        $file = fopen(storage_path() . '/aperture/' . $selection[$choice], 'r');

        $this->snapshot->handle = $file;
        $this->snapshot->restore($database, $this->argument('table'), $this->option('chunk'));

        fclose($file);
        $this->info('Snapshot restored!');
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
