<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    /**
     * @var string
     */
    protected $table = 'news';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'url',
        'upvotes',
        'downvotes',
        'verified',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'verified' => 'boolean',
    ];
}
