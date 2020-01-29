<?php

namespace App\Http\Requests;

use App\GoogleProject;
use App\Project;
use App\Rules\ValidRepository;
use App\SourceProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['string', 'required'],
            'google_project_id' => [
                'required',
                Rule::in($this->user()->currentTeam->googleProjects()->pluck('id'))
            ],
            'source_provider_id' => [
                'required',
                Rule::exists('source_providers', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id);
                })
            ],
            'region' => [
                'required',
                Rule::in(collect(GoogleProject::REGIONS)->keys()),
            ],
            'type' => [
                'required',
                Rule::in(collect(Project::TYPES)->keys()),
            ],
            'repository' => [
                'required',
                'string',
                new ValidRepository(SourceProvider::find($this->source_provider_id))
            ],
        ];
    }
}
