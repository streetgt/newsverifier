<?php

namespace App\Http\Controllers;

use App\News;
use Yajra\Datatables\Datatables;

class HomeController extends Controller
{
    /**
     * HomeController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    /**
     * @return mixed
     */
    public function news()
    {
        return Datatables::of(News::query())->make(true);
    }

    public function verify($id)
    {
        $news = News::find($id);

        if ($news == null) {
            return redirect()->back();
        }

        $news->verified == false ? $news->verified = true : $news->verified = false;
        $news->save();

        return redirect()->back();
    }
}
