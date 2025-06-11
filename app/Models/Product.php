<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name','slug','description','price','stock','image'];

    public function cartItem(){
        return $this->hasMany(CartItem::class);
    }


    public function category()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }
}
