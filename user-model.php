<?php
namespace App\Models;

class User extends BaseModel {
    protected $table = 'users';
    protected $fillable = [
        'email',
        'password_hash',
        'user_type',
        'status',
        'phone',
        'email_verified',
        'phone_verified'
    ];

    public function getProfile() {
        if ($this->user_type === 'trader') {
            return (new TraderProfile())->where('user_id', $this->id)->first();
        } else {
            return (new CustomerProfile())->where('user_id', $this->id)->first();
        }
    }

    public function isVerified() {
        return $this->email_verified && $this->phone_verified;
    }

    public function jobs() {
        return (new Job())->where('customer_id', $this->id)->get();
    }

    public function reviews() {
        if ($this->user_type === 'trader') {
            return (new Review())->where('trader_id', $this->id)->get();
        }
        return (new Review())->where('customer_id', $this->id)->get();
    }

    public function verifyEmail($token) {
        // Email verification logic
        if ($this->email_verification_token === $token) {
            $this->email_verified = true;
            $this->email_verification_token = null;
            return $this->save();
        }
        return false;
    }

    protected static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->password_hash);
    }
}
