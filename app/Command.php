<?php

namespace App;

use Exception;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Database\Eloquent\Model;

class Command extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_FINISHED = 'finished';
    const STATUS_FAILED = 'failed';

    protected $guarded = [];

    public function environment()
    {
        return $this->belongsTo('App\Environment');
    }

    public function dispatch()
    {
        # code...
    }

    /**
     * Run the command on a worker.
     *
     * @return string
     */
    public function runCommandOnWorker(): string
    {
        $workerUrl = $this->environment->worker_url . '/_rafter/command/run';
        $jsonKey = $this->environment->project->googleProject->service_account_json;

        /**
         * Google helps us out by creating a middleware to sign the outgoing request to the
         * worker service with an OIDC token based on the audience (which is the $workerUrl).
         */
        $creds = new ServiceAccountCredentials(null, $jsonKey, null, $workerUrl);
        $middleware = new AuthTokenMiddleware($creds);

        $stack = HandlerStack::create();
        $stack->push($middleware);

        $client = new Client([
            'handler' => $stack,
            'auth' => 'google_auth'
        ]);

        try {
            $response = $client->post($workerUrl, [
                'form_params' => [
                    'command' => $this->command,
                ],
            ]);

            $output = $response->getBody()->getContents();

            $this->markFinished($output);

            return $output;
        } catch (Exception $e) {
            $this->markFailed($e->getMessage());
        }
    }

    public function markRunning()
    {
        $this->update(['status' => static::STATUS_RUNNING]);
    }

    public function markFinished(string $output)
    {
        $this->update([
            'status' => static::STATUS_FINISHED,
            'output' => $output,
        ]);
    }

    public function markFailed(string $output)
    {
        $this->update([
            'status' => static::STATUS_FAILED,
            'output' => $output,
        ]);
    }

    public function isRunning()
    {
        return $this->status == static::STATUS_RUNNING;
    }

    public function isFinished()
    {
        return $this->status == static::STATUS_FINISHED;
    }

    public function isFailed()
    {
        return $this->status == static::STATUS_FAILED;
    }

    /**
     * Get the elapsed time for a given command.
     *
     * @return string
     */
    public function elapsedTime(): string
    {
        return $this->updated_at->longAbsoluteDiffForHumans($this->created_at);
    }
}
