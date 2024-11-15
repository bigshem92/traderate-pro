<?php
namespace App\Models;

class Job extends BaseModel {
    protected $table = 'jobs';
    protected $fillable = [
        'customer_id',
        'category_id',
        'title',
        'description',
        'budget_min',
        'budget_max',
        'postcode',
        'address_line1',
        'address_line2',
        'city',
        'county',
        'status',
        'preferred_start_date',
        'preferred_completion_date'
    ];

    public function customer() {
        return (new User())->find($this->customer_id);
    }

    public function category() {
        return (new Category())->find($this->category_id);
    }

    public function quotes() {
        return (new Quote())->where('job_id', $this->id)->get();
    }

    public function acceptedQuote() {
        return (new Quote())->where('job_id', $this->id)
                           ->where('status', 'accepted')
                           ->first();
    }

    public function images() {
        return (new JobImage())->where('job_id', $this->id)->get();
    }

    public function review() {
        return (new Review())->where('job_id', $this->id)->first();
    }

    public function isOpen() {
        return $this->status === 'open';
    }

    public function canQuote() {
        return $this->status === 'open' && !$this->acceptedQuote();
    }

    public function addImage($path, $caption = null) {
        return (new JobImage())->create([
            'job_id' => $this->id,
            'image_url' => $path,
            'caption' => $caption
        ]);
    }

    public function matchingTraders() {
        $query = "SELECT t.* FROM trader_profiles t
                 JOIN trader_categories tc ON t.user_id = tc.trader_id
                 JOIN trader_service_areas tsa ON t.user_id = tsa.trader_id
                 WHERE tc.category_id = ? 
                 AND tsa.postcode_prefix = LEFT(?, 4)
                 AND t.verification_status = 'verified'
                 ORDER BY t.average_rating DESC";
                 
        return $this->query($query, [$this->category_id, $this->postcode]);
    }
}
