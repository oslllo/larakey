<?php

namespace Ghustavh97\Guardian;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Ghustavh97\Guardian\Contracts\Role as RoleContract;
use Ghustavh97\Guardian\Exceptions\StrictModeRestriction;
use Ghustavh97\Guardian\Contracts\Permission as PermissionContract;

use Ghustavh97\Guardian\Models\PermissionPivot;

class GuardianServiceProvider extends ServiceProvider
{
    public function boot(GuardianRegistrar $permissionLoader, Filesystem $filesystem)
    {
        if (isNotLumen()) {
            $this->publishes([
                __DIR__.'/../config/guardian.php' => config_path('guardian.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations/create_guardian_permission_tables.php.stub' => $this->getMigrationFileName($filesystem),
            ], 'migrations');

            $this->registerMacroHelpers();
        }

        $this->commands([
            Commands\CacheReset::class,
            Commands\CreateRole::class,
            Commands\CreatePermission::class,
            Commands\Show::class,
        ]);

        $this->registerModelBindings();

        $permissionLoader->registerPermissions();

        $this->app->singleton(GuardianRegistrar::class, function ($app) use ($permissionLoader) {
            return $permissionLoader;
        });

        $permissionPivot = app(GuardianRegistrar::class)->getPermissionPivotClass();

        $permissionPivot::creating(function ($permission) {
            if (! $permission->to_id || ! $permission->to_type) {
                if (config('guardian.strict.permission.assignment')) {
                    throw StrictModeRestriction::assignment();
                }
                if (! $permission->to_id) {
                    $permission->to_id = null;
                }

                if (! $permission->to_type) {
                    $permission->to_type = '*';
                }
            }
        });
    }

    public function register()
    {
        if (isNotLumen()) {
            $this->mergeConfigFrom(
                __DIR__.'/../config/guardian.php',
                'guardian'
            );
        }

        $this->registerBladeExtensions();
    }

    protected function registerModelBindings()
    {
        $config = $this->app->config['guardian.models'];

        $this->app->bind(PermissionContract::class, $config['permission']);
        $this->app->bind(RoleContract::class, $config['role']);
    }

    protected function registerBladeExtensions()
    {
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $bladeCompiler->directive('role', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('elserole', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php elseif(auth({$guard})->check() && auth({$guard})->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasrole', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endhasrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasanyrole', function ($arguments) {
                list($roles, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasAnyRole({$roles})): ?>";
            });
            $bladeCompiler->directive('endhasanyrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasallroles', function ($arguments) {
                list($roles, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasAllRoles({$roles})): ?>";
            });
            $bladeCompiler->directive('endhasallroles', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('unlessrole', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php if(!auth({$guard})->check() || ! auth({$guard})->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endunlessrole', function () {
                return '<?php endif; ?>';
            });
        });
    }

    protected function registerMacroHelpers()
    {
        Route::macro('role', function ($roles = []) {
            if (! is_array($roles)) {
                $roles = [$roles];
            }

            $roles = implode('|', $roles);

            $this->middleware("role:$roles");

            return $this;
        });

        Route::macro('permission', function ($permissions = []) {
            if (! is_array($permissions)) {
                $permissions = [$permissions];
            }

            $permissions = implode('|', $permissions);

            $this->middleware("permission:$permissions");

            return $this;
        });
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem): string
    {
        $timestamp = date('Y_m_d_His');

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path.'*_create_guardian_permission_tables.php');
            })->push($this->app->databasePath()."/migrations/{$timestamp}_create_guardian_permission_tables.php")
            ->first();
    }
}
