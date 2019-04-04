<?php

namespace App\Http\Middleware;

use Closure;

use App\Api\V1\Models\Concept;

class ConceptMiddleware extends BaseMiddleware
{
    /**
     * Retrieve concept from header
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $concept_id = $request->header('Solo-Concept');
        //$concept_id = '4d29fc01-3a15-4b93-9f73-842f81d7c7ec';
        if (!$concept_id) {
            abort(400, 'Concept ID is missing');
        }
        $concept = Concept::find($concept_id);
        if (!$concept) {
            abort(400, 'Invalid Concept ID');
        }
        $request->attributes->add(['concept' => $concept]);
        return $next($request);
    }
}
