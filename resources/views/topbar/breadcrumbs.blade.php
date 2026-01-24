<div class="flex flex-col items-start gap-4 pt-4">
    <h1 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
        Dashboard
    </h1>

    <x-filament::breadcrumbs :breadcrumbs="[
        '/' => 'Home',
        '/dashboard' => 'Dashboard',
        '/dashboard/users' => 'Users',
        '/dashboard/users/create' => 'Create User',
    ]" />
</div>
