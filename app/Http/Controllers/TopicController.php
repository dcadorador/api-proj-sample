<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Models\Topic;
use App\Api\V1\Transformers\TopicTransformer;
use Illuminate\Http\Request;

class TopicController extends ApiController
{

    /**
     * @return \Dingo\Api\Http\Response
     */
    public function index(Request $request)
    {
        $concept = $this->getConcept($request);

        $filter = $request->input('filter',[]);

        $topics = Topic::where('concept_id', $concept->id);

        if(array_key_exists('type', $filter) && in_array($filter['type'], ['delivery', 'non-delivery'])) {
            $types = array_flip(Topic::TYPE);

            $type = $types[strtoupper($filter['type'])];

            $topics = $topics->where('type', $type);
        }

        $topics = $topics->orderBy('created_at','DESC')
            ->paginate($this->perPage);

        return $this->response->paginator($topics, new TopicTransformer, ['key' => 'topics']);
    }

    /**
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function store(Request $request)
    {
        $concept = $this->getConcept($request);

        $topic = new Topic();
        $topic->concept_id = $concept->id;
        $topic->code = strtolower(str_random(10));
        $topic->name = $this->getLocalizedInput($request, 'name');
        $topic->type = $request->input('type', Topic::TYPE['DELIVERY']);
        $topic->save();

        return $this->response->item($topic, new TopicTransformer, ['key' => 'topic']);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Dingo\Api\Http\Response
     */
    public function update(Request $request, $id)
    {
        $concept = $this->getConcept($request);

        $topic = Topic::find($id);
        $topic->concept_id = $concept->id;
        $topic->name = $this->getLocalizedInput($request, 'name');
        $topic->type = $request->input('type', Topic::TYPE['DELIVERY']);
        $topic->save();

        return $this->response->item($topic, new TopicTransformer, ['key' => 'topic']);
    }

    /**
     * @param $id
     * @return \Dingo\Api\Http\Response
     */
    public function destroy($id)
    {
        $topic = Topic::find($id);

        $topic->delete();

        return $this->response->item($topic, new TopicTransformer, ['key' => 'topic']);
    }
}