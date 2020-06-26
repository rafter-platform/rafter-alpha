<?php

namespace App\Http\Requests;

use App\Environment;
use Illuminate\Foundation\Http\FormRequest;

class GitHubHookStatusRequest extends FormRequest
{
    public function rules()
    {
        return [
            //
        ];
    }

    public function installationid()
    {
        return $this->installation['id'];
    }

    public function repository()
    {
        return $this->name;
    }

    public function getBranches()
    {
        return collect($this->branches)->map(fn ($branch) => $branch['name']);
    }

    public function hash()
    {
        return $this->sha;
    }

    public function senderEmail()
    {
        return $this->commit['commit']['author']['email'] ?? null;
    }

    public function environments()
    {
        return Environment::query()
            ->whereIn('branch', $this->getBranches())
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
