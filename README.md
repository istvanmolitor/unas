# UNAS modul

Szinkronizálás unas webshoppal

## Előfeltételek

Telepíteni kell a következő modulokat.:
- https://gitlab.com/molitor/product
- https://gitlab.com/molitor/order

## Telepítés

### Provider regisztrálása
config/app.php
```php
'providers' => ServiceProvider::defaultProviders()->merge([
    /*
    * Package Service Providers...
    */
    \Molitor\Unas\Providers\UnasServiceProvider::class,
])->toArray(),
```

### Seeder regisztrálása

database/seeders/DatabaseSeeder.php
```php
use Molitor\Unas\database\seeders\UnasSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UnasSeeder::class,
        ]);
    }
}
```

### Menüpont megjelenítése az admin menüben

Ma a Menü modul telepítve van akkor meg lehet jeleníteni az admin menüben.

```php
<?php
//Menü builderek listája:
return [
    \Molitor\Unas\Services\Menu\UnasMenuBuilder::class
];
```

### Breadcrumb telepítése

A modul breadcrumbs.php fileját regisztrálni kell a configs/breadcrumbs.php fileban.
```php
<?php
'files' => [
    base_path('/vendor/molitor/unas/src/routes/breadcrumbs.php'),
],
```