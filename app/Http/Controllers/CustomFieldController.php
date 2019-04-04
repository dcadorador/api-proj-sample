<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Api\V1\Models\CustomField;
use App\Api\V1\Models\CustomFieldData;

use App\Api\V1\Transformers\CustomFieldTransformer;


class CustomFieldController extends ApiController
{

    public function show(Request $request, $customField)
    {
        app('cache')->store('redis')->flush();
        app('cache')->flush();
        $customField = CustomField::find($customField);

        if ($customField === null) {
            return response()->json(['error' => [
                $this->responseArray(1023,404)
            ]], 404);
        }

        return $this->response->item($customField, new CustomFieldTransformer, ['key' => 'custom-field']);
    }

    public function index(Request $request)
    {
        app('cache')->store('redis')->flush();
        app('cache')->flush();
        $concept = $this->getConcept($request);

        return $this->response->paginator(CustomField::where('concept_id', $concept->id)->paginate($this->perPage), new CustomFieldTransformer, ['key' => 'custom-field']);
    }

    public function store(Request $request)
    {
        app('cache')->store('redis')->flush();
        app('cache')->flush();
        $concept = $this->getConcept($request,true);

        $customField = new CustomField();
        $customField->concept_id = $concept->id;
        $customField->label = $request->input('label');
        $customField->type = $request->input('type');

        $customField->save();

        return $this->response->item($customField, new CustomFieldTransformer, ['key' => 'custom-field'])->setStatusCode(201);
    }

    public function edit(Request $request, $customField)
    {
        app('cache')->store('redis')->flush();
        app('cache')->flush();
        $this->getConcept($request,true);
        $customField = CustomField::find($customField);

        if ($customField === null) {
            return response()->json(['error' => [
                $this->responseArray(1023,404)
            ]], 404);
        }

        $customField->label = $request->input('label');
        $customField->type = $request->input('type');

        $customField->save();

        return $this->response->item($customField, new CustomFieldTransformer, ['key' => 'custom-field']);
    }

    public function postData(Request $request, $customField)
    {
        app('cache')->store('redis')->flush();
        app('cache')->flush();
        $cf = CustomField::find($customField);

        if ($cf === null) {
            return response()->json(['error' => [
                $this->responseArray(1023,404)
            ]], 404);
        }

        $entity = $request->input('entity');
        $entityId = $request->input('entity-id');
        $value = $request->input('value');

        $model = 'App\Api\V1\Models\\'.studly_case($entity);

        $customFieldData = CustomFieldData::where('custom_field_id', $customField)
                                            ->where('custom_fieldable_id', $entityId)
                                            ->where('custom_fieldable_type', $model)->first();
        if ($customFieldData) {
            $customFieldData->data = $value;
            $customFieldData->update();
        }
        else {
            $customFieldData = new CustomFieldData();
            $customFieldData->custom_field_id = $customField;
            $customFieldData->custom_fieldable_id = $entityId;
            $customFieldData->custom_fieldable_type = $model;
            $customFieldData->data = $value;
            $customFieldData->save();
        }

        return $this->response->item($cf, new CustomFieldTransformer, ['key' => 'custom-field']);
    }

    public function deleteData(Request $request, $customField)
    {
        app('cache')->store('redis')->flush();
        app('cache')->flush();
        $cf = CustomField::find($customField);
        if ($cf === null) {
            return response()->json(['error' => [
                $this->responseArray(1023,404)
            ]], 404);
        }

        $entity = $request->input('entity');
        $entityId = $request->input('entity-id');

        $model = 'App\Api\V1\Models\\'.studly_case($entity);

        $customFieldData = CustomFieldData::where('custom_field_id', $customField)
                                            ->where('custom_fieldable_id', $entityId)
                                            ->where('custom_fieldable_type', $model)->delete();

        return $this->response->item($cf, new CustomFieldTransformer, ['key' => 'custom-field']);
    }

}