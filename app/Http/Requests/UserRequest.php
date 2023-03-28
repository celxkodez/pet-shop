<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends BaseFormRequest
{
    protected array $routeRequest = [
        'api/v1/user/login|post' => 'loginMethodRule',
        'api/v1/user/create|post' => 'registerMethodRule',
        'api/v1/user/edit|put' => 'editMethodRule',
        'api/v1/user/forgot-password|post' => 'forgotPasswordMethodRule',
        'api/v1/user/reset-password-token|post' => 'resetPasswordTokenMethodRule',
        'api/v1/user/orders|get' => 'ordersMethodRule',
        'api/v1/user/orders|head' => 'ordersMethodRule',
    ];
    public function registerMethodRule(): void
    {
        $this->rules = [
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', 'min:4'],
            'avatar' => ['string'],
            'address' => ['required', 'string'],
            'phone_number' => ['required', 'string'],
            'is_marketing' => ['string'],
        ];
    }

    public function editMethodRule(): void
    {
        $this->registerMethodRule();

        $this->rules['email'] = ['required', 'email'];
    }

    public function loginMethodRule(): void
    {
        $this->rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function forgotPasswordMethodRule(): void
    {
        $this->rules = [
            'email' => ['required', 'email'],
        ];
    }

    public function resetPasswordTokenMethodRule(): void
    {
        $this->rules = [
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', 'min:4'],
        ];
    }

    public function ordersMethodRule(): void
    {
        $this->rules = [
            'page' => ['integer',],
            'limit' => ['integer',],
            'sortBy' => ['string',],
            'desc' => ['string'],
        ];
    }
}
