<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id','nama', 'nrp', 'foto', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    
    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    private function getUserRole()
    {
        return $this->role()->getResults();
    }

    private function checkRole($role)
    {
        return (strtolower($role) == strtolower($this->have_role->role)) ? true : false ;
    }

    public function hasRole($roles)
    {
        $this->have_role = $this->getUserRole();

        if (is_array($roles)) {
            foreach ($roles as $need_role) {
                if ($this->checkRole($need_role)) {
                    return true;
                }
            }
        } else {
            return $this->checkRole($roles);
        }
    }

    public function presents()
    {
        return $this->hasMany('App\Present');
    }
}
