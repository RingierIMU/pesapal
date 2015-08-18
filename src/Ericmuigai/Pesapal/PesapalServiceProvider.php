<?php namespace Ericmuigai\Pesapal;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\UrlGenerator as URL;
class PesapalServiceProvider extends ServiceProvider {
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->booting(function()
        {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Pesapal', 'Ericmuigai\Pesapal\Facades\Pesapal');
        });
        $this->app['pesapal'] = $this->app->share(function($app)
        {
            return new Pesapal($app['view']);
        });
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }
}