<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'region',
        'google_project_id',
    ];

    public function team()
    {
        return $this->belongsTo('App\Team');
    }

    public function googleProject()
    {
        return $this->belongsTo('App\GoogleProject');
    }

    /**
     * Create an initial deployment on Cloud Run.
     *
     * TODO: Extract this out and make it the responsibilty of each environment.
     */
    public function createInitialDeployment()
    {
        // Create an initial build
        // TODO: Use either GitHub event or manual push payload URL
        $build = (new CloudBuild($this))
            ->forManualPush('rafter-demo-project-rafter-uploads', 'rafter-demo.tar.gz');

        $operation = $this->googleProject->client()->createImageForBuild($build);

        // TODO: Delay until operation is complete
        // $this->googleProject->client()->getOperation($operation['name']);

        // dump($image);
    }
}
