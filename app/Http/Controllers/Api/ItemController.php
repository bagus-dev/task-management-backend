<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Library\ApiHelpers;
use App\Models\Item;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    use ApiHelpers;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        if($this->isUser($request->user())) {
            $validator = Validator::make($request->all(), $this->itemValidationRules());

            if($validator->passes()) {
                $task_id = $request->input('task_id');
                $item = new Item();
                $task = Task::find($task_id);

                DB::transaction(function() use ($request, $task_id, $item, $task) {
                    $item->task_id = $task_id;
                    $item->title = $request->input('title');
                    $item->priority = $request->input('priority');
                    $item->save();

                    $task->updated_by = $request->user()->id;
                    $task->save();
                });

                return $this->onSuccess($item, 'Item Created', 201);
            }

            return $this->onError(400, $validator->errors());
        }

        return $this->onError(401, 'Unauthorized Access');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id): JsonResponse
    {
        if($this->isUser($request->user()) OR $this->isAdmin($request->user())) {
            $item = Item::where('task_id', '=', $id)->orderBy('priority', 'ASC')->get();
            $item_not_yet = Item::where('task_id', '=', $id, 'and')->where('done', '=', 0)->get()->count();
            $item_done = Item::where('task_id', '=', $id, 'and')->where('done', '=', 1)->get()->count();

            $array = [
                'item_not_yet' => $item_not_yet,
                'item_done' => $item_done
            ];

            if(!empty($item)) {
                return $this->onSuccess($item, 'Item Retrieved', 200, $array);
            }

            return $this->onError(404, 'Item Not Found');
        }

        return $this->onError(401, 'Unauthorized Access');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): JsonResponse
    {
        if($this->isUser($request->user())) {
            $validator = Validator::make($request->all(), $this->itemValidationRules());

            if($validator->passes()) {
                $item = Item::find($id);
                $task_id = $request->input('task_id');
                $task = Task::find($task_id);

                if(!empty($item)) {
                    DB::transaction(function() use ($request, $task, $item) {
                        $item->title = $request->input('title');
                        $item->priority = $request->input('priority');
                        $item->save();

                        $task->updated_by = $request->user()->id;
                        $task->save();
                    });

                    $item->key = $item->id;
                    $item->number = $request->input('no');
    
                    return $this->onSuccess($item, 'Item Updated');
                }

                return $this->onError(404, 'Item Not Found');
            }

            return $this->onError(400, $validator->errors());
        }

        return $this->onError(401, 'Unauthorized Access');
    }

    public function updateDone(Request $request): JsonResponse
    {
        if($this->isUser($request->user())) {
            $keys = $request->input('keys');
            $explode = explode(",",$keys);
            $task_id = $request->input('task_id');

            if(count($explode) > 0) {
                if($explode[0] !== "") {
                    DB::transaction(function() use ($request, $explode, $task_id) {
                        for($i=0; $i < count($explode); $i++) {
                            $item = Item::find($explode[$i]);
    
                            if(!empty($item)) {
                                $item->done = 1;
                                $item->save();
                            } else {
                                return $this->onError(404, 'Item Not Found');
                            }
                        }
    
                        $task = Task::find($task_id);
    
                        if(!empty($task)) {
                            $task->updated_by = $request->user()->id;
                            $task->save();
                        }
    
                        return $this->onError(404, 'Task Not Found');
                    });

                    return $this->onSuccess([], 'Item Updated');
                }
                    
                return $this->onError(400, 'No Key Selected');
            }

            return $this->onError(400, 'No Key Selected');
        }

        return $this->onError(401, 'Unauthorized Access');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        if($this->isUser($request->user())) {
            $item = Item::find($id);

            if(empty($item)) {
                return $this->onError(404, 'Item Not Found');
            }

            $item->delete();

            return $this->onSuccess($item, 'Task Deleted');
        }

        return $this->onError(401, 'Unauthorized Access');
    }
}
