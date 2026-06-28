<?php

use App\Models\User;
use Nicole\Box\Core\Models\Permission;
use Nicole\Box\Core\Models\Role;

return [
  /*
|--------------------------------------------------------------------------
| Catalog Options
|--------------------------------------------------------------------------
| Hybrid EAV engine settings
*/
  'catalog' => [
    'storage_disk' => 'public',
    'image_collection' => 'products',
  ],

  /*
|--------------------------------------------------------------------------
| Model Registry
|--------------------------------------------------------------------------
| List of models used by the Core
*/
  'models' => [
    'role' => Role::class,
    'permission' => Permission::class,
    'staff' => User::class,
  ],

  'industry' => env('VMS_INDUSTRY', 'stone'),

  /*
   |--------------------------------------------------------------------------
   | Setting Entities
   |--------------------------------------------------------------------------
   | List of morph keys for entities that support dynamic setting schemas.
   | Other plugins can merge their entities into this array.
   */
  'setting_entities' => [
    'attribute' => 'Attributes',
    'product' => 'Products',
    'product_type' => 'Product Types',
    'family' => 'Product Families',
    'category' => 'Categories',
    'complex_dictionary' => 'Complex Dictionaries',
    'price_type' => 'Price Types',
    'currency' => 'Currencies',
    'warehouse' => 'Warehouses',
    'unit' => 'Units',
    'product_variant' => 'Product Variants',
    'attribute_option' => 'Dictionary Options',
    'stock' => 'Stock',
    'pipeline' => 'Pipelines',
  ],

  /*
    |--------------------------------------------------------------------------
    | Import Modules
    |--------------------------------------------------------------------------
    | The order of execution is strict (relational dependencies).
    */
  'import_modules' => [
    \Nicole\Box\Core\Importers\SettingsImporter::class,
    \Nicole\Box\Core\Importers\DictionaryImporter::class,
    \Nicole\Box\Core\Importers\AttributeImporter::class,
    \Nicole\Box\Core\Importers\FamilyTypeImporter::class,
    \Nicole\Box\Core\Importers\CategoryImporter::class,
    \Nicole\Box\Core\Importers\ProductImporter::class,
    \Nicole\Box\Core\Importers\ServiceImporter::class,
  ],

  /*
  |--------------------------------------------------------------------------
  | Динамический выбор активного PDF-шаблона сметы [nicole.php]
  |--------------------------------------------------------------------------
  | По умолчанию ядро использует простейший базовый шаблон.
  | Для камня мы переопределим его в .env на 'valerie-stone::pdf.calculator-report'
  */
  'pdf_template' => env('VMS_PDF_TEMPLATE', 'nicole-core::pdf.calculator-report'),

  /*
  |--------------------------------------------------------------------------
  | Реквизиты бренда и компании по умолчанию [nicole.php]
  |--------------------------------------------------------------------------
  */
  'company' => [
    'name' => env('VMS_COMPANY_NAME', 'Vistegra'),
    'website' => env('VMS_COMPANY_WEBSITE', 'vistegra.ru'),
    'phone' => env('VMS_COMPANY_PHONE', '+375 (29) 189-83-22'),
    'email' => env('VMS_COMPANY_EMAIL', 'info@vistegra.by'),
    'address' => env('VMS_COMPANY_ADDRESS', 'Могилёв, Космонавтов 19, оф. 316'),
    'cover_image' => env('VMS_COMPANY_COVER_IMAGE', '/pdf/cover.jpg'),
  ],


];
