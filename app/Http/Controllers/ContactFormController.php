<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Models\ContactForm;
use App\Traits\HandlesApiResponse;

class ContactFormController extends Controller
{
    use HandlesApiResponse;

    public function index()
    {
        return $this->safeCall(function () {
           $data = ContactForm::all();

           return $this->successResponse(
               'Contact form data',
               ['data' => $data]
           );
        });
    }

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

    public function show($id)
    {
        return $this->safeCall(function () use ($id) {
            $data = ContactForm::find($id);

            if (!$data || !$data->exists) {
                return $this->errorResponse(
                    'No data found',
                    404
                );
            }

            return $this->successResponse(
                'Contact form data',
                ['data' => $data]
            );
        });
    }

    public function destroy($id)
    {
        return $this->safeCall(function () use ($id) {
            $data = ContactForm::find($id);

            // Check if the data exists
            if (!$data) {
                return $this->errorResponse(
                    'No data found with the given ID',
                    404
                );
            }

            // Delete the data
            $data->delete();

            return $this->successResponse(
                'Contact form data deleted successfully',
                ['data' => $data]
            );
        });
    }


}
