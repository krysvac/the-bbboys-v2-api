<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUndefinedFieldInspection */
/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpComposerExtensionStubsInspection */

namespace App\Http\Controllers;

use App\Polls;
use App\Polls_choices;
use App\Polls_answers;
use App\Registration_links;
use App\User;
use App\Weeb_answers;
use App\Weeb_choices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use stdClass;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Goutte\Client;

class ApiController extends Controller
{
    /**
     * The request instance.
     *
     * @var Request
     */
    private $request;

    /**
     * Create a new controller instance.
     *
     * @param Request $request
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
        $website_url = "https://www.hors.se/karlskrona/17/3/bistro-j/";

        $client = new Client();
        $crawler = $client->request('GET', $website_url);
        $items = $crawler->filter("div.menu-item")->each(function ($node) {
            return $node->filter("p")->each(function ($node1) {
                return trim($node1->text());
            });
        });

        $items_final = new stdClass();

        foreach ($items as $key => $value) {
            $key1 = $key + 1;
            $items_final->$key1 = $value;
        }

        return response()->json($items_final);
    }

    public function getVillaItems()
    {
        $website_url = "http://www.villaoscar.webbess.se/";

        $client = new Client();
        $crawler = $client->request('GET', $website_url);
        $items = $crawler->filter("div.menu-item")->each(function ($node) {
            return $node->filter("p")->each(function ($node1) {
                return trim($node1->text());
            });
        });

        $items_final = new stdClass();

        foreach ($items as $key => $value) {
            $key1 = $key + 1;
            $items_final->$key1 = $value;
        }

        return response()->json($items_final);
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

        return null;
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

    public function getCurrentWeebChoices()
    {
        return response()->json(Weeb_choices::getCurrentChoices()->get());
    }

    public function addWeebChoice()
    {
        date_default_timezone_set("Europe/Stockholm");
        if ($this->request->has('value') && $this->request->has('name')) {
            $this->validate($this->request, [
                'value' => 'bail|required|max:50',
                'name' => 'bail|required|max:100'
            ]);

            $value = $this->request->input('value');
            $name = $this->request->input('name');

            $maybeChoices = Weeb_choices::getByValue($value)->get();

            if (count($maybeChoices) > 0) {
                return response()->json([
                    'status' => 204,
                    'message' => 'Choice already added by someone else'
                ], 204);
            } else {
                $user_id = $this->request->auth["id"];

                $choice = new Weeb_choices();

                $choice->value = $value;
                $choice->name = $name;
                $choice->user_id = (int)$user_id;
                $choice->timestamp = date('Y-m-d H:i:s');
                $choice->timestamps = false;

                $choice->save();

                return response()->json([
                    'status' => 201,
                    'message' => 'Choice added'
                ], 201);

            }
        } else {
            throw new BadRequestHttpException;
        }
    }

    public function getWeebVotingIsAllowed()
    {
        $cutoff = Carbon::createFromTimestamp(strtotime('friday this week + 23 hours 59 minutes 59 seconds'))->toDateTimeString();
        $now = Carbon::now();

        return response()->json(['votingAllowed' => $cutoff >= $now]);
    }

    public function getWeebAnswersForUser()
    {
        $user_id = $this->request->auth["id"];

        $answers = Weeb_answers::byUserId($user_id)->get(["weeb_answers.timestamp as timestamp", "weeb_choices.value", "weeb_choices.name"]);

        if (count($answers) > 0) {
            $newestDate = $answers[0]->timestamp;

            $finalAnswers = [];

            foreach ($answers as $answer) {
                if ($answer->timestamp == $newestDate) $finalAnswers[] = $answer;
            }

            return response()->json($finalAnswers);
        } else {
            return response()->json([]);
        }
    }

    public function voteWeeb()
    {
        date_default_timezone_set("Europe/Stockholm");
        if ($this->request->has('choices')) {
            $this->validate($this->request, [
                'choices' => 'bail|required|array|min:1',
                'choices.*' => 'bail|required|string|distinct|min:1'
            ]);

            $choices = $this->request->input('choices');
            $continue = true;
            foreach ($choices as $choice) {
                $maybeChoices = Weeb_choices::getByValue($choice)->get();

                if (count($maybeChoices) === 0) {
                    $continue = false;
                    break;
                }
            }

            $user_id = $this->request->auth["id"];

            if ($continue) {
                $timestamp = date('Y-m-d H:i:s');
                foreach ($choices as $value) {
                    $choice = Weeb_choices::getByValue($value)->first();

                    $answer = new Weeb_answers();

                    $answer->user_id = (int)$user_id;
                    $answer->choice_id = (int)$choice->id;
                    $answer->ip_address = $this->request->ip();
                    $answer->timestamp = $timestamp;

                    $answer->timestamps = false;

                    $answer->save();
                }

                return response()->json([
                    'status' => 201,
                    'message' => 'Vote(s) saved'
                ], 201);
            } else {
                throw new BadRequestHttpException;
            }
        } else {
            throw new BadRequestHttpException;
        }
    }

    public function getAllWeebAnswers()
    {
        $answers = Weeb_answers::getAll()->get(["weeb_answers.timestamp as timestamp", "weeb_choices.value", "weeb_choices.name", "weeb_answers.user_id"]);

        if (count($answers) > 0) {
            $userIds = [];
            $userTimestamps = new stdClass();

            foreach ($answers as $answer) {
                if (!in_array($answer->user_id, $userIds)) $userIds[] = $answer->user_id;
            }

            foreach ($userIds as $userId) {
                $userTimestamps->{$userId} = strtotime('monday this week - 69 days');
                foreach ($answers as $answer) {
                    if ($answer->user_id == $userId) {
                        if (strtotime($answer->timestamp) > $userTimestamps->{$userId}) {
                            $userTimestamps->{$userId} = strtotime($answer->timestamp);
                        }
                    }
                }
            }

            $finalAnswers = [];

            foreach ($userTimestamps as $userId => $timestamp) {
                foreach ($answers as $answer) {
                    if ($answer->user_id == $userId && strtotime($answer->timestamp) == $timestamp) $finalAnswers[] = $answer;
                }
            }

            return response()->json($finalAnswers);
        } else {
            return response()->json([]);
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
