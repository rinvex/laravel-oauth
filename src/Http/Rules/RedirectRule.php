<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Http\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\Factory;

class RedirectRule implements Rule
{
    /**
     * The validator instance.
     *
     * @var \Illuminate\Contracts\Validation\Factory
     */
    protected $validator;

    /**
     * Create a new rule instance.
     *
     * @param  \Illuminate\Contracts\Validation\Factory  $validator
     * @return void
     */
    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function passes($attribute, $value)
    {
        foreach (explode(',', $value) as $redirect) {
            $validator = $this->validator->make(['redirect' => $redirect], ['redirect' => 'url']);

            if ($validator->fails()) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function message()
    {
        return 'One or more redirects have an invalid url format.';
    }
}
