<?php

namespace Spatie\MailPreview;

use Illuminate\Filesystem\Filesystem;
use Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\MailPreview\Http\Controllers\ShowMailController;

class MailPreviewServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-mail-preview')
            ->hasConfigFile()
            ->hasViews();
    }

    public function packageBooted()
    {
        $this
            ->registerPreviewMailTransport()
            ->registerRouteMacro();
    }

    protected function registerPreviewMailTransport(): self
    {
        $previewTransport = new PreviewMailTransport(
            app(Filesystem::class),
            config('mail-preview.maximum_lifetime_in_seconds')
        );

        app('mail.manager')->extend('preview', fn () => $previewTransport);

        return $this;
    }

    protected function registerRouteMacro(): self
    {
        Route::macro('mailPreview', function (string $prefix = 'spatie-mail-preview') {
            if (config('mail-preview.enabled')) {
                Route::get($prefix, '\\' . ShowMailController::class)->name('mail.preview');
            }
        });

        return $this;
    }
}
