<?php

namespace App\Http\Controllers;

use App\Polls;
use App\Polls_choices;
use App\Polls_answers;
use App\Registration_links;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getPoll($poll_id)
    {
        return response()->json(
            [
                'poll' => Polls::findOrFail($poll_id)->first(),
                'choices' => Polls_choices::byPollId($poll_id)->get()
            ]);
    }

    public function getPollAnswers($poll_id)
    {
        return response()->json(Polls_answers::byPollId($poll_id)->get());
    }

    public function getRegistrationLinks()
    {
        return response()->json(Registration_links::orderBy("timestamp", "desc")->get());
    }

    public function getBistrojItems()
    {
        $days = array(
            "1" => '<th class="amatic-700 whoa text-danger">M',
            "2" => '<th class="amatic-700 whoa text-danger">Tis',
            "3" => '<th class="amatic-700 whoa text-danger">Ons',
            "4" => '<th class="amatic-700 whoa text-danger">Tors',
            "5" => '<th class="amatic-700 whoa text-danger">Fre');

        $mealStart = array(
            "1" => '<td width="33%">',
            "2" => '<td width="33%">',
            "3" => '<td width="33%">',
            "4" => '<td width="33%">',
            "5" => '<td width="33%">');

        $daysEndings = array(
            "1" => "</th>",
            "2" => "</th>",
            "3" => "</th>",
            "4" => "</th>",
            "5" => "</th>");

        $website_url = "http://www.hors.se/veckans-meny/?rest=183";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $website_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $html = curl_exec($curl);
        curl_close($curl);

        $start_point = strpos($html, '<table id="mattabellen" class="table non-responsive-menu-table">');
        $end_point = strpos($html, '</table>', $start_point);
        $length = $end_point - $start_point;
        $html = substr($html, $start_point, $length);

        $items = new \stdClass();

        foreach ($days as $key => $value) {
            $start = strpos($html, $value);
            $end = strpos($html, '</tr>', $start);
            $length = $end - $start;
            $var = substr($html, $start, $length);
            $var = strstr($var, $daysEndings[$key]);
            $var = trim(substr($var, strlen($daysEndings[$key])));

            $meals = array();
            $abort = false;
            while (!$abort) {
                $start_point = strpos($var, '<td width="33%">');
                $end_point = strpos($var, '</td>', $start_point);

                if ($start_point === false || $end_point === false) {
                    $abort = true;
                    break;
                }

                $length = $end_point - $start_point;

                $add = substr($var, $start_point, $length + 5);
                $add = substr($add, strlen($mealStart[$key]));
                $add = substr($add, 0, -5);

                if (strpos($add, '<br />') !== false) {
                    $splitmeals = explode("<br />", $add);

                    foreach ($splitmeals as $split) {
                        array_push($meals, trim($split));
                    }
                } else {
                    array_push($meals, $add);
                }

                $var = trim(substr($var, $length + 5));
            }

            $items->$key = $meals;
        }

        return response()->json($items);
    }

    public function getVillaItems()
    {
        $days = array(
            "1" => '<th class="amatic-700 whoa text-danger">M',
            "2" => '<th class="amatic-700 whoa text-danger">Tis',
            "3" => '<th class="amatic-700 whoa text-danger">Ons',
            "4" => '<th class="amatic-700 whoa text-danger">Tors',
            "5" => '<th class="amatic-700 whoa text-danger">Fre');

        $mealStart = array(
            "1" => '<td width="33%">',
            "2" => '<td width="33%">',
            "3" => '<td width="33%">',
            "4" => '<td width="33%">',
            "5" => '<td width="33%">');

        $daysEndings = array(
            "1" => "</th>",
            "2" => "</th>",
            "3" => "</th>",
            "4" => "</th>",
            "5" => "</th>");

        $website_url = "http://www.hors.se/veckans-meny/?rest=2881";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $website_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $html = curl_exec($curl);
        curl_close($curl);

        $start_point = strpos($html, '<table id="mattabellen" class="table non-responsive-menu-table">');
        $end_point = strpos($html, '</table>', $start_point);
        $length = $end_point - $start_point;
        $html = substr($html, $start_point, $length);

        $items = new \stdClass();

        foreach ($days as $key => $value) {
            $start = strpos($html, $value);
            $end = strpos($html, '</tr>', $start);
            $length = $end - $start;
            $var = substr($html, $start, $length);
            $var = strstr($var, $daysEndings[$key]);
            $var = trim(substr($var, strlen($daysEndings[$key])));

            $meals = array();
            $abort = false;
            while (!$abort) {
                $start_point = strpos($var, '<td width="33%">');
                $end_point = strpos($var, '</td>', $start_point);

                if ($start_point === false || $end_point === false) {
                    $abort = true;
                    break;
                }

                $length = $end_point - $start_point;

                $add = substr($var, $start_point, $length + 5);
                $add = substr($add, strlen($mealStart[$key]));
                $add = substr($add, 0, -5);

                if (strpos($add, '<br />') !== false) {
                    $splitmeals = explode("<br />", $add);

                    foreach ($splitmeals as $split) {
                        array_push($meals, trim($split));
                    }
                } else {
                    array_push($meals, $add);
                }

                $var = trim(substr($var, $length + 5));
            }

            $items->$key = $meals;
        }

        return response()->json($items);
    }

    public function getUserCanVoteToday()
    {
        $user_id = $this->request->auth["id"];

        $votes = Polls_answers::byUserId($user_id)->get();

        return response()->json(['canVote' => count($votes) === 0]);
    }

    public function getVotingIsAllowed()
    {
        $cutoff = Carbon::createFromTimestamp(strtotime('today midnight + 11 hours 30 minutes'))->toDateTimeString();
        $now = Carbon::now();

        return response()->json(['votingAllowed' => $cutoff >= $now]);
    }
}
