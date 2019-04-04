<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Api\V1\Models\Slider;

use App\Api\V1\Transformers\SliderTransformer;


class SliderController extends ApiController
{

    public function index(Request $request)
    {
        $sliders = Slider::where('status','active')
            ->where('concept_id',$this->getConcept($request)->id)
            ->paginate($this->perPage);

        return $this->response->paginator($sliders, new SliderTransformer, ['key' => 'slider']);
    }

    public function store(Request $request)
    {
        $sliders = new Slider();
        $sliders->label = $request->json('label');
        $sliders->status = 'active';
        $sliders->concept_id = $this->getConcept($request)->id;
        $sliders->save();

        return $this->response->item($sliders, new SliderTransformer, ['key' => 'slider']);
    }

    public function delete(Request $request, $id)
    {
        $slider = Slider::where('id',$id)
            ->where('status','active')
            ->first();

        if ($slider === null) {
            return response()->json(['error' => [
                $this->responseArray(1046,404)
            ]], 404);
        }

        $slider->status = 'inactive';
        $slider->update();

        return $this->response->item($slider, new SliderTransformer, ['key' => 'slider']);
    }

    public function show(Request $request, $id)
    {
        $slider = Slider::where('id',$id)
            ->where('concept_id',$this->getConcept($request)->id)
            ->where('status','active')
            ->first();

        if ($slider === null) {
            return response()->json(['error' => [
                $this->responseArray(1046,404)
            ]], 404);
        }

        return $this->response->item($slider, new SliderTransformer, ['key' => 'slider']);
    }


}