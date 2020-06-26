<?php

namespace App;

use App\Casts\Options;
use Error;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\RequestException;

class SourceProvider extends Model
{
    const TYPE_GITHUB = 'github';

    protected $casts = [
        'meta' => Options::class,
    ];

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get the token for this source provider.
     *
     * @return string
     */
    public function token(): string
    {
        return $this->client()->token();
    }

    /**
     * Get a source control provider client for the provider.
     *
     * @return \App\Contracts\SourceProviderClient
     */
    public function client()
    {
        return SourceProviderClientFactory::make($this);
    }

    /**
     * Refresh a GitHub Installation for the current source provider.
     * If the user has removed access to the installation, a ClientException
     * will be thrown, and the source provider will automatically be deleted.
     *
     * @return void
     */
    public function refreshGitHubInstallation()
    {
        if ($this->type != static::TYPE_GITHUB) {
            throw new Error('Only GitHub source providers can be refreshed.');
        }

        try {
            $this->client()->refreshInstallation();
        } catch (RequestException $e) {
            if ($e->getCode() === 404) {
                $this->remove();
            }

            logger()->error($e->getMessage());
        }
    }

    /**
     * Remove the source provider and alert the user.
     *
     * @return void
     */
    public function remove()
    {
        // TODO: Emit an notification to the user that a source has been removed,
        // and any connected projects will stop being deployed.
        $this->delete();
    }

    public function isGitHub()
    {
        return $this->type == static::TYPE_GITHUB;
    }

    public function createDeployment(Deployment $deployment, $payload = [])
    {
        $pendingDeployment = PendingSourceProviderDeployment::make()
            ->forHash($deployment->commit_hash)
            ->forEnvironment($deployment->environment)
            ->byUserId($deployment->initiator_id)
            ->withPayload($payload);

        $response = $this->client()->createDeployment($pendingDeployment);

        $deploymentId = $response['id'];
        $deployment->meta['github_deployment_id'] = $deploymentId;
        $deployment->save();

        $this->updateDeploymentStatus($deployment, 'in_progress');
    }

    public function updateDeploymentStatus(Deployment $deployment, string $state)
    {
        $this->client()->updateDeploymentStatus($deployment, $state);
    }
}
