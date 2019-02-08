<?php

namespace App\Http\Controllers;

use App\Polls;
use App\Polls_choices;
use App\Polls_answers;
use App\Registration_links;

class ApiController extends Controller
{
    public function getPoll($poll_id)
    {
        return response()->json(Polls::findOrFail($poll_id)->get());
    }

    public function getPollChoices($poll_id)
    {
        return response()->json(Polls_choices::byPollId($poll_id)->get());
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
            "monday" => '<th class="amatic-700 whoa text-danger">M',
            "tuesday" => '<th class="amatic-700 whoa text-danger">Tis',
            "wednesday" => '<th class="amatic-700 whoa text-danger">Ons',
            "thursday" => '<th class="amatic-700 whoa text-danger">Tors',
            "friday" => '<th class="amatic-700 whoa text-danger">Fre');

        $mealStart = array(
            "monday" => '<td width="33%">',
            "tuesday" => '<td width="33%">',
            "wednesday" => '<td width="33%">',
            "thursday" => '<td width="33%">',
            "friday" => '<td width="33%">');

        $daysEndings = array(
            "monday" => "</th>",
            "tuesday" => "</th>",
            "wednesday" => "</th>",
            "thursday" => "</th>",
            "friday" => "</th>");

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
            "monday" => '<th class="amatic-700 whoa text-danger">M',
            "tuesday" => '<th class="amatic-700 whoa text-danger">Tis',
            "wednesday" => '<th class="amatic-700 whoa text-danger">Ons',
            "thursday" => '<th class="amatic-700 whoa text-danger">Tors',
            "friday" => '<th class="amatic-700 whoa text-danger">Fre');

        $mealStart = array(
            "monday" => '<td width="33%">',
            "tuesday" => '<td width="33%">',
            "wednesday" => '<td width="33%">',
            "thursday" => '<td width="33%">',
            "friday" => '<td width="33%">');

        $daysEndings = array(
            "monday" => "</th>",
            "tuesday" => "</th>",
            "wednesday" => "</th>",
            "thursday" => "</th>",
            "friday" => "</th>");

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
}
