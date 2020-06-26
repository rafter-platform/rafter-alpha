<?php

namespace App\Http\Requests;

use App\Environment;
use Illuminate\Foundation\Http\FormRequest;

class GitHubHookPushRequest extends FormRequest
{
    public function rules()
    {
        return [];
    }

    public function installationid()
    {
        return $this->installation['id'];
    }

    public function branch()
    {
        return str_replace("refs/heads/", "", $this->ref);
    }

    public function repository()
    {
        return $this->repository['full_name'];
    }

    public function hash()
    {
        return $this->head_commit['id'];
    }

    public function senderEmail()
    {
        return $this->pusher['email'] ?? null;
    }

    public function environments()
    {
        return Environment::query()
            ->where('branch', $this->branch())
            ->whereHas('project.sourceProvider', function ($query) {
                $query->where([
                    ['installation_id', $this->installationId()],
                    ['type', 'github'],
                ]);
            })
            ->whereHas('project', function ($query) {
                $query->where('repository', $this->repository());
            })
            ->get();
    }
}
