<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MangXaHoi;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function index()
    {
        return MangXaHoi::orderBy('Ten_mang_xa_hoi')->get();
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'Ten_mang_xa_hoi' => 'required|string|max:255',
            'Link_mang_xa_hoi' => 'required|url|max:255',
            'Icon' => 'nullable|string|max:255',
        ]);

        $socialLink = MangXaHoi::create($validatedData);

        return response()->json($socialLink, 201);
    }

    public function update(Request $request, MangXaHoi $socialLink)
    {
        $validatedData = $request->validate([
            'Ten_mang_xa_hoi' => 'required|string|max:255',
            'Link_mang_xa_hoi' => 'required|url|max:255',
            'Icon' => 'nullable|string|max:255',
        ]);

        $socialLink->update($validatedData);

        return response()->json($socialLink);
    }

    public function destroy(MangXaHoi $socialLink)
    {
        $socialLink->delete();
        return response()->json(null, 204);
    }
     public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $socialLink = MangXaHoi::findOrFail($id);
        $socialLink->is_active = $request->input('is_active');
        $socialLink->save();

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công!',
            'data' => $socialLink,
        ]);
    }
     public function getActiveLinks()
    {
        $links = MangXaHoi::where('is_active', true)
                          ->orderBy('Ten_mang_xa_hoi', 'asc')
                          ->get();

        return response()->json($links);
    }

}
