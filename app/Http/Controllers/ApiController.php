<?php

namespace App\Http\Controllers;

use App\Polls;
use App\Polls_choices;
use App\Polls_answers;
use App\Registration_links;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
        $cutoff = Carbon::createFromTimestamp(strtotime('today midnight + 23 hours 30 minutes'))->toDateTimeString();
        $now = Carbon::now();

        return response()->json(['votingAllowed' => $cutoff >= $now]);
    }

    public function getChoiceVotedFor()
    {
        $user_id = $this->request->auth["id"];

        return response()->json(Polls_answers::byUserId($user_id)->first());
    }

    public function vote()
    {
        date_default_timezone_set("Europe/Stockholm");
        if ($this->request->has('poll_id') && $this->request->has('choice_id')) {
            $poll_id = $this->request->input('poll_id');
            $choice_id = $this->request->input('choice_id');

            Polls::findOrFail($poll_id);
            Polls_choices::findOrFail($choice_id);

            $user_id = $this->request->auth["id"];

            $votes = Polls_answers::byUserId($user_id)->get();

            if (count($votes) === 0) {
                $answer = new Polls_answers();

                $answer->user_id = (int)$user_id;
                $answer->poll_id = (int)$poll_id;
                $answer->choice_id = (int)$choice_id;
                $answer->ip_address = $this->request->ip();
                $answer->timestamp = date('Y-m-d H:i:s');

                $answer->timestamps = false;

                $answer->save();

                return response()->json([
                    'status' => 201,
                    'message' => 'Vote saved'
                ], 201);
            } else {
                return response()->json([
                    'status' => '401_ALREADY_VOTED',
                    'message' => config()['errors']['401_ALREADY_VOTED']
                ], 401);
            }
        } else {
            throw new BadRequestHttpException;
        }
    }

    public function createLink()
    {
        $link = new Registration_links();

        $link->token = $this->randomToken(32);
        $link->timestamp = date('Y-m-d H:i:s');

        $link->timestamps = false;

        $link->save();

        return response()->json([
            'status' => 201,
            'message' => 'Link created'
        ], 201);
    }

    private function randomToken($length = 32)
    {
        if (!isset($length) || intval($length) <= 8) {
            $length = 32;
        }
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        }
    }

    public function changePassword()
    {
        if ($this->request->has('oldPassword') && $this->request->has('newPassword')) {
            $this->validate($this->request, [
                'oldPassword' => 'bail|required|max:50',
                'newPassword' => 'bail|required|min:10|max:50|regex:/^[a-zA-ZåÅäÄöÖ\_0-9!@#.]+$/'
            ]);

            $oldPw = $this->request->input('oldPassword');
            $newPw = $this->request->input('newPassword');
            $user_id = $this->request->auth["id"];

            $user = User::where('id', $user_id)->first();
            if (!Hash::check($oldPw, $user->password)) {
                return response()->json([
                    'status' => '401_CURRENT_PASSWORD',
                    'message' => config()['errors'][401]
                ], 401);
            }

            $user->password = password_hash($newPw, PASSWORD_BCRYPT);
            $user->timestamps = false;

            $user->save();

            return response()->json([
                'status' => 204,
                'message' => 'Password changed'
            ], 204);
        } else {
            throw new BadRequestHttpException;
        }
    }

    public function validateToken()
    {
        if ($this->request->has('token')) {
            $this->validate($this->request, [
                'token' => 'bail|required|alpha_num|max:128'
            ]);

            $token = $this->request->input('token');

            if ($this->registerTokenIsValid($token)) {
                return response()->json([
                    'status' => 204,
                    'message' => 'Token valid'
                ], 204);
            } else {
                return response()->json([
                    'status' => 403,
                    'message' => config()['errors'][403]
                ], 403);
            }
        } else {
            throw new BadRequestHttpException;
        }
    }

    public function register()
    {
        if ($this->request->has('username') && $this->request->has('password') &&
            $this->request->has('token')) {
            $this->validate($this->request, [
                'username' => 'bail|required|min:2|max:32|regex:/^[a-zA-ZåÅäÄöÖ\_0-9]+$/',
                'password' => 'bail|required|min:10|max:50|regex:/^[a-zA-ZåÅäÄöÖ\_0-9!@#.]+$/',
                'token' => 'bail|required|alpha_num|max:128'
            ]);

            $username = $this->request->input('username');
            $password = $this->request->input('password');
            $token = $this->request->input('token');

            if (!$this->registerTokenIsValid($token)) {
                return response()->json([
                    'status' => '403_TOKEN_INVALID',
                    'message' => config()['errors'][403]
                ], 403);
            }

            if ($this->usernameIsTaken($username)) {
                return response()->json([
                    'status' => '403_USERNAME_TAKEN',
                    'message' => config()['errors'][403]
                ], 403);
            }

            $user = new User();

            $user->username = $username;
            $user->password = password_hash($password, PASSWORD_BCRYPT);

            $user->timestamps = false;

            $user->save();

            $this->consumeRegisterToken($token);

            return response()->json([
                'status' => 201,
                'message' => 'User created'
            ], 201);
        } else {
            throw new BadRequestHttpException;
        }
    }

    private function registerTokenIsValid($token)
    {
        $link = Registration_links::byToken($token)->get();

        if (count($link) === 0) {
            return false;
        } else {
            return true;
        }
    }

    private function usernameIsTaken($username)
    {
        $user = User::byUsername($username)->get();

        if (count($user) === 0) {
            return false;
        } else {
            return true;
        }
    }

    private function consumeRegisterToken($token)
    {
        $link = Registration_links::where('token', '=', $token)->first();
        $link->timestamps = false;

        $link->used = 1;

        $link->save();
    }
}
