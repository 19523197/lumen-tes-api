<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Books extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title', 'summary', 'author', 'price', 'cover_url', 'category'
    ];


    public $timestamps = false;

    protected $primaryKey = 'book_id';
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */




    public function paginateCategory($row, $query)
    {
        return Books::where('category', 'like', '%' . $query . '%')->paginate($row);
    }

    public function scopeTitle($query)
    {
        if (request('title')) {
            return $query->where('title', 'like', '%' . request('title') . '%');
        }
        if (request('category')) {
            return $query->where('category', 'like', '%' . request('category') . '%');
        }
        if (request('summary')) {
            return $query->where('summary', 'like', '%' . request('summary') . '%');
        }
    }
}
