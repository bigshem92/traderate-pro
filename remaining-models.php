<?php
// app/Models/Review.php
namespace App\Models;

class Review extends BaseModel {
    protected $table = 'reviews';
    protected $fillable = [
        'job_id',
        'customer_id',
        'trader_id',
        'rating',
        'reliability_score',
        'quality_score',
        'value_score',
        'review_text',
        'trader_response'
    ];

    public function images() {
        return (new ReviewImage())->where('review_id', $this->id)->get();
    }

    public function job() {
        return (new Job())->find($this->job_id);
    }

    public function trader() {
        return (new TraderProfile())->where('user_id', $this->trader_id)->first();
    }
}

// app/Models/Quote.php
namespace App\Models;

class Quote extends BaseModel {
    protected $table = 'quotes';
    protected $fillable = [
        'job_id',
        'trader_id',
        'amount',
        'description',
        'start_date',
        'completion_date',
        'status'
    ];

    public function job() {
        return (new Job())->find($this->job_id);
    }

    public function trader() {
        return (new TraderProfile())->where('user_id', $this->trader_id)->first();
    }
}

// app/Models/Category.php
namespace App\Models;

class Category extends BaseModel {
    protected $table = 'trade_categories';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'icon'
    ];

    public function subcategories() {
        return (new self())->where('parent_id', $this->id)->get();
    }

    public function parent() {
        return $this->parent_id ? (new self())->find($this->parent_id) : null;
    }
}
