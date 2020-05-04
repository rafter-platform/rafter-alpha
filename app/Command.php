<?php

namespace App;

use App\Jobs\DispatchCommand;
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

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Dispatch the command asynchronously.
     *
     * @return void
     */
    public function dispatch()
    {
        DispatchCommand::dispatch($this);
    }

    /**
     * Run the command on a worker.
     *
     * @return string
     */
    public function runCommandOnWorker()
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

    /**
     * Re-run a given command, returning a new instance of a model with
     * the command copied into it.
     *
     * @return Command
     */
    public function reRun(): Command
    {
        $command = static::create([
            'command' => $this->command,
            'user_id' => $this->user_id,
            'environment_id' => $this->environment_id,
        ]);

        $command->dispatch();

        return $command;
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
        return $this->updated_at->shortAbsoluteDiffForHumans($this->created_at);
    }

    /**
     * The URL to this command.
     *
     * @return string
     */
    public function url(): string
    {
        return route('projects.environments.commands.show', [
            $this->environment->project,
            $this->environment,
            $this
        ]);
    }

    public function prefix(): string
    {
        return $this->environment->project->commandPrefix();
    }
}
