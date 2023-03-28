<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends UserRequest
{
    protected array $routeRequest = [
        'api/v1/admin/login|post' => 'loginMethodRule',
        'api/v1/admin/create|post' => 'createUserMethodRule',
        'api/v1/admin/user-listing|get' => 'userListingMethodRule',
        'api/v1/admin/user-edit/{uuid}|put' => 'editUserMethodRule',
    ];

    /**
     * @OA\Schema(
     *  schema="CreateUserProperties",
     *  @OA\Xml(name="CreateUserProperties"),
     *  @OA\Property(property="first_name", type="string", format="first_name", example="first_name"),
     *  @OA\Property(property="last_name", type="string", format="last_name", example="last_name"),
     *  @OA\Property(property="email", type="string", format="email", example="user@email.com"),
     *  @OA\Property(property="password", type="string", format="password", example="password"),
     *  @OA\Property(property="avatar", type="string", format="avatar", example="avatar"),
     *  @OA\Property(property="address", type="string", format="address", example="address"),
     *  @OA\Property(property="phone_number", type="string", format="phone_number", example="phone_number"),
     *  @OA\Property(property="is_marketing", type="string", format="is_marketing", example="is_marketing"),
     * )
     */
    public function createUserMethodRule(): void
    {
        $this->registerMethodRule();

        $this->rules['avatar'] = ['required', 'string'];
    }

    /**
    * @OA\Schema(
    *  schema="AdminListingProperties",
    *  @OA\Xml(name="AdminListingProperties"),
    *  @OA\Property(property="page", type="integer", format="page", example="15"),
    *  @OA\Property(property="limit", type="integer", format="limit", example="15"),
    *  @OA\Property(property="sortBy", type="string", format="sortBy", example="created_at"),
    *  @OA\Property(property="desc", type="boolean", format="desc", example="true"),
    *  @OA\Property(property="email", type="string", format="email", example="example@email.com"),
    *  @OA\Property(property="address", type="string", format="address", example="address"),
    *  @OA\Property(property="phone", type="string", format="phone", example="+190545455"),
    *  @OA\Property(property="created_at", type="string", format="created_at", example="13-3-1994"),
    *  @OA\Property(property="marketing", type="string", format="marketing", example="1"),
    * )
    */
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

    public function editUserMethodRule(): void
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

    public function getPath(): string
    {
        dd($this->getPath());
    }
}
