<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class SponsorController extends Controller
{
    public function index()
    {
        $sponsor = Sponsor::where('status', 'Active')
            ->whereNull('deleted_at')
            ->orderBy('position', 'asc')
            ->get();

        if ($sponsor) {
            return $this->sendResponse($sponsor, 'Get Sponsor successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'failed query'], 500);
        }
    }

    public function get()
    {
        $sponsor = Sponsor::where('status', 'Active')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($sponsor) {
            return $this->sendResponse($sponsor, 'Get Sponsor successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'failed query'], 500);
        }
    }

    public function getByPosition(Request $request)
    {
        $sponsor = Sponsor::where('position', $request->position)
            ->whereNull('deleted_at')
            ->first();

        if ($sponsor) {
            return $this->sendResponse($sponsor, 'Get Sponsor successfully.');
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

        $position = Sponsor::whereNull("deleted_at")->max("position");

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
        $data["image"] = config("app.url") . "images/" . $name . ".webp";
        $save = Sponsor::create($data);

        if ($save) {
            return $this->sendResponse($save, 'Created Sponsor successfully.', 201);
        } else {
            return $this->sendError('Failed.', ['error' => 'failed query'], 500);
        }
    }

    public function update(Request $request)
    {
        $content = Sponsor::find($request->id);

        if (!$content) {
            return $this->sendError('Sponsor not found.', ['reason' => 'Sponsor not found on database']);
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
            $data["image"] = config("app.url") . "images/" . $name . ".webp";
        }

        // Sustituye una posicion asignada y la archiva
        if ($request->action == "change-position") {
            $exist = Sponsor::where('position', $request->position)
                ->where('id', '!=', $request->id)
                ->first();
            if ($exist) {
                $exist->position = 0;
                $exist->status = "Archived";
                $exist->save();
            }
        }

        $update = $content->update($data);

        if ($update) {
            return $this->sendResponse($update, 'Updated Sponsor successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'failed query'], 500);
        }
    }

    public function delete(Request $request)
    {

        $content = Sponsor::find($request->id);

        if (!$content) {
            return $this->sendError('Content not found.', ['reason' => 'Sponsor not found on database']);
        }

        $content->deleted_at = Carbon::now();
        $content->status = "Deleted";
        $content->save();

        return $this->sendResponse($content, 'Deleted Sponsor successfully.');
    }
}
