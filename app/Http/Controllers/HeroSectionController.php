<?php

namespace App\Http\Controllers;

use App\Models\HeroSection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class HeroSectionController extends Controller
{
    public function index()
    {
        $hero = HeroSection::where('status', 'Active')
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
        $hero = HeroSection::whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($hero) {
            return $this->sendResponse($hero, 'Get Content successfully.');
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

        $position = HeroSection::whereNull("deleted_at")->max("position");

        if (!$position) {
            $position = 0;
        }

        $name = $request->file('image')->getClientOriginalName();
        $manager = new ImageManager(new Driver());
        $image = $manager->read(file_get_contents($request->image));
        $image->cover(600, 360);
        $encoded = $image->toWebp(70);
        $encoded->save("images/" . $name . ".webp");

        $data = $request->all();
        $data["position"] = $position++;
        $data["created_by"] = Auth::user()->id;
        $data["image"] = config("app.url") . "/images/" . $name . ".webp";
        $save = HeroSection::create($data);

        if ($save) {
            return $this->sendResponse($save, 'Created Content successfully.', 201);
        } else {
            return $this->sendError('Failed.', ['error' => 'failed query'], 500);
        }
    }

    public function update(Request $request)
    {
        $content = HeroSection::find($request->id);

        if (!$content) {
            return $this->sendError('Content not found.', ['reason' => 'Content not found on database']);
        }

        $data = $request->all();

        // Hace el cambio de imagen si es solicitado
        if ($request->file('image')) {
            $name = $request->file('image')->getClientOriginalName();
            $manager = new ImageManager(new Driver());
            $image = $manager->read(file_get_contents($request->image));
            $image->cover(600, 360);
            $encoded = $image->toWebp(70);
            $encoded->save("images/" . $name . ".webp");
            $data["image"] = config("app.url") . "/images/" . $name . ".webp";
        }

        // Sustituye una posicion asignada y la archiva
        if ($request->action == "change-position") {
            $exist = HeroSection::where('position', $request->position)
                ->where('id', '!=', $request->id)
                ->first();
            logger(json_encode($exist));
            if ($exist) {
                $exist->position = 0;
                $exist->status = "Archived";
                $exist->save();
            }
        }

        $update = $content->update($data);

        if ($update) {
            return $this->sendResponse($update, 'Updated Content successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'failed query'], 500);
        }
    }

    public function delete(Request $request)
    {

        $content = HeroSection::find($request->id);

        if (!$content) {
            return $this->sendError('Content not found.', ['reason' => 'Content not found on database']);
        }

        $content->deleted_at = Carbon::now();
        $content->status = "Deleted";
        $content->save();

        return $this->sendResponse($content, 'Deleted Content successfully.');
    }
}
