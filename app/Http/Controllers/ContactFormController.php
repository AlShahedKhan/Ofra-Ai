<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Models\ContactForm;
use App\Traits\HandlesApiResponse;

class ContactFormController extends Controller
{
    use HandlesApiResponse;

    public function submit(ContactFormRequest $request)
    {
        return $this->safeCall(function () use ($request) {
            $data = ContactForm::create($request->all());

            if (!$data || !$data->exists) {
                return $this->errorResponse(
                    'Failed to submit your query',
                    500
                );
            }

            return $this->successResponse(
                'Your query has been submitted successfully!',
                ['data' => $data]
            );
        });
    }
}
