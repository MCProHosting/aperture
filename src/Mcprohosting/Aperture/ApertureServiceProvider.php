<?php namespace Mcprohosting\Aperture;

use Illuminate\Support\ServiceProvider;

class ApertureServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app->bind('Mcprohosting\Aperture\Snapshot',
            function($app) {
                return new Snapshot($app['db']);
            }
        );
		$this->app['command.snapshot.take'] = $this->app->share(
            function ($app) {
                return new Commands\TakeSnapshot($app['Mcprohosting\Aperture\Snapshot'], $app['config']);
            }
        );

        $this->app['command.snapshot.restore'] = $this->app->share(
            function ($app) {
                return new Commands\RestoreSnapshot($app['Mcprohosting\Aperture\Snapshot'], $app['config']);
            }
        );

        foreach ($this->provides() as $command) {
            $this->commands($command);
        }
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array(
            'command.snapshot.take',
            'command.snapshot.restore'
        );
	}
}
