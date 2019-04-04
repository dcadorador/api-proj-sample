<?php 

namespace App\Api\V1\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class ApiSubscriber extends ApiModel implements Authenticatable, JWTSubject
{
    use \Illuminate\Auth\Authenticatable;

    protected $table = 'api_subscribers';
    protected $primaryKey = 'id';

    protected $visible = ['id', 'client_id', 'username', 'userable_type', 'userable_id', 'created_at', 'updated_at'];
    protected $fillable = ['id', 'client_id', 'username', 'userable_type', 'userable_id', 'created_at', 'updated_at'];

    public function userable() {
        return $this->morphTo();
    }


    /**
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = app('hash')->make($value);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}