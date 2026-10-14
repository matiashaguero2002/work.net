<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;

class CompanyController extends Controller
{
    public function profile()
    {
        $company = [
            'name' => 'Tech Solutions S.A.',
            'initials' => 'TS',
            'ruc' => '80012345-6',
            'description' => 'Tech Solutions S.A. es una empresa paraguaya dedicada al desarrollo de software a medida, consultoría tecnológica e integración de sistemas. Con más de 10 años de experiencia, hemos trabajado con clientes de diversos rubros, desde startups hasta grandes corporaciones. Nuestro equipo está conformado por profesionales apasionados por la tecnología y comprometidos con la excelencia.',
            'industry' => 'Desarrollo de software',
            'size' => '11-50 empleados',
            'founded' => '2014',
            'website' => 'www.techsolutions.com.py',
            'email' => 'contacto@techsolutions.com',
            'phone' => '+595 71 200 300',
            'whatsapp' => '+595 985 123 456',
            'address' => 'Av. Irrazábal 1234, Encarnación, Paraguay',
            'coords' => [-27.3306, -55.8667],
            'verified' => true,
            'completion' => 75,
        ];

        $stats = [
            'active_offers' => 4,
            'applications' => 23,
            'followers' => 128,
            'rating' => 4.8,
        ];

        $socials = [
            ['name' => 'linkedin.com/company/techsolutions', 'icon' => 'bi-linkedin'],
            ['name' => 'facebook.com/techsolutionspy', 'icon' => 'bi-facebook'],
            ['name' => '@techsolutionspy', 'icon' => 'bi-instagram'],
            ['name' => 'www.techsolutions.com.py', 'icon' => 'bi-globe'],
        ];

        return view('employer.company.profile', compact('company', 'stats', 'socials'));
    }
}
