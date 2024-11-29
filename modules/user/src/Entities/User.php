<?php

namespace App\User\Entities;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Wallet\Interfaces\Wallet;
use App\Wallet\Traits\HasWallet;

class User extends Authenticatable implements Wallet
{
    use HasApiTokens, HasFactory, Notifiable, HasWallet;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name', 
        'status', 
        'email',
        'phone_number',
        'code',
        'profile_picture',
        'email_verified_at',
        'phone_number_verified_at',
        'code_verified_at',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'password', 'email_verified_at', 'phone_number_verified_at', 'code_verified_at', 'created_at', 'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_number_verified_at' => 'datetime',
        'code_verified_at' => 'datetime',
        'birthdate' => 'datetime',
        'date_of_last_password_change' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        if ($this->first_name || $this->last_name) {
            return trim("{$this->first_name} {$this->last_name}");
        }
        return __('User');
    }

    /**
     * Get all of the transactions for the user.
     */
    public function transactions()
    {
        return $this->hasMany(\App\Payment\Entities\Transaction::class);
    }

    /**
     * Get all of the addresses for the user.
     */
    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'user_id', 'id');
    }

    /**
     * Getthe legal info for the user.
     */
    public function userLegal()
    {
        return $this->hasOne(UserLegal::class);
    }

    /**
     * Get all of the bank accounts for the user.
     */
    public function bankAccounts()
    {
        return $this->hasMany(UserBankAccount::class, 'user_id', 'id');
    }

    /**
     * Get all of the messages.
     */
    public function messages()
    {
        return $this->morphMany(\App\Ticket\Entities\TicketMessage::class, 'modelable');
    }
}
