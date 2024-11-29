<?php

namespace App\User\Entities;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\User\Traits\{HasRoleAndPermission, AuthenticationLogable};
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoleAndPermission, AuthenticationLogable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name', 
        'username',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'password', 'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
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
        return $this->username;
    }

    /**
     * The organization belongsTo a certain user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get all of the organizations for the user.
     */
    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }

    /**
     * Get all of the logs for the user.
     */
    public function logs()
    {
        return $this->hasMany(\Laravel\Nova\Actions\ActionEvent::class, 'user_id', 'id');
    }

    /**
     * Get all of the transactions for the user.
     */
    public function transactions()
    {
        return $this->hasMany(\App\Payment\Entities\Transaction::class);
    }

    /**
     * Get all of the authentication logs's user.
     */
    public function authentications()
    {
        return $this->morphMany(\Rappasoft\LaravelAuthenticationLog\Models\AuthenticationLog::class, 'authenticatable');
    }

    /**
     * Get all of the messages.
     */
    public function messages()
    {
        return $this->morphMany(\App\Ticket\Entities\TicketMessage::class, 'modelable');
    }
}
