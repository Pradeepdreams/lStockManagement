<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\CountryRequest;
use App\Services\Masters\CountryService;

class CountryController extends Controller
{

    protected $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    public function index()
    {

        return $this->countryService->index();
    }

    public function store(CountryRequest $request)
    {
        return $this->countryService->store($request->validated());
    }

    public function show($id)
    {
        return $this->countryService->show($id);
    }

    public function update(CountryRequest $request, $id)
    {
        return $this->countryService->update($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->countryService->destroy($id);
    }


    public function list(){
        return $this->countryService->list();
    }
}
