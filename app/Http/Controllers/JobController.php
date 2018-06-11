<?php

namespace App\Http\Controllers;

use App\Jobs\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends RestApiController
{
    public function __construct()
    {
        //
    }

    public function getJobStatus($id)
    {
        $pendingJob = DB::table('jobs')->find($id);
        $failedJob = DB::table('failed_jobs')
            ->join('jobs', 'jobs.payload', '=', 'jobs.payload')
            ->where('jobs.id', '=', $id)
            ->count();

        $this->setResponseCode(200);
        $this->setResponseStatus('success');

        if($failedJob){
            $this->setResponseMessage('Job failed');
            return $this->sendJsonResponse();
        }

        if(!$pendingJob){
            $this->setResponseMessage('Job processed or Invalid job id');
            return $this->sendJsonResponse();
        }

        if($pendingJob->reserved_at){
            $this->setResponseMessage('Job processing');
            return $this->sendJsonResponse();
        }

        $this->setResponseMessage('Job pending');
        return $this->sendJsonResponse();
    }

    public function getNextJob()
    {
        $this->setResponseCode(200);
        $this->setResponseMessage('No job in queue found');
        $this->setResponseStatus('success');

        $nextJob = DB::table('jobs')->count();

        if(!$nextJob){
            return $this->sendJsonResponse();
        }

        $nextJob = DB::table('jobs')
            ->where('queue', 'high')
            ->orderBy('id', 'desc')
            ->first();

        if($nextJob){
            $this->setResponseMessage('Job ID: ' . $nextJob->id);
            return $this->sendJsonResponse();
        }

        $nextJob = DB::table('jobs')
            ->where('queue', 'medium')
            ->orderBy('id', 'desc')
            ->first();

        if($nextJob){
            $this->setResponseMessage('Job ID: ' . $nextJob->id);
            return $this->sendJsonResponse();
        }

        $nextJob = DB::table('jobs')
            ->where('queue', 'low')
            ->orderBy('id', 'desc')
            ->first();

        if($nextJob){
            $this->setResponseMessage('Job ID: ' . $nextJob->id);
            return $this->sendJsonResponse();
        }

        $nextJob = DB::table('jobs')
            ->where('queue', 'default')
            ->orderBy('id', 'desc')
            ->first();

        if($nextJob){
            $this->setResponseMessage('Job ID: ' . $nextJob->id);
            return $this->sendJsonResponse();
        }

        return $this->sendJsonResponse();
    }

    public function addJobToQueue(Request $request)
    {
        $this->setResponseCode(400);
        $this->setResponseMessage('Invalid data format');
        $this->setResponseStatus('fail');

        if (!$request->isJson()) {
            return $this->sendJsonResponse();
        }

        $data = array_only($request->json()->all(), ['submitter', 'command', 'priority']);

        try {
            $submitter = ucwords($data['submitter']);
            $priority = $data['priority'] ?? "default";
            $jobClass = 'App\\Jobs\\' . $submitter . 'Job';

            /**
             * @var Job $jobHandler
             */
            $jobHandler = new $jobClass($data['command']);
            $jobHandler = $jobHandler->onQueue($priority);
            $dispatch = $this->dispatch($jobHandler);

            $this->setResponseCode(201);
            $this->setResponseMessage($dispatch);
            $this->setResponseStatus('success');

        } catch (\Exception $e){
            $this->setResponseMessage('Error processing request');
        }

        return $this->sendJsonResponse();
    }
}
