<?php
namespace App\Models;

class TraderProfile extends BaseModel {
    protected $table = 'trader_profiles';
    protected $fillable = [
        'user_id',
        'business_name',
        'description',
        'years_experience',
        'website',
        'insurance_info',
        'verification_status',
        'average_rating',
        'total_reviews'
    ];

    public function user() {
        return (new User())->find($this->user_id);
    }

    public function categories() {
        return $this->belongsToMany('TraderCategory', 'trader_categories', 'trader_id', 'category_id');
    }

    public function serviceAreas() {
        return (new ServiceArea())->where('trader_id', $this->user_id)->get();
    }

    public function qualifications() {
        return (new Qualification())->where('trader_id', $this->user_id)->get();
    }

    public function reviews() {
        return (new Review())->where('trader_id', $this->user_id)
                            ->orderBy('created_at', 'DESC')
                            ->get();
    }

    public function availability() {
        return (new Availability())->where('trader_id', $this->user_id)->get();
    }

    public function updateRating() {
        $reviews = $this->reviews();
        if (count($reviews) > 0) {
            $this->average_rating = array_sum(array_column($reviews, 'rating')) / count($reviews);
            $this->total_reviews = count($reviews);
            $this->save();
        }
    }

    public function isAvailable($date) {
        $availability = $this->availability();
        $dayOfWeek = date('w', strtotime($date));
        
        foreach ($availability as $slot) {
            if ($slot->day_of_week == $dayOfWeek && $slot->is_available) {
                return true;
            }
        }
        return false;
    }

    public function isVerified() {
        return $this->verification_status === 'verified';
    }
}
