<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Services\JobFilterService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    protected $filterService;

    public function __construct(JobFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    public function index(Request $request)
    {
        $query = Job::query()->with(['languages', 'locations', 'categories', 'attributeValues.attribute']);

        if ($request->has('filter')) {
            $filters = $this->filterService->parseFilterString($request->filter);
            $query = $this->filterService->apply($query, $filters);
        }

        $jobs = $query->paginate(10);

        return response()->json($jobs);
    }
}
