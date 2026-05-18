<?php

namespace App\Providers;

use App\Support\StatusDesign;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Blade::directive('statusBadge', function (string $expression): string {
            return "<?php \$statusBadge = \\App\\Support\\StatusDesign::badge($expression); ?>"
                . "<span class=\"badge <?php echo \$statusBadge['class']; ?>\"><?php echo e(\$statusBadge['label']); ?></span>";
        });
    }
}
