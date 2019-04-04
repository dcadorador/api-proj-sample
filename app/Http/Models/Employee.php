<?php

namespace App\Api\V1\Models;

class Employee extends ApiModel
{

    protected $fillable = [
        'code',
        'first_name',
        'last_name',
        'username',
        'email',
        'mobile',
        'status'
    ];

    public function roles() 
    {
        return $this->belongsToMany($this->ns.'\Role','employee_role','employee_id','role_id');
    }

    public function assignRole($role)
    {
        return $this->roles()->save(
            Role::where('id', $role)->firstOrFail()
        );
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        return (bool) $role->intersect($this->roles)->count();

        //foreach ($role as $r) {
        //    if ($this->hasRole($r->name)) {
        //        return true;
        //    }
        //}
        //return false;
    }

    public function locations()
    {
        return $this->belongsToMany($this->ns.'\Location','employee_location','employee_id','location_id');
    }


    public function user()
    {
        return $this->morphOne($this->ns . '\ApiSubscriber', 'userable');
    }

    public function getUriAttribute() 
    {
        return $this->getBaseUri() . '/employees/' . $this->id;
    }

    public function concepts()
    {
        return $this->belongsToMany($this->ns.'\Concept');
    }

    public function bearing()
    {
        return $this->hasMany($this->ns.'\Bearing','employee_id');
    }

    public function orders()
    {
        return $this->belongsToMany($this->ns.'\Order','employee_order','employee_id','order_id');
    }

}