<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Api\V1\Models\Role;

use App\Api\V1\Transformers\RoleTransformer;


class RoleController extends ApiController
{

    public function index(Request $request) 
    {
        $roles = Role::paginate($this->perPage);

        return $this->response->paginator($roles, new RoleTransformer, ['key' => 'role']);
    }


}