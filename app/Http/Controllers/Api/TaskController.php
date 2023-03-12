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

class TaskController extends Controller
{
    use ApiHelpers;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {
        if($this->isUser($request->user())) {
            $task = Task::where('created_by', '=', $request->user()->id)->get();

            return $this->onSuccess($task, 'Task Retrieved');
        } elseif($this->isAdmin($request->user())) {
            $task = Task::where('done', '=', 1)->get();

            return $this->onSuccess($task, 'Task Retrieved');
        }

        return $this->onError(401, 'Unauthorized Access');
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
            $validator = Validator::make($request->all(), $this->taskValidationRules());

            if($validator->passes()) {
                $task = new Task();

                DB::transaction(function() use ($request, $task) {
                    $task->title = $request->input('title');
                    $task->description = $request->input('description');
                    $task->start_date = $request->input('start_date');
                    $task->end_date = $request->input('end_date');
                    $task->created_by = $request->user()->id;
                    $task->updated_by = $request->user()->id;
                    $task->save();
                });

                return $this->onSuccess($task, 'Task Created', 201);
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
            $task = Task::where('id', '=', $id)->first();

            if(!empty($task)) {
                return $this->onSuccess($task, 'Task Retrieved');
            }

            return $this->onError(404, 'Task Not Found');
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
            $validator = Validator::make($request->all(), $this->taskValidationRules());

            if($validator->passes()) {
                $task = Task::find($id);

                if(!empty($task)) {
                    DB::transaction(function() use ($request, $task) {
                        $task->title = $request->input('title');
                        $task->description = $request->input('description');
                        $task->start_date = $request->input('start_date');
                        $task->end_date = $request->input('end_date');
                        $task->updated_by = $request->user()->id;
                        $task->save();
                    });
    
                    return $this->onSuccess($task, 'Task Updated');
                }

                return $this->onError(404, 'Task Not Found');
            }

            return $this->onError(400, $validator->errors());
        }

        return $this->onError(401, 'Unauthorized Access');
    }

    public function updateDone(Request $request): JsonResponse
    {
        if($this->isUser($request->user()) OR $this->isAdmin($request->user())) {
            $id = $request->input('id');
            $task = Task::find($id);

            if(!empty($task)) {
                if($this->isUser($request->user())) {
                    $task->done = 1;
                    $task->done_date = date('Y-m-d H:i:s');
                } elseif($this->isAdmin($request->user())) {
                    $task->approved = 1;
                    $task->approved_date = date('Y-m-d H:i:s');
                }
                
                $task->updated_by = $request->user()->id;
                $task->save();

                return $this->onSuccess($task, 'Task Updated');
            }

            return $this->onError(404, 'Task Not Found');
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
            $task = Task::find($id);

            if(empty($task)) {
                return $this->onError(404, 'Task Not Found');
            }

            if($task->created_by == $request->user()->id) {
                $item = Item::where('task_id', '=', $id)->get();

                if(!empty($item)) {
                    Item::where('task_id', '=', $id)->delete();
                }

                $task->delete();

                return $this->onSuccess($task, 'Task Deleted');
            }

            return $this->onError(401, 'Unauthorized Access');
        }

        return $this->onError(401, 'Unauthorized Access');
    }
}
