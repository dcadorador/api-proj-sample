<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Api\V1\Models\Slider;
use App\Api\V1\Models\Slide;

use App\Api\V1\Transformers\SlideTransformer;


class SlideController extends ApiController
{
    public function index(Request $request, $id)
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

        return $this->response->paginator($slider->slides()->where('status','active')->paginate($this->perPage), new SlideTransformer, ['key' => 'slide']);
    }

    public function store(Request $request, $id)
    {
        $slide = new Slide();
        $slide->slider_id = $id;
        $slide->label = $request->input('label');
        $slide->title = $this->getLocalizedInput($request, 'title');
        if($request->has('link-label')) {
            $slide->link_label = $this->getLocalizedInput($request, 'link-label');
        }
        $slide->link = $request->input('link');
        $slide->description = $this->getLocalizedInput($request, 'description');

        if ($request->hasFile('image')) {
            $slide->image_uri = $this->saveUploadedFile($request, 'image');
        }
        else {
            $slide->image_uri = $request->input('image-uri');
        }

        $slide->starts_at = $request->input('starts-at');
        $slide->expires_at = $request->input('expires-at');
        $slide->expires_at = $request->input('display-order');
        $slide->status = 'active';
        $slide->save();

        return $this->response->item($slide, new SlideTransformer, ['key' => 'slide']);
    }

    public function show(Request $request, $slider, $slide){

        $slide = Slide::where('id',$slide)
            ->where('status','active')
            ->first();

        if ($slide === null) {
            return response()->json(['error' => [
                $this->responseArray(1049,404)
            ]], 404);
        }

        return $this->response->item($slide, new SlideTransformer, ['key' => 'slide']);
    }

    public function edit(Request $request, $slider, $slide)
    {
        $slide = Slide::where('id',$slide)
            ->where('status','active')
            ->first();

        if ($slide === null) {
            return response()->json(['error' => [
                $this->responseArray(1049,404)
            ]], 404);
        }

        if ($request->has('title')) {
            $slide->label = $request->input('label');
        }

        if ($request->has('title')) {
            $slide->title = $this->getLocalizedInput($request, 'title');
        }
        if ($request->has('description')) {
            $slide->description = $this->getLocalizedInput($request, 'description');
        }

        if ($request->hasFile('image')) {
            $slide->image_uri = $this->saveUploadedFile($request, 'image');
        }
        else {
            $slide->image_uri = $request->input('image-uri');
        }

        if ($request->has('starts-at')) {
            $slide->starts_at = $request->input('starts-at');
        }
        if ($request->has('expires-at')) {
            $slide->expires_at = $request->input('expires-at');
        }
        if ($request->has('display-order')) {
            $slide->expires_at = $request->input('display-order');
        }
        if ($request->has('status')) {
            $slide->expires_at = $request->input('status');
        }
        if ($request->has('link')) {
            $slide->link = $request->input('link');
        }
        if ($request->has('link-label')) {
            $slide->link_label = $this->getLocalizedInput($request, 'link-label');
        }
        $slide->update();

        return $this->response->item($slide, new SlideTransformer, ['key' => 'slide']);
    }

    public function delete(Request $request, $slider, $slide){

        $slide = Slide::where('id',$slide)
            ->where('status','active')
            ->first();

        if ($slide === null) {
            return response()->json(['error' => [
                $this->responseArray(1049,404)
            ]], 404);
        }

        $slide->status = 'inactive';
        $slide->update();

        return $this->response->item($slide, new SlideTransformer, ['key' => 'slide']);
    }
}