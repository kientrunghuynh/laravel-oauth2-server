<?php namespace App\Providers;

use App\Oauth\ScopeStorage;
use App\Oauth\ClientStorage;
use App\Oauth\SessionStorage;
use App\Oauth\AuthCodeStorage;
use App\Oauth\AccessTokenStorage;
use App\Oauth\RefreshTokenStorage;
use Illuminate\Support\ServiceProvider;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;

class OauthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any Oauth services
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any Oauth services.
     *
     * @return void
     */
    public function register()
    {
        $this->authorisation();

        $this->resource();
    }

    /**
     * Register the Authorisation Server
     *
     * @return void
     */
    private function authorisation()
    {
        $this->app->singleton('League\OAuth2\Server\AuthorizationServer', function ($app) {
            $server = new AuthorizationServer;

            $server->setSessionStorage(new SessionStorage($app->make('db')));
            $server->setAccessTokenStorage(new AccessTokenStorage($app->make('db')));
            $server->setRefreshTokenStorage(new RefreshTokenStorage($app->make('db')));
            $server->setClientStorage(new ClientStorage($app->make('db')));
            $server->setScopeStorage(new ScopeStorage($app->make('db')));
            $server->setAuthCodeStorage(new AuthCodeStorage($app->make('db')));

            $passwordGrant = new PasswordGrant();
            $passwordGrant->setVerifyCredentialsCallback(function ($user, $pass) {
                /*
                 * @TODO: need implement real validate user information at here
                 */
                return true;
            });
            $server->addGrantType($passwordGrant);

            $refreshTokenGrant = new RefreshTokenGrant;
            $server->addGrantType($refreshTokenGrant);

            $server->setRequest($app['request']);

            return $server;
        });
    }

    /**
     * Register the Resource Server
     *
     * @return void
     */
    private function resource()
    {
        $this->app->singleton('League\OAuth2\Server\ResourceServer', function ($app) {
            $server = new ResourceServer(
                new SessionStorage($app->make('db')),
                new AccessTokenStorage($app->make('db')),
                new ClientStorage($app->make('db')),
                new ScopeStorage($app->make('db'))
            );

            $server->setRequest($app['request']);

            return $server;
        });
    }
}
