<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\SocialMediaRequest;
use App\Services\Masters\SocialMediaService;
use Illuminate\Http\Request;

class SocialMediaController extends Controller
{
    protected $service;

    public function __construct(SocialMediaService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    public function store(SocialMediaRequest $request)
    {

        return $this->service->store($request->validated());
    }

    public function show($id)
    {
        return $this->service->show($id);
    }

    public function update(SocialMediaRequest $request, $id)
    {
        return $this->service->update($id, $request->validated());
    }

    public function destroy($id)
    {
       return $this->service->destroy($id);

    }

    public function list(){
        return $this->service->list();
    }
}
