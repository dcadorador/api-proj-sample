<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Api\V1\Models\Menu;
use App\Api\V1\Models\Category;
use App\Api\V1\Models\Item;
use App\Api\V1\Models\Modifier;
use App\Api\V1\Models\ModifierGroup;

use App\Api\V1\Transformers\MenuTransformer;
use App\Api\V1\Transformers\CategoryTransformer;
use App\Api\V1\Transformers\ItemTransformer;
use App\Api\V1\Transformers\ModifierTransformer;
use App\Api\V1\Transformers\ModifierGroupTransformer;

class MenuController extends ApiController
{

    public function show($menu)
    {
        $menu = Menu::findOrFail($menu);

        return $this->response->item($menu, new MenuTransformer, ['key' => 'menu']);
    }

    public function index(Request $request) 
    {
        $filter = $request->input('filter',[]);
        if(array_key_exists('key', $filter)) {
            $menu = $this->getConcept($request)->menus()
                ->where('key',$filter['key'])
                ->paginate($this->perPage);
        } else {
            $menu = $this->getConcept($request)->menus()->paginate($this->perPage);
        }

        // return collection with paginator
        return $this->response->paginator($menu, new MenuTransformer, ['key' => 'menu']);
    }

    public function store(Request $request)
    {
        $menu = $this->getConcept($request,true)->menus()->create([
                'label' => $request->json('label'),
                'key' => $request->json('key')
        ]);

        return $this->response->created($menu->uri);
    }

    public function getModifierGroups(Request $request, $item = null)
    {
        if ($item !== null) {
            $modifierGroups = $this->getModifierGroupsForItem($item);
        }
        else {
            //TODO change to current concept_id
            $modifierGroups = ModifierGroup::where('concept_id', '1b29fc01-3a15-4b93-9f73-842f81d7c7ec');
        }

        return $this->response->paginator($modifierGroups->paginate($this->perPage), new ModifierGroupTransformer, ['key' => 'modifier-group']);
    }

    public function getModifierGroup(Request $request, $modifier_group)
    {
        $modifierGroup = ModifierGroup::find($modifier_group);

        if ($modifierGroup === null) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        }

        return $this->response->item($modifierGroup, new ModifierGroupTransformer, ['key' => 'modifier-group']);
    }

    public function getModifiers(Request $request, $modifier_group)
    {
        $modifierGroup = ModifierGroup::find($modifier_group);

        if ($modifierGroup === null) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        }

        return $this->response->paginator($modifierGroup->modifiers()->paginate($this->perPage), new ModifierTransformer, ['key' => 'modifier']);
    }

    public function getModifier(Request $request, $modifier)
    {
        $modifier = Modifier::find($modifier);

        if ($modifier === null) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        }

        return $this->response->item($modifier, new ModifierTransformer, ['key' => 'modifier']);
    }

    private function getModifierGroupsForItem($itemId) 
    {
        $item = Item::find($itemId);

        if ($item === null) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        }
        return $item->modifierGroups();
    }


}