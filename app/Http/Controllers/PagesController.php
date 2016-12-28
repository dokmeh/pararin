<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class PagesController extends Controller
{
    public function home()
    {
        $data = $this->home_ajax();
        return view('master', compact('data'));
    }
    public function home_ajax()
    {
        return ['title' => 'Pararin Pendar', 'page' => 'home', 'content' => view('home')->render()];
    }

    public function projects()
    {
        $data = $this->projects_ajax();
        return view('master', compact('data'));
    }
    public function projects_ajax()
    {
        return ['title' => 'Pararin Pendar', 'page' => 'projects', 'content' => view('projects')->render()];
    }

    public function project($url)
    {
        $data = $this->project_ajax($url);
        return view('master', compact('data'));
    }
    public function project_ajax($url)
    {
        return ['title' => 'Pararin Pendar', 'page' => 'project', 'content' => view('project')->render()];
    }

    public function publications()
    {
        $data = $this->publications_ajax();
        return view('master', compact('data'));
    }
    public function publications_ajax()
    {
        return ['title' => 'Pararin Pendar', 'page' => 'publications', 'content' => view('publications')->render()];
    }

    public function awards($url = "")
    {
        $data = $this->awards_ajax($url);
        return view('master', compact('data'));
    }
    public function awards_ajax($url = "")
    {
        return ['title' => 'Pararin Pendar', 'page' => (empty($url) ? 'awards' : 'award'), 'content' => view('awards', compact('url'))->render()];
    }
}
