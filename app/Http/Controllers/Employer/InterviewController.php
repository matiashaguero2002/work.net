<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;

class InterviewController extends Controller
{
    public function create($applicantId = null)
    {
        $applicant = [
            'id' => $applicantId ?? 1,
            'name' => 'Juan Pérez',
            'initials' => 'JP',
            'position' => 'Desarrollador Laravel Senior',
            'email' => 'juan.perez@email.com',
            'phone' => '+595 985 123 456',
            'match' => 92,
        ];

        return view('employer.interviews.create', compact('applicant'));
    }
}
