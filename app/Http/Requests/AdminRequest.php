<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends UserRequest
{
    protected array $routeRequest = [
        'api/v1/admin/login|post' => 'loginMethodRule',
        'api/v1/admin/create|post' => 'createUserMethodRule',
        'api/v1/admin/user-listing|get' => 'userListingMethodRule',
    ];

    public function createUserMethodRule(): void
    {
        $this->registerMethodRule();

        $this->rules['avatar'] = ['required', 'string'];
    }

    public function userListingMethodRule(): void
    {
        $this->rules = [
            'page' => ['integer'],
            'limit' => ['integer'],
            'sortBy' => ['string'],
            'desc' => ['string'],
            'first_name' => ['string'],
            'email' => ['string'],
            'phone' => ['string'],
            'address' => ['string'],
            'created_at' => ['string'],
            'marketing' => ['string'],
        ];
    }
}
