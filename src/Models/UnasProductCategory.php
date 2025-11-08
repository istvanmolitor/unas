<?php

declare(strict_types=1);

namespace Molitor\Unas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnasProductCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unas_shop_id',
        'parent_id',
        'name',
        'title',
        'keywords',
        'description',
        'display_page',
        'display_menu',
        'remote_id',
        'changed',
    ];

    public function shop()
    {
        return $this->belongsTo(UnasShop::class, 'unas_shop_id');
    }

    public function parent()
    {
        return $this->belongsTo(UnasProductCategory::class, 'parent_id');
    }

    public function childCategories()
    {
        return $this->hasMany(UnasProductCategory::class, 'parent_id');
    }

    public function shopProducts()
    {
        return $this->belongsToMany(
            UnasProduct::class,
            'unas_product_category_products',
            'unas_product_category_id',
            'unas_product_id'
        );
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }
}
