<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostViews;
use App\Models\User;
use App\Services\UserActivityService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class PostController extends Controller
{
    public function index()
    {
        $hero = Post::where('status', 'Active')
            ->whereNull('deleted_at')
            ->orderBy('position', 'asc')
            ->get();

        if ($hero) {
            return $this->sendResponse($hero, 'Get Content successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'failed query'], 500);
        }
    }

    public function get()
    {
        $posts = Post::whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        $response = [];
        $response['hero'] = $this->articlesTransformer($posts);
        $response['posts'] = $this->getLatest();
        $categories = Category::orderBy('created_at', 'desc')->get();
        $latestByCat = [];
        foreach ($categories as $category) {
            $line = [];
            $line['category_name'] = $category->name;
            $line['category_slug'] = $category->slug;
            $line['posts'] = $this->getLatestByCat($category->id);
            if (count($line['posts']['posts']) <= 0) {
                continue;
            }
            array_push($latestByCat, $line);
        }
        $response['latest_by_cat'] = $latestByCat;
        $response['hots'] = $this->getHots();
        $response['categories'] = $categories;
        if ($posts) {
            return $this->sendResponse($response, 'Get Content successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'failed query'], 500);
        }
    }

    public function getAllToDash()
    {
        $posts = Post::orderBy('created_at', 'desc')
            ->withTrashed()
            ->get();
        $response = [];
        $response['posts'] = $this->articlesTransformer($posts);
        $categories = Category::orderBy('created_at', 'desc')->get();
        $response['categories'] = $categories;
        if ($posts) {
            return $this->sendResponse($response, 'Get Content successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'failed query'], 500);
        }
    }

    public function getArticlesByCat($slug)
    {

        $category = Category::where('slug', $slug)->first();

        $posts = Post::whereNull('deleted_at')
            ->where('category_id', $category->id)
            ->orderBy('created_at', 'desc')
            ->get();
        $response = [];
        $response['title'] = $category->name;
        $response['description'] = $category->description;
        $response['posts'] = $this->articlesTransformer($posts);
        $categories = Category::orderBy('created_at', 'desc')->get();
        $response['categories'] = $categories;
        if ($posts) {
            return $this->sendResponse($response, 'Get Content successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'failed query'], 500);
        }
    }

    protected function getLatest()
    {
        $posts = Post::whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return $this->articlesTransformer($posts);
    }

    protected function getHots()
    {
        $posts = Post::whereNull('deleted_at')
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();

        return $this->articlesTransformer($posts);
    }

    protected function getLatestByCat($cat_id)
    {
        $posts = Post::whereNull('deleted_at')
            ->where('category_id', $cat_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        $category = Category::where('id', $cat_id)->first();
        $response['category_name'] = $category->name;
        $response['category_description'] = $category->description;
        $response['category_slug'] = $category->slug;
        $response['posts'] = $this->articlesTransformer($posts);

        return $response;
    }

    public function getBySlug(Request $request, $slug)
    {
        $post = Post::whereNull('deleted_at')
            ->where('slug', $slug)
            ->first();

        $post->views = $post->views + 1;
        $post->save();

        $response = [
            "id" => $post->id,
            "title" => $post->title,
            "sub_title" => $post->sub_title,
            "description" => $post->description,
            "short_description" => $post->short_description,
            "slug" => $post->slug,
            "main_image" => $post->main_image,
            "category_id" => $post->category_id,
            "category_name" => Category::where('id', $post->category_id)->value('name'),
            "body" => $post->body,
            "status" => $post->status,
            "views" => $post->views,
            "created_by" => $post->created_by,
            "owner" => User::where('id', $post->created_by)->value('name'),
            "updated_by" => User::where('id', $post->updated_by)->value('name'),
            "created_at" => Carbon::parse($post->created_at)->format('d/m/Y'),
            "updated_at" => Carbon::parse($post->updated_at)->format('d/m/Y')
        ];

        if ($post) {
            return $this->sendResponse($response, 'Get Post successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'failed query'], 500);
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'image' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $name = $request->file('image')->getClientOriginalName();
        $manager = new ImageManager(new Driver());
        $image = $manager->read(file_get_contents($request->image));
        $image->cover(1200, 720);
        $encoded = $image->toWebp(70);
        $encoded->save("images/" . $name . ".webp");

        $titulo = $request->title;
        $texto_minusculas = strtolower($titulo);
        $texto_con_guiones = str_replace(" ", "-", $texto_minusculas);
        $slug = preg_replace("/[^a-zA-Z0-9\-]/", "", $texto_con_guiones);

        $data = $request->all();
        $data["id"] = Str::uuid();
        $data["slug"] = $slug . "-" . random_int(1000, 9999);
        $data["created_by"] = Auth::user()->id;
        $data["main_image"] = config("app.url") . "/images/" . $name . ".webp";
        $save = Post::create($data);

        $description = [
            "affected_record" => $save->id,
            "model" => "App\Models\Post"
        ];
        UserActivityService::log(auth()->id(), "create-post", json_encode($description), "success");

        if ($save) {
            return $this->sendResponse($save, 'Created Post successfully.', 201);
        } else {
            return $this->sendError('Failed.', ['error' => 'failed query'], 500);
        }
    }

    public function update(Request $request)
    {
        $content = Post::where('id', $request->id)
            ->withTrashed()
            ->first();

        if (!$content) {
            return $this->sendError('Content not found.', ['reason' => 'Content not found on database']);
        }

        $data = $request->all();

        // Hace el cambio de imagen si es solicitado
        if ($request->file('image')) {
            $name = $request->file('image')->getClientOriginalName();
            $manager = new ImageManager(new Driver());
            $image = $manager->read(file_get_contents($request->image));
            $image->cover(1200, 720);
            $encoded = $image->toWebp(70);
            $encoded->save("images/" . $name . ".webp");
            $data["main_image"] = config("app.url") . "/images/" . $name . ".webp";
        }

        $update = $content->update($data);

        $description = [
            "affected_record" => $request->id,
            "payload" => $request->all(),
            "model" => "App\Models\Post"
        ];
        UserActivityService::log(auth()->id(), "update-post", json_encode($description), "success");

        if ($update) {
            return $this->sendResponse($update, 'Updated Content successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'failed query'], 500);
        }
    }

    public function delete(Request $request)
    {
        $content = Post::where('id', $request->id)
            ->withTrashed()
            ->first();

        if (!$content) {
            return $this->sendError('Content not found.', ['reason' => 'Content not found on database']);
        }

        $content->deleted_at = Carbon::now();
        $content->status = "Deleted";
        $content->save();

        $description = [
            "affected_record" => $request->id,
            "model" => "App\Models\Post"
        ];
        UserActivityService::log(auth()->id(), "delete-post", json_encode($description), "success");

        return $this->sendResponse($content, 'Deleted Content successfully.');
    }

    public function articlesTransformer($posts)
    {
        $articles = [];
        foreach ($posts as $post) {
            $item = [];
            $item = [
                "id" => $post->id,
                "title" => $post->title,
                "sub_title" => $post->sub_title,
                "description" => $post->description,
                "short_description" => $post->short_description,
                "slug" => $post->slug,
                "main_image" => $post->main_image,
                "category_id" => $post->category_id,
                "category_name" => Category::where('id', $post->category_id)->value('name'),
                "body" => $post->body,
                "status" => $post->status,
                "views" => $post->views,
                "created_by" => $post->created_by,
                "owner" => User::where('id', $post->created_by)->value('name'),
                "updated_by" => User::where('id', $post->updated_by)->value('name'),
                "created_at" => Carbon::parse($post->created_at)->format('d/m/Y'),
                "updated_at" => Carbon::parse($post->updated_at)->format('d/m/Y')
            ];
            array_push($articles, $item);
        }
        return $articles;
    }
}
