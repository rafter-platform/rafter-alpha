<?php

namespace App;

use App\Casts\Options;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasOptions;

    /**
     * Possible Project types
     */
    const TYPES = [
        'laravel' => "Laravel",
        'nodejs' => "Node.js",
        'rails' => "Rails",
    ];

    protected $guarded = [];

    protected $casts = [
        'options' => Options::class,
    ];

    public function team()
    {
        return $this->belongsTo('App\Team');
    }

    public function googleProject()
    {
        return $this->belongsTo('App\GoogleProject');
    }

    public function environments()
    {
        return $this->hasMany('App\Environment');
    }

    public function sourceProvider()
    {
        return $this->belongsTo('App\SourceProvider');
    }

    /**
     * Create the initial environments for the project
     */
    public function createInitialEnvironments($options = [])
    {
        $variables = $options['variables'] ?? '';

        collect(Environment::INITIAL_ENVIRONMENTS)
            ->each(function ($name) use ($variables) {
                tap($this->environments()->create([
                    'name' => $name
                ]), function ($environment) use ($variables) {
                    $environment->provision($variables);
                });
            });
    }

    public function isLaravel()
    {
        return $this->type === 'laravel';
    }

    public function isRails()
    {
        return $this->type === 'rails';
    }

    public function production()
    {
        return $this->environments()
            ->where('name', 'production')
            ->first();
    }

    public function productionUrl()
    {
        return $this->production()->url ?? '';
    }

    public function typeLabel(): string
    {
        return static::TYPES[$this->type];
    }

    /**
     * Get the prefix for running Commands for a given project type.
     *
     * @return string
     */
    public function commandPrefix(): string
    {
        if ($this->type == 'laravel') {
            return 'php artisan';
        }

        return '';
    }

    public function usesCommands(): bool
    {
        return $this->type == 'laravel';
    }
}
