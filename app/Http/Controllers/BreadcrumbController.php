<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BreadcrumbController extends Controller
{
    public static function generate($segments = [])
    {
        $breadcrumbs = [];
        $url = url('/');
        $breadcrumbs[] = ['label' => 'Inicio', 'url' => $url];

        foreach ($segments as $segment) {
            $url .= '/' . $segment['slug'];
            $breadcrumbs[] = [
                'label' => $segment['label'],
                'url' => $url
            ];
        }

        return $breadcrumbs;
    }
}
